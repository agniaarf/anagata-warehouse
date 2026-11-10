@php
  $s = $cms[$serviceSlug];
  $steps = json_decode(cms_field($s, 'steps', '[]'), true) ?: [];
  $wa = cms_field($cms['site_settings'], 'whatsapp_number');
  $waText = 'Halo Anagata Warehouse, saya ingin berdiskusi tentang kebutuhan fulfillment bisnis saya.';
  $cta = cms_field($s, 'cta_label');
@endphp
<section id="{{ $serviceSlug }}" class="service-chapter service-chapter--{{ $serviceSlug }}" data-analytics-section="{{ $serviceSlug }}" data-funnel-event="service_view" data-funnel-target="{{ $serviceSlug }}" @if($serviceSlug === 'inbound') data-main-entry @endif>
  <div class="wrap service-story">
    <div class="service-photo reveal-on-scroll">
      <img src="{{ \App\Support\Cms::largeUrl(cms_field($s, 'image')) }}" alt="{{ cms_field($s, 'image_alt') }}" loading="lazy" decoding="async">
    </div>
    <div class="service-copy reveal-on-scroll">
      @if($serviceSlug === 'inbound')<span class="service-intro-label">EXPLORE OUR SERVICES / END-TO-END FULFILLMENT</span>@endif
      <span class="story-kicker">{{ cms_field($s, 'eyebrow') }}</span>
      <h2>{!! cms_rich_text(cms_field($s, 'headline')) !!}</h2>
      <div class="service-body">{!! cms_rich_text(cms_field($s, 'body')) !!}</div>
      <ol class="service-steps">
        @foreach($steps as $step)
          <li><span>{{ $loop->iteration }}</span>{{ $step }}</li>
        @endforeach
      </ol>
      @if($cta)
        <a href="https://wa.me/{{ $wa }}?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="service-cta" data-analytics-contact="whatsapp">{{ $cta }} <span aria-hidden="true">↗</span></a>
      @endif
    </div>
  </div>
</section>
