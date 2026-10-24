// nav blur-on-scroll — transparent over the hero, solid+blurred once scrolled
(function(){
  const navEl = document.querySelector('nav');
  function syncNavScroll(){
    navEl.classList.toggle('is-scrolled', window.scrollY > 40);
  }
  syncNavScroll();
  window.addEventListener('scroll', syncNavScroll, {passive:true});
})();

// mobile nav toggle
document.getElementById('navToggle').addEventListener('click', ()=>{
  document.getElementById('navMobile').classList.toggle('open');
});
document.querySelectorAll('#navMobile a').forEach(a=>{
  a.addEventListener('click', ()=>document.getElementById('navMobile').classList.remove('open'));
});

// First-party, anonymous visitor analytics. A tab is one session unless it has
// been idle for 30 minutes; only section impressions and contact-channel clicks
// are collected. Raw referrer URLs, IPs, and full user-agent strings are never
// sent to or stored by the analytics endpoint.
(function(){
  const body = document.body;
  const endpoint = body.dataset.analyticsEndpoint;
  if(body.dataset.analyticsEnabled !== 'true' || !endpoint || !window.fetch) return;

  const visitorStorageKey = 'anagata.analytics.visitor.v1';
  const sessionStorageKey = 'anagata.analytics.session.v1';
  const lastActiveStorageKey = 'anagata.analytics.last-active.v1';
  const timeoutMs = Number(body.dataset.analyticsSessionTimeout || 30) * 60 * 1000;
  const visitorLifetimeMs = Number(body.dataset.analyticsVisitorDays || 90) * 24 * 60 * 60 * 1000;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  let memoryVisitorId = null;
  let memorySessionId = null;
  let memoryLastActive = 0;
  let sessionStartPromise = Promise.resolve();

  function uuid(){
    if(window.crypto?.randomUUID) return window.crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character=>{
      const random = Math.random() * 16 | 0;
      return (character === 'x' ? random : (random & 0x3 | 0x8)).toString(16);
    });
  }

  function getStorage(storageName, key){
    try { return window[storageName].getItem(key); } catch(_) { return null; }
  }

  function setStorage(storageName, key, value){
    try { window[storageName].setItem(key, value); } catch(_) {}
  }

  function getVisitorId(){
    const now = Date.now();
    const saved = getStorage('localStorage', visitorStorageKey);
    if(saved){
      try {
        const visitor = JSON.parse(saved);
        if(visitor.id && visitor.expiresAt > now){
          visitor.expiresAt = now + visitorLifetimeMs;
          setStorage('localStorage', visitorStorageKey, JSON.stringify(visitor));
          return visitor.id;
        }
      } catch(_) {}
    }

    memoryVisitorId = memoryVisitorId || uuid();
    setStorage('localStorage', visitorStorageKey, JSON.stringify({id:memoryVisitorId, expiresAt:now + visitorLifetimeMs}));
    return memoryVisitorId;
  }

  function campaignValue(key){
    const value = new URL(window.location.href).searchParams.get(key);
    return value ? value.slice(0, 100) : null;
  }

  function referrerHost(){
    try {
      const referrer = new URL(document.referrer);
      return referrer.hostname && referrer.hostname !== window.location.hostname ? referrer.hostname.slice(0, 253) : null;
    } catch(_) { return null; }
  }

  function send(payload){
    return window.fetch(endpoint, {
      method:'POST',
      credentials:'same-origin',
      keepalive:true,
      headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
      body:JSON.stringify(payload)
    }).catch(()=>null);
  }

  function eventPayload(type, target, sessionId){
    return {
      event_id:uuid(),
      session_id:sessionId,
      visitor_id:getVisitorId(),
      type,
      target:target || null,
      path:window.location.pathname.slice(0, 255) || '/',
      referrer_host:referrerHost(),
      utm_source:campaignValue('utm_source'),
      utm_medium:campaignValue('utm_medium'),
      utm_campaign:campaignValue('utm_campaign')
    };
  }

  function beginSession(){
    const sessionId = uuid();
    memorySessionId = sessionId;
    setStorage('sessionStorage', sessionStorageKey, sessionId);
    memoryLastActive = Date.now();
    setStorage('sessionStorage', lastActiveStorageKey, String(memoryLastActive));
    const payload = eventPayload('session_start', null, sessionId);
    sessionStartPromise = send(payload);
    return sessionId;
  }

  function activeSession(){
    const now = Date.now();
    const storedId = getStorage('sessionStorage', sessionStorageKey);
    const lastActive = Number(getStorage('sessionStorage', lastActiveStorageKey) || memoryLastActive || 0);
    let sessionId = storedId || memorySessionId;

    if(!sessionId || !lastActive || now - lastActive >= timeoutMs){
      sessionId = beginSession();
    }

    memoryLastActive = now;
    setStorage('sessionStorage', lastActiveStorageKey, String(now));
    return sessionId;
  }

  function record(type, target){
    const sessionId = activeSession();
    const payload = eventPayload(type, target, sessionId);
    return sessionStartPromise.then(()=>send(payload));
  }

  activeSession();

  const seenStoragePrefix = 'anagata.analytics.seen.';
  let observedSessionId = activeSession();
  let observedSections = new Set();
  function loadSeenSections(sessionId){
    try {
      return new Set(JSON.parse(getStorage('sessionStorage', seenStoragePrefix + sessionId) || '[]'));
    } catch(_) { return new Set(); }
  }
  observedSections = loadSeenSections(observedSessionId);

  function trackSection(section){
    const sessionId = activeSession();
    if(observedSessionId !== sessionId){
      observedSessionId = sessionId;
      observedSections = loadSeenSections(sessionId);
    }
    if(observedSections.has(section)) return;
    observedSections.add(section);
    setStorage('sessionStorage', seenStoragePrefix + sessionId, JSON.stringify([...observedSections]));
    record('section_view', section);
  }

  const sectionTargets = document.querySelectorAll('[data-analytics-section]');
  if('IntersectionObserver' in window){
    const observer = new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(entry.isIntersecting) trackSection(entry.target.dataset.analyticsSection);
    }), {threshold:0.12});
    sectionTargets.forEach(target=>observer.observe(target));
  } else {
    sectionTargets.forEach(target=>trackSection(target.dataset.analyticsSection));
  }

  document.querySelectorAll('[data-analytics-contact]').forEach(link=>{
    link.addEventListener('click', ()=>record('contact_click', link.dataset.analyticsContact));
  });
})();

