@php
  $g = $cms['gallery'];
  $items = cms_items($g);
  $altSuffix = cms_field($cms['seo'], 'image_alt_suffix');
  // The homepage shows a fixed 8-tile preview so the section height never grows
  // with the photo count; everything else lives in the lightbox. Grid tiles use
  // thumbnails, the lightbox loads the full-size image only when opened.
  $previewCount = 8;
  $photos = $items->map(fn ($item) => [
      'thumb' => \App\Support\Cms::thumbUrl($item['value']['src']),
      'full' => \App\Support\Cms::largeUrl($item['value']['src']),
      'caption' => $item['value']['caption'],
      'alt' => trim($item['value']['caption'].', '.$altSuffix, ', '),
  ])->values();
  $total = $photos->count();
  $hasMore = $total > $previewCount;
@endphp
<!-- GALLERY -->
<section id="gallery" data-analytics-section="gallery">
  <div class="wrap">
    <div class="gallery-head-simple">
      <div class="eyebrow">{{ cms_field($g, 'eyebrow') }}</div>
      <h2>{{ cms_field($g, 'title') }}</h2>
      <p>{{ cms_field($g, 'description') }}</p>
    </div>
    <div class="gallery-bento" data-count="{{ min($total, $previewCount) }}">
      @foreach($photos->take($previewCount) as $i => $photo)
        @php $isMoreTile = $hasMore && $i === $previewCount - 1; @endphp
        <button type="button" class="bento-item @if($isMoreTile) bento-more @endif" data-gallery-open="{{ $i }}"
                aria-label="{{ $isMoreTile ? 'Lihat '.($total - $previewCount + 1).' foto lainnya' : 'Perbesar foto: '.$photo['caption'] }}">
          <img src="{{ $photo['thumb'] }}" loading="lazy" decoding="async" alt="{{ $photo['alt'] }}">
          @if($isMoreTile)
            <span class="bento-more-count">+{{ $total - $previewCount + 1 }}</span>
          @else
            <span class="gal-cap">{{ $photo['caption'] }}</span>
          @endif
        </button>
      @endforeach
    </div>
    @if($hasMore)
      <div class="gallery-more">
        <button type="button" class="btn btn-ghost hero-cta-arrow" data-gallery-open="0">
          {{ cms_field($g, 'view_all_label', 'Lihat semua foto') }} ({{ $total }})
        </button>
      </div>
    @endif
  </div>

  @if($total)
    <dialog class="lightbox" id="galleryLightbox" aria-label="{{ cms_field($g, 'title') }}">
      <div class="lb-top">
        <span class="lb-counter" aria-live="polite"></span>
        <button type="button" class="lb-close" aria-label="Tutup">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </div>
      <div class="lb-stage">
        <button type="button" class="lb-nav lb-prev" aria-label="Foto sebelumnya">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
        </button>
        <figure class="lb-figure">
          <img class="lb-image" alt="" loading="eager" decoding="async">
          <figcaption class="lb-caption"></figcaption>
        </figure>
        <button type="button" class="lb-nav lb-next" aria-label="Foto berikutnya">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
      <div class="lb-strip" role="list"></div>
      <script type="application/json" class="lb-data">@json($photos)</script>
    </dialog>
  @endif
</section>
