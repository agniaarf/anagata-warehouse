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

  const registered = new WeakSet();
  const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => entries.forEach(entry => {
    if(entry.isIntersecting) trackSection(entry.target.dataset.analyticsSection);
  }), {threshold:0.12}) : null;
  function registerSections(){
    document.querySelectorAll('[data-analytics-section]').forEach(target => {
      if(registered.has(target)) return;
      registered.add(target);
      if(observer) observer.observe(target); else trackSection(target.dataset.analyticsSection);
    });
  }
  new MutationObserver(registerSections).observe(document.getElementById('root'), {childList:true,subtree:true});
  registerSections();
  document.addEventListener('click', event => {
    const link = event.target.closest('[data-analytics-contact]');
    if(link) record('contact_click', link.dataset.analyticsContact);
  });

  document.addEventListener('click', event => { if(event.target.closest('[data-journey-start]')) record('journey_start'); });

  const seenFunnelEvents = new Set();
  function trackFunnel(target){
    const type = target.dataset.funnelEvent;
    const eventTarget = target.dataset.funnelTarget || null;
    const key = `${type}:${eventTarget || ''}`;
    if(!type || seenFunnelEvents.has(key)) return;
    seenFunnelEvents.add(key);
    record(type, eventTarget);
    if(target.hasAttribute('data-main-entry')) record('main_site_enter');
  }
  const funnelTargets = document.querySelectorAll('[data-funnel-event]');
  if('IntersectionObserver' in window){
    const funnelObserver = new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(entry.isIntersecting) trackFunnel(entry.target);
    }), {threshold:0.25});
    funnelTargets.forEach(target=>funnelObserver.observe(target));
  } else {
    funnelTargets.forEach(trackFunnel);
  }
})();