// marquee duplicate for seamless loop
const mq = document.getElementById('marquee');
mq.innerHTML += mq.innerHTML;

// Set browser-native lazy loading and a shared loading placeholder for every
// content image. The nav logo stays eager because it is visible immediately.
(function(){
  function prepare(image){
    if(image.closest('.leaflet-container')) return;
    if(!image.hasAttribute('loading')) image.loading = image.closest('nav') ? 'eager' : 'lazy';
    if(!image.hasAttribute('decoding')) image.decoding = 'async';
    if(!image.getAttribute('src')) return;

    image.classList.add('image-skeleton');
    const loaded = ()=>image.classList.remove('image-skeleton');
    if(image.complete) loaded();
    else {
      image.addEventListener('load', loaded, {once:true});
      image.addEventListener('error', loaded, {once:true});
    }
  }

  function prepareAdded(node){
    if(node.nodeType !== 1) return;
    if(node.matches('img')) prepare(node);
    node.querySelectorAll('img').forEach(prepare);
  }

  document.querySelectorAll('img').forEach(prepare);
  new MutationObserver(records=>records.forEach(record=>record.addedNodes.forEach(prepareAdded)))
    .observe(document.body, {childList:true, subtree:true});
})();

// Gallery lightbox — the page only renders an 8-tile preview (gallery.blade.php);
// every photo's thumb/full URL comes from the JSON blob inside the <dialog>, and
// full-size images are only requested once someone actually opens the viewer.
(function(){
  const dialog = document.getElementById('galleryLightbox');
  if(!dialog) return;
  const photos = JSON.parse(dialog.querySelector('.lb-data').textContent || '[]');
  if(!photos.length) return;
  const image = dialog.querySelector('.lb-image');
  const caption = dialog.querySelector('.lb-caption');
  const counter = dialog.querySelector('.lb-counter');
  const strip = dialog.querySelector('.lb-strip');
  let index = 0;
  let stripBuilt = false;

  function buildStrip(){
    photos.forEach((photo, i)=>{
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'lb-thumb';
      button.setAttribute('role', 'listitem');
      button.setAttribute('aria-label', `Foto ${i + 1}: ${photo.caption}`);
      const thumb = document.createElement('img');
      thumb.src = photo.thumb;
      thumb.alt = '';
      thumb.loading = 'lazy';
      button.append(thumb);
      button.addEventListener('click', ()=>show(i));
      strip.append(button);
    });
    stripBuilt = true;
  }

  function preload(i){
    const next = new Image();
    next.src = photos[(i + photos.length) % photos.length].full;
  }

  function show(i){
    index = (i + photos.length) % photos.length;
    const photo = photos[index];
    // drop the previous photo first so it doesn't linger while the next one loads
    image.removeAttribute('src');
    image.classList.add('image-skeleton');
    image.onload = image.onerror = ()=>image.classList.remove('image-skeleton');
    image.src = photo.full;
    image.alt = photo.alt;
    caption.textContent = photo.caption;
    counter.textContent = `${index + 1} / ${photos.length}`;
    [...strip.children].forEach((button, j)=>{
      button.classList.toggle('active', j === index);
      if(j === index) button.setAttribute('aria-current', 'true');
      else button.removeAttribute('aria-current');
    });
    const active = strip.children[index];
    if(active) active.scrollIntoView({block:'nearest', inline:'center'});
    if(photos.length > 1){ preload(index + 1); preload(index - 1); }
  }

  function open(i){
    if(!stripBuilt) buildStrip();
    if(typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', '');
    document.documentElement.classList.add('lb-open');
    show(i);
  }

  function close(){
    if(typeof dialog.close === 'function') dialog.close();
    else dialog.removeAttribute('open');
    document.documentElement.classList.remove('lb-open');
  }

  document.querySelectorAll('[data-gallery-open]').forEach(button=>{
    button.addEventListener('click', ()=>open(Number(button.dataset.galleryOpen) || 0));
  });
  // Esc closes natively; the 'close' event can arrive late, so only unlock
  // scrolling if the viewer wasn't reopened in the meantime.
  dialog.addEventListener('close', ()=>{
    if(!dialog.open) document.documentElement.classList.remove('lb-open');
  });
  dialog.querySelector('.lb-close').addEventListener('click', close);
  dialog.querySelector('.lb-prev').addEventListener('click', ()=>show(index - 1));
  dialog.querySelector('.lb-next').addEventListener('click', ()=>show(index + 1));
  // clicking the empty area around the photo closes, like most viewers
  dialog.addEventListener('click', e=>{
    if(e.target === dialog || e.target.classList.contains('lb-stage') || e.target.classList.contains('lb-figure')) close();
  });
  dialog.addEventListener('keydown', e=>{
    if(e.key === 'ArrowLeft'){ e.preventDefault(); show(index - 1); }
    if(e.key === 'ArrowRight'){ e.preventDefault(); show(index + 1); }
  });

  let touchX = null;
  dialog.addEventListener('touchstart', e=>{ touchX = e.touches[0].clientX; }, {passive:true});
  dialog.addEventListener('touchend', e=>{
    if(touchX === null) return;
    const dx = e.changedTouches[0].clientX - touchX;
    touchX = null;
    if(Math.abs(dx) > 50 && !e.target.closest('.lb-strip')) show(index + (dx < 0 ? 1 : -1));
  });
})();

// hero carousel — each slide can carry its own stat overlay (synced by index)
(function(){
  const slides = document.querySelectorAll('.hero-slide');
  if(!slides.length) return;
  const dotsWrap = document.getElementById('heroDots');
  const overlays = document.querySelectorAll('.hero-stat-overlay');
  function loadSlide(slide){
    if(!slide || slide.dataset.loading === 'true' || slide.dataset.loaded === 'true') return;
    const image = new Image();
    slide.dataset.loading = 'true';
    slide.classList.add('image-skeleton');
    image.decoding = 'async';
    image.onload = ()=>{
      slide.style.backgroundImage = `url("${image.src}")`;
      slide.dataset.loaded = 'true';
      delete slide.dataset.loading;
      slide.classList.remove('image-skeleton');
    };
    image.onerror = ()=>{
      delete slide.dataset.loading;
      slide.classList.remove('image-skeleton');
    };
    image.src = slide.dataset.background;
  }
  slides.forEach((s,i)=>{
    const d=document.createElement('div');
    d.className='dot'+(i===0?' active':'');
    d.addEventListener('click',()=>go(i));
    dotsWrap.appendChild(d);
  });
  let idx=0;
  loadSlide(slides[0]);
  loadSlide(slides[1]);
  function go(i){
    if(i === idx) return;
    slides[idx].classList.remove('active');
    dotsWrap.children[idx].classList.remove('active');
    overlays.forEach(o=>{ if(+o.dataset.index === idx) o.classList.remove('active'); });
    idx=i;
    loadSlide(slides[idx]);
    loadSlide(slides[(idx + 1) % slides.length]);
    slides[idx].classList.add('active');
    dotsWrap.children[idx].classList.add('active');
    overlays.forEach(o=>{ if(+o.dataset.index === idx) o.classList.add('active'); });
  }
  if(slides.length > 1) setInterval(()=>{ go((idx+1)%slides.length); }, 5000);
})();

// service flip cards — hover flips on desktop (CSS-only), tap flips on touch/no-hover
// devices, and Enter/Space flips for keyboard users on any device
(function(){
  const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  document.querySelectorAll('.flip-card').forEach(card=>{
    const toggle = () => card.classList.toggle('flipped');
    if(!canHover) card.addEventListener('click', toggle);
    card.addEventListener('keydown', (e)=>{
      if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); toggle(); }
    });
  });
})();

