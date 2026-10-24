@php
  $v = $cms['value_prop'];
  $items = cms_items($v);
  // Fixed icon per position — these 4 commitments are stable brand pillars,
  // not a growing collection, so the icon isn't an admin-editable field.
  $icons = [
    '<path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/>', // Same-Day: clock
    '<path d="M3 12h4l3 8 4-16 3 8h4"/>', // Real-time: pulse
    '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/>', // 100%: shield
    '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>', // Terpisah: separated squares
  ];
@endphp
<!-- VALUE PROP -->
<section class="value-section" data-analytics-section="value_prop">
  <div class="wrap">
    <div class="value-head">
      <h2>{!! cms_rich_text(cms_field($v, 'headline')) !!}</h2>
      <p>{{ cms_field($v, 'subtext') }}</p>
    </div>
    <div class="value-grid">
      @foreach($items as $i => $item)
        <div class="value-card">
          <svg class="value-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            {!! $icons[$i % count($icons)] !!}
          </svg>
          <h3>{{ $item['value']['number'] }}</h3>
          <p>{{ $item['value']['label'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
