@php
  $h = $cms['hero'];
  $slides = cms_items($h);
  // One real stat per slide (same 4 numbers already shown in Stats — not new
  // claims, just distributed across the carousel instead of repeating #1).
  $heroStats = cms_items($cms['stats']);
@endphp
<!-- HERO -->
<header class="hero" id="home" data-analytics-section="hero">
  {{-- full-bleed background slider (as in the original static landing page);
       overlay keeps the copy readable and fades into the marquee below --}}
  <div class="hero-carousel" id="heroCarousel" aria-hidden="true">
    @foreach($slides as $i => $slide)
      @php $imageUrl = \App\Support\Cms::imageUrl($slide['value']['image']); @endphp
      <div class="hero-slide @if($i === 0) active @endif" data-background="{{ $imageUrl }}" @if($i === 0) style="background-image:url('{{ $imageUrl }}')" @endif></div>
    @endforeach
  </div>
  <div class="hero-overlay"></div>
  @include('partials._silk', ['variant' => 'hero', 'line' => false])

  <div class="wrap hero-inner">
    <div class="hero-tag">{{ cms_field($h, 'tag') }}</div>
    <h1>{!! cms_rich_text(cms_field($h, 'headline')) !!}</h1>
    <div class="hero-hash">{{ cms_field($h, 'hashtag') }}</div>
    {{-- 'brand' field stays editable in Website > Hero (CMS) but isn't
         rendered here anymore — the wordmark line was redundant on-page. --}}
    <p>{!! cms_rich_text(cms_field($h, 'paragraph')) !!}</p>
    <div class="hero-actions">
      <a href="{{ cms_field($h, 'cta_primary_href') }}" class="btn btn-solid">{{ cms_field($h, 'cta_primary_label') }}</a>
      <a href="{{ cms_field($h, 'cta_secondary_href') }}" class="btn btn-ghost hero-cta-arrow">{{ cms_field($h, 'cta_secondary_label') }}</a>
    </div>
  </div>

  <div class="hero-meta">
    <div class="wrap hero-meta-inner">
      <div class="hero-stats">
        @foreach($slides as $i => $slide)
          @php $stat = $heroStats[$i] ?? null; @endphp
          @if($stat)
            <div class="hero-stat-overlay @if($i === 0) active @endif" data-index="{{ $i }}">
              <span class="hero-stat-num">{{ $stat['value']['number'] }}</span>
              <span class="hero-stat-label">{{ $stat['value']['label'] }}</span>
            </div>
          @endif
        @endforeach
      </div>
      <div class="hero-dots" id="heroDots"></div>
    </div>
  </div>
</header>
