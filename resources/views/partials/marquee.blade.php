@php
  $logos = cms_items($cms['marquee']);
@endphp
<!-- Partner logos continue from Hero without a separate label or background. -->
<div class="marquee-section" data-analytics-section="marquee">
  <div class="marquee-track" id="marquee">
    @foreach($logos as $logo)
      <span class="mq-logo"><img loading="lazy" decoding="async" src="{{ \App\Support\Cms::imageUrl($logo['value']['path']) }}" alt="{{ $logo['value']['alt'] }}"></span>
    @endforeach
  </div>
</div>