// testimonials — auto-cycling pull quotes, pauses on reduced-motion preference
(function(){
  const track = document.querySelector('.testi-track');
  if(!track) return;
  const slides = [...track.querySelectorAll('.testi-slide')];
  const dotsWrap = document.getElementById('testiDots');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let idx = 0;

  slides.forEach((s,i)=>{
    const d = document.createElement('div');
    d.className = 'dot' + (i===0 ? ' active' : '');
    d.addEventListener('click', ()=>go(i));
    dotsWrap.appendChild(d);
  });

  function go(i){
    slides[idx].classList.remove('active');
    dotsWrap.children[idx].classList.remove('active');
    idx = i;
    slides[idx].classList.add('active');
    dotsWrap.children[idx].classList.add('active');
  }

  if(slides.length > 1 && !reduceMotion){
    setInterval(()=>{ go((idx+1) % slides.length); }, 6000);
  }
})();

// leaflet location map - Esri light-gray tiles, free and keyless.
// Warehouse/city coordinates are read straight from the .loc-card markup below
// (single source of truth — no coordinate list to keep in sync in two places).
(function(){
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // bounding box of the Indonesian archipelago (Sabang to Merauke) — fitBounds()
  // computes whatever zoom level is needed for the CURRENT container size, so the
  // whole country stays visible on any screen instead of a fixed zoom that crops
  // the edges on narrow/short mobile map containers
  const INDONESIA_BOUNDS = [[-11.5, 94.5], [7, 141.5]];

  const map = L.map('locMap', {zoomControl:false, attributionControl:false, scrollWheelZoom:false, dragging:!L.Browser.mobile});
  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {maxZoom:16}).addTo(map);

  // fitBounds() needs the container's real, settled size to compute a correct zoom —
  // calling it immediately can catch #locMap before layout/webfonts finish and produce
  // a wildly-wrong (too zoomed out) fit, so defer one frame first
  function frameIndonesia(){
    map.invalidateSize();
    map.fitBounds(INDONESIA_BOUNDS, {padding:[8,8]});
  }
  requestAnimationFrame(frameIndonesia);

  // re-fit whenever the map container's size changes (window resize, orientation
  // change, or a breakpoint swapping .map-wrap's height) so Indonesia stays fully framed
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(frameIndonesia, 200);
  });

  // scroll-wheel zoom stays off until the visitor deliberately clicks into the
  // map, so hovering the section while scrolling the page never hijacks the scroll
  map.on('click', () => map.scrollWheelZoom.enable());
  map.getContainer().addEventListener('mouseleave', () => map.scrollWheelZoom.disable());

  const cardEls = [...document.querySelectorAll('.loc-card')];
  const cards = cardEls.map(el => ({
    el,
    badge: el.querySelector('.loc-card-badge').textContent.trim(),
    coords: [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)],
    hub: el.dataset.hub || null,
    coming: el.classList.contains('coming'),
  }));
  const hubs = cards.filter(c => !c.coming);
  const reachCities = cards.filter(c => c.coming);

  // purely decorative reach points (delivery-coverage radar, not real warehouse cities)
  const DECOR_REACH = [
    {hub:'JKT', coords:[3.9,101.9]},
    {hub:'MKS', coords:[1.3,124.8]},
    {hub:'MKS', coords:[-3.7,128.2]},
    {hub:'MKS', coords:[-4.3,138.0]},
    {hub:'MKS', coords:[-8.5,156.0]},
  ];

  function hubIcon(code){
    return L.divIcon({className:'', html:`<div class="marker-hub"><span class="hub-ring"></span><span class="hub-ring"></span><span class="hub-ring"></span><div class="badge">${code}</div></div>`, iconSize:[52,52]});
  }
  function dotIcon(delay){
    const style = reduceMotion ? '' : ` style="animation-delay:${delay}ms"`;
    return L.divIcon({className:'', html:`<div class="marker-dot"${style}></div>`, iconSize:[9,9]});
  }
  function ghostIcon(){ return L.divIcon({className:'', html:'<div class="marker-ghost"><span></span></div>', iconSize:[26,26]}); }
  function ghostOffIcon(){ return L.divIcon({className:'', html:'<div class="marker-ghost off"><span></span></div>', iconSize:[26,26]}); }

  // sample a gentle quadratic-bezier arc between two points so reach lines read
  // like flight paths rather than flat straight segments
  function curve(a, b, bend = 0.15, segments = 24){
    const [lat1,lng1] = a, [lat2,lng2] = b;
    const midLat = (lat1+lat2)/2, midLng = (lng1+lng2)/2;
    const dx = lng2-lng1, dy = lat2-lat1;
    const ctrlLat = midLat + dx*bend, ctrlLng = midLng - dy*bend;
    const pts = [];
    for(let i=0;i<=segments;i++){
      const t = i/segments;
      const lat = (1-t)*(1-t)*lat1 + 2*(1-t)*t*ctrlLat + t*t*lat2;
      const lng = (1-t)*(1-t)*lng1 + 2*(1-t)*t*ctrlLng + t*t*lng2;
      pts.push([lat,lng]);
    }
    return pts;
  }

  function hubByBadge(badge){ return hubs.find(h => h.badge === badge); }

  const routeStyle = {className:'route-line', color:'#610890', weight:1.6, opacity:0.55};
  const reachLayers = [];
  let dotDelay = 0;

  reachCities.forEach(city => {
    const hub = hubByBadge(city.hub);
    if(!hub) return;
    reachLayers.push(L.polyline(curve(hub.coords, city.coords), routeStyle));
    const isDormant = city.badge === 'BJM'; // matches existing "segera hadir" static-dot treatment
    reachLayers.push(L.marker(city.coords, {icon:dotIcon(dotDelay), zIndexOffset:-10}));
    reachLayers.push(
      L.marker(city.coords, {icon: isDormant ? ghostOffIcon() : ghostIcon()})
        .bindTooltip(city.el.querySelector('h4').textContent.replace('Warehouse ',''), {direction:'top', offset:[0,-10]})
    );
    dotDelay += 45;
  });

  // hub-to-hub link, then the decorative coverage web
  if(hubs.length === 2) reachLayers.push(L.polyline(curve(hubs[0].coords, hubs[1].coords, 0.08), routeStyle));
  DECOR_REACH.forEach(pt => {
    const hub = hubByBadge(pt.hub);
    if(!hub) return;
    reachLayers.push(L.polyline(curve(hub.coords, pt.coords), routeStyle));
    reachLayers.push(L.marker(pt.coords, {icon:dotIcon(dotDelay), zIndexOffset:-10}));
    dotDelay += 45;
  });

  function selectHub(hub){
    cardEls.forEach(c => c.classList.remove('active'));
    hub.el.classList.add('active');
    hub.el.scrollIntoView({block:'nearest', behavior: reduceMotion ? 'auto' : 'smooth'});
    map.flyTo(hub.coords, 6, {duration: reduceMotion ? 0 : 0.8});
  }

  hubs.forEach(hub => {
    const name = hub.el.querySelector('h4').textContent;
    const address = hub.el.querySelector('.loc-card-info p').textContent.trim();
    const gmapsUrl = `https://www.google.com/maps/search/?api=1&query=${hub.coords[0]},${hub.coords[1]}`;
    const popupHtml = `<div class="map-popup"><b>${name}</b><p>${address}</p><a href="${gmapsUrl}" target="_blank" rel="noopener">Buka di Google Maps →</a></div>`;
    L.marker(hub.coords, {icon:hubIcon(hub.badge)})
      .addTo(map)
      .bindTooltip(name + ' · Aktif', {direction:'top', offset:[0,-20]})
      .bindPopup(popupHtml, {closeButton:true, className:'map-popup-wrap'})
      .on('click', () => selectHub(hub));
  });

  // clicking any card (hub or coming-soon) highlights it and flies the map there
  cards.forEach(c => {
    c.el.addEventListener('click', () => {
      cardEls.forEach(el => el.classList.remove('active'));
      c.el.classList.add('active');
      map.flyTo(c.coords, 6, {duration: reduceMotion ? 0 : 0.8});
    });
  });

  // reach web (dashed routes + decorative coverage dots) is always shown — no toggle
  reachLayers.forEach(l => l.addTo(map));

  // count-up stats when the panel scrolls into view
  const counters = document.querySelectorAll('.count-up');
  if(counters.length){
    const animateCount = (el) => {
      const target = parseInt(el.dataset.target, 10);
      if(reduceMotion){ el.textContent = target; return; }
      const duration = 900;
      const start = performance.now();
      function tick(now){
        const p = Math.min((now-start)/duration, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1-p, 3)));
        if(p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    };
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){ animateCount(entry.target); obs.unobserve(entry.target); }
      });
    }, {threshold:0.6});
    counters.forEach(el => io.observe(el));
  }
})();
