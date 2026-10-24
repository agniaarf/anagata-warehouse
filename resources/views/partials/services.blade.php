@php
  $s = $cms['services'];
  $cards = cms_items($s);
@endphp
<!-- SERVICES -->
<section id="services" data-analytics-section="services">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">{{ cms_field($s, 'eyebrow') }}</div>
      <h2>{{ cms_field($s, 'headline') }}</h2>
      <p>{{ cms_field($s, 'paragraph') }}</p>
    </div>
  </div>
  <div class="wrap">
    {{-- flip cards: front = short flow, back = step detail. Hover flips on
         desktop (CSS), tap / Enter / Space flips elsewhere (app.js). --}}
    <div class="service-grid">
      @foreach($cards as $card)
        @php $v = $card['value']; @endphp
        <div class="flip-card" tabindex="0" role="button" aria-label="Lihat detail layanan {{ $v['title'] }}">
          <div class="flip-inner">
            <div class="flip-front">
              <h3>{{ $v['title'] }}</h3>
              <div class="flow-vert">
                @foreach($v['flow_steps'] as $step)
                  <div class="flow-step">{{ $step }}</div>
                @endforeach
              </div>
              <div class="flip-front-foot">
                <div class="service-hint">{{ $v['hint'] }}</div>
                <div class="flip-cue">Lihat detail <span aria-hidden="true">→</span></div>
              </div>
            </div>
            <div class="flip-back">
              <h3>{{ $v['title'] }}</h3>
              <div class="step-list">
                @foreach($v['back_steps'] as $step)
                  <div class="step-row"><b>{{ $step['title'] }}</b><span>{{ $step['description'] }}</span></div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
