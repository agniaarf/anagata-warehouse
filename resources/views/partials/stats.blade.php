@php
  $s = $cms['stats'];
  $stats = cms_items($s);
@endphp
<!-- STATS -->
<section class="stats-section" data-analytics-section="stats">
  <div class="wrap">
    <div class="stats-card">
      <div class="stats-grid">
        @foreach($stats as $stat)
          <div class="stats-panel">
            <span class="stats-num">{{ $stat['value']['number'] }}</span>
            <p>{{ $stat['value']['label'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
