@php
  $a = $cms['about'];
  $misiBullets = json_decode(cms_field($a, 'misi_bullets', '[]'), true) ?: [];
@endphp
<!-- ABOUT / VISI MISI -->
<section id="about" data-analytics-section="about">
  <div class="wrap about-grid">
    <div>
      <div class="eyebrow">{{ cms_field($a, 'eyebrow') }}</div>
      <h2>{!! cms_rich_text(cms_field($a, 'headline')) !!}</h2>
      <p>{!! cms_rich_text(cms_field($a, 'paragraph_1')) !!}</p>
      <p>{!! cms_rich_text(cms_field($a, 'paragraph_2')) !!}</p>
      <div class="about-photo">
        <img src="{{ \App\Support\Cms::imageUrl(cms_field($a, 'photo_image')) }}" loading="lazy" decoding="async" alt="{{ cms_field($a, 'photo_alt') }}">
        <span>{{ cms_field($a, 'photo_caption') }}</span>
      </div>
    </div>
    <div>
      <div class="vm-card">
        <h3>{{ cms_field($a, 'visi_title') }}</h3>
        <p style="color:var(--ink);margin:0;">{{ cms_field($a, 'visi_text') }}</p>
      </div>
      <div class="vm-card">
        <h3>{{ cms_field($a, 'misi_title') }}</h3>
        <ul>
          @foreach($misiBullets as $bullet)
            <li>{{ $bullet }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</section>
