@php
  $s = $cms['collage_finale'];
  $photos = cms_items($s)->filter(fn ($item) => str_starts_with($item['key'], 'photo_'));
@endphp
<section id="collage-finale" class="collage-finale" data-analytics-section="collage_finale" data-funnel-event="collage_complete">
  <div class="collage-intro reveal-on-scroll"><span>THE PEOPLE BEHIND THE JOURNEY</span><p>Setiap perjalanan punya cerita.<br>Ini adalah cerita kita bersama.</p></div>
  <div class="collage-stage" aria-label="Kolase aktivitas dan manusia di Anagata Warehouse">
    @foreach($photos as $photo)
      <figure class="collage-tile collage-tile--{{ ($loop->index % 8) + 1 }} reveal-on-scroll">
        <img src="{{ \App\Support\Cms::thumbUrl($photo['value']['image'] ?? '') }}" alt="{{ $photo['value']['alt'] ?? '' }}" loading="lazy" decoding="async">
      </figure>
    @endforeach
  </div>
  <div class="collage-message reveal-on-scroll">
    <h2>{!! cms_rich_text(cms_field($s, 'headline')) !!}</h2>
    <p>{{ cms_field($s, 'hashtag') }}</p>
  </div>
</section>
