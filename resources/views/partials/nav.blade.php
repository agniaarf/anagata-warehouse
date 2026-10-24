@php
  $pageSections = config('page_sections');
  $navItems = cms_items($cms['page_layout'])
    ->filter(fn ($i) => $i['value']['visible'] && $i['value']['show_in_nav'])
    ->sortBy('order')
    ->map(fn ($i) => [
      'label' => $i['value']['nav_label'],
      'anchor' => $pageSections[$i['key']]['anchor'],
    ])
    ->filter(fn ($i) => $i['anchor']); // sections with no anchor id can't be nav targets
  $wa = cms_field($cms['site_settings'], 'whatsapp_number');
  $ig = cms_field($cms['site_settings'], 'instagram_handle');
  $email = cms_field($cms['site_settings'], 'email');
  $waText = 'Halo Anagata Warehouse, saya mau tanya soal layanan fulfillment ini.';
  $logo = \App\Support\Cms::imageUrl(cms_field($cms['site_settings'], 'logo_light'));
@endphp
<nav>
  <div class="logo"><img src="{{ $logo }}" alt="Anagata Warehouse, fulfillment warehouse Jakarta &amp; Makassar" loading="eager" decoding="async"></div>
  <ul class="nav-menu">
    @foreach($navItems as $item)
      <li><a href="{{ $item['anchor'] }}">{{ $item['label'] }}</a></li>
    @endforeach
  </ul>
  <div class="nav-right">
    <div class="nav-social">
      <a href="https://wa.me/{{ $wa }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp" data-analytics-contact="whatsapp">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
      </a>
      <a href="https://instagram.com/{{ $ig }}" target="_blank" rel="noopener" aria-label="Instagram" data-analytics-contact="instagram">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
      </a>
      <a href="mailto:{{ $email }}" aria-label="Email" data-analytics-contact="email">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
      </a>
    </div>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
  </div>
</nav>
<div class="nav-mobile" id="navMobile">
  @foreach($navItems as $item)
    <a href="{{ $item['anchor'] }}">{{ $item['label'] }}</a>
  @endforeach
</div>
