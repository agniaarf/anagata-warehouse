@php
  $i = $cms['intro'];
  $poster = \App\Support\Cms::imageUrl(cms_field($i, 'poster_image'));
  $webm = \App\Support\Cms::imageUrl(cms_field($i, 'video_webm'));
  $mp4 = \App\Support\Cms::imageUrl(cms_field($i, 'video_mp4'));
  $overlay = max(0, min(100, (float) cms_field($i, 'overlay_opacity', '38'))) / 100;
  $rate = max(0.1, min(2, (float) cms_field($i, 'playback_rate', '0.6')));
@endphp
<section id="intro" class="intro-section" data-intro-show-once="{{ cms_field($i, 'show_once', '1') }}" data-analytics-section="intro" data-funnel-event="intro_view" style="--intro-overlay:{{ $overlay }};">
  <div class="intro-media" aria-hidden="true">
    @if($webm || $mp4)
      <video class="intro-video" poster="{{ $poster }}" autoplay muted loop playsinline preload="metadata" data-playback-rate="{{ $rate }}">
        @if($webm)<source src="{{ $webm }}" type="video/webm">@endif
        @if($mp4)<source src="{{ $mp4 }}" type="video/mp4">@endif
      </video>
    @else
      <img src="{{ $poster }}" alt="" fetchpriority="high" decoding="async">
    @endif
  </div>
  <div class="intro-shade" aria-hidden="true"></div>
  <div class="intro-content">
    <div class="intro-wordmark" aria-label="{{ cms_field($i, 'brand') }} {{ cms_field($i, 'subbrand') }}">
      <strong>{{ cms_field($i, 'brand') }}</strong>
      <span>{{ cms_field($i, 'subbrand') }}</span>
    </div>
    <p>{{ cms_field($i, 'hashtag') }}</p>
    <a href="#journey-stories" class="intro-cta" data-journey-start>{{ cms_field($i, 'cta_label') }}</a>
  </div>
  <div class="intro-portal" aria-hidden="true"></div>
</section>
