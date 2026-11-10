@php
  $l = $cms['location'];
  $cards = cms_items($l);
  $locationImage = cms_field($cms['history'], 'image');
@endphp
<section id="location" class="locations-editorial" data-analytics-section="location">
  <div class="locations-image">
    <img src="{{ \App\Support\Cms::largeUrl($locationImage) }}" alt="{{ cms_field($cms['history'], 'image_alt') }}" loading="lazy" decoding="async">
    <div class="locations-image-caption">DARI INDONESIA,<br>UNTUK INDONESIA.</div>
  </div>
  <div class="locations-content">
    <span class="story-kicker">OUR LOCATIONS</span>
    <h2>{!! cms_rich_text(cms_field($l, 'headline')) !!}</h2>
    <p>{{ cms_field($l, 'paragraph') }}</p>
    <div class="locations-list">
      @foreach($cards as $card)
        @php $v = $card['value']; @endphp
        @if(($v['status'] ?? '') === 'hub')
          <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($v['address'] ?? $v['name']) }}" target="_blank" rel="noopener" class="location-row">
            <span class="location-badge">{{ $v['badge'] }}</span><span><strong>{{ $v['name'] }}</strong><small>{{ $v['address'] }}</small></span><span class="location-arrow" aria-hidden="true">↗</span>
          </a>
        @else
          <div class="location-row location-row--upcoming"><span class="location-badge">{{ $v['badge'] }}</span><span><strong>{{ $v['name'] }}</strong><small>Segera hadir</small></span></div>
        @endif
      @endforeach
    </div>
  </div>
</section>
