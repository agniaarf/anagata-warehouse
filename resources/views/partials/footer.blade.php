@php
  $f = $cms['footer'];
  $wa = cms_field($cms['site_settings'], 'whatsapp_number');
  $email = cms_field($cms['site_settings'], 'email');
  $ig = cms_field($cms['site_settings'], 'instagram_handle');
  $waText = 'Halo Anagata Warehouse, saya mau tanya soal layanan fulfillment ini.';
  // Gudang list is NOT a separately-seeded field — it's derived from Location's
  // active hub cards (single source of truth), so an address edit there
  // automatically stays in sync with what's shown here.
  $hubs = cms_items($cms['location'])->filter(fn ($i) => $i['value']['status'] === 'hub');
  $comingSoon = cms_items($cms['location'])->filter(fn ($i) => $i['value']['status'] === 'coming');
  $logo = \App\Support\Cms::imageUrl(cms_field($cms['site_settings'], 'logo_light'));
@endphp
<!-- CONTACT -->
<section id="contact" class="contact-section" data-analytics-section="contact">
  <div class="wrap contact-center">
    <div class="eyebrow">{{ cms_field($f, 'eyebrow') }}</div>
    <h2>{!! cms_rich_text(cms_field($f, 'headline')) !!}</h2>
    <div class="hero-hash contact-hash">{{ cms_field($f, 'tagline') }}</div>
    <a href="https://wa.me/{{ $wa }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="btn btn-solid foot-wa-btn" data-analytics-contact="whatsapp">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
      {{ cms_field($f, 'cta_label') }}
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="wrap">
    <div class="foot-top">
      <div class="foot-brand">
        <img src="{{ $logo }}" loading="lazy" decoding="async" alt="Anagata Warehouse, partner fulfillment dan storage untuk seller online">
        <p>{{ cms_field($f, 'brand_tagline') }}</p>
      </div>
      <div class="foot-col">
        <h4>Lokasi</h4>
        @foreach($hubs as $hub)
          <div>{{ $hub['value']['badge'] }}: {{ $hub['value']['area'] }}</div>
        @endforeach
        @if($comingSoon->isNotEmpty())
          <div>{{ $comingSoon->pluck('value.name')->map(fn ($n) => str_replace('Warehouse ', '', $n))->implode(', ') }}</div>
        @endif
      </div>
      <div class="foot-col">
        <h4>Ikuti Kami</h4>
        <div class="foot-social">
          <a href="https://wa.me/{{ $wa }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" aria-label="WhatsApp" data-analytics-contact="whatsapp">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
          </a>
          <a href="https://instagram.com/{{ $ig }}" target="_blank" rel="noopener" aria-label="Instagram" data-analytics-contact="instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="mailto:{{ $email }}" aria-label="Email" data-analytics-contact="email">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
          </a>
        </div>
        <div style="margin-top:14px;">{{ $email }}</div>
      </div>
    </div>
    <div class="foot-bottom">
      <div>© {{ date('Y') }} {{ cms_field($f, 'copyright_holder') }}</div>
      <div class="hero-hash">{{ cms_field($f, 'tagline') }}</div>
    </div>
    <p class="analytics-disclosure">Statistik penggunaan anonim membantu kami memahami section yang dilihat. Fitur ini tidak menyimpan alamat IP mentah.</p>
  </div>
</footer>
