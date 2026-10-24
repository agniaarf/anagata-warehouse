@php
  $l = $cms['location'];
  $cards = cms_items($l);
  $firstHubKey = optional($cards->firstWhere('value.status', 'hub'))['key'];
@endphp
<!-- LOCATION -->
<section id="location" data-analytics-section="location">
  <div class="wrap">
    <div class="loc-grid">
      <div class="map-wrap"><div id="locMap"></div></div>
      <div class="map-stats">
        <div><b class="count-up" data-target="{{ cms_field($l, 'stat_gudang') }}">0</b><span>{{ cms_field($l, 'stat_gudang_label') }}</span></div>
        <div><b class="count-up" data-target="{{ cms_field($l, 'stat_kota') }}">0</b><span>{{ cms_field($l, 'stat_kota_label') }}</span></div>
        <div><b class="count-up" data-target="{{ cms_field($l, 'stat_pulau') }}">0</b><span>{{ cms_field($l, 'stat_pulau_label') }}</span></div>
      </div>
      <div class="loc-panel">
        <div class="eyebrow">{{ cms_field($l, 'eyebrow') }}</div>
        <h2>{!! cms_rich_text(cms_field($l, 'headline')) !!}</h2>
        <p>{{ cms_field($l, 'paragraph') }}</p>
        <div class="loc-cards" id="locCards">
          @foreach($cards as $card)
            @php $v = $card['value']; @endphp
            <div class="loc-card @if($card['key'] === $firstHubKey) active @endif @if($v['status'] === 'coming') coming @endif"
                 data-lat="{{ $v['lat'] }}" data-lng="{{ $v['lng'] }}"
                 @if($v['status'] === 'coming') data-hub="{{ $v['hub_ref'] }}" @endif>
              <div class="loc-card-badge">{{ $v['badge'] }}</div>
              <div class="loc-card-info">
                <h4>{{ $v['name'] }}</h4>
                <p>{{ $v['address'] }}</p>
              </div>
              @if(!empty($v['thumbnail']))
                <div class="loc-card-thumb" style="background-image:url('{{ \App\Support\Cms::imageUrl($v['thumbnail']) }}')"></div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
