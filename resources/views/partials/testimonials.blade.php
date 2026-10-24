@php
  $t = $cms['testimonials'];
  $testimonials = cms_items($t);
@endphp
<!-- TESTIMONIALS -->
<section class="testi-section" data-analytics-section="testimonials">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">{{ cms_field($t, 'eyebrow') }}</div>
      <h2>{!! cms_rich_text(cms_field($t, 'headline')) !!}</h2>
    </div>

    <div class="testi-cycle" id="testiCycle">
      <div class="testi-track">
        @foreach($testimonials as $i => $item)
          @php $v = $item['value']; @endphp
          <div class="testi-slide @if($i === 0) active @endif">
            <p class="testi-quote-big">&ldquo;{{ $v['quote'] }}&rdquo;</p>
            <div class="testi-who">
              <span class="testi-avatar">{{ $v['initials'] }}</span>
              <div>
                <div class="testi-name">{{ $v['name'] }}</div>
                <div class="testi-role">{{ $v['role'] }}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="testi-dots" id="testiDots"></div>
    </div>
  </div>
</section>
