@php
  $layout = cms_items($cms['page_layout']);
  $visible = fn ($key) => $layout->contains(fn ($item) => $item['key'] === $key && $item['value']['visible']);
  $logo = \App\Support\Cms::imageUrl(cms_field($cms['site_settings'], 'logo_light'));
@endphp
<nav class="main-nav" data-main-nav aria-label="Navigasi utama">
  <div class="logo"><a href="#working-activity" data-hub-home aria-label="Anagata Warehouse: kembali ke menu utama"><img src="{{ $logo }}" alt="Anagata Warehouse" loading="eager" decoding="async"></a></div>
  <ul class="nav-menu">
    @if($visible('collage_finale'))<li><a href="#working-activity" data-activity-nav>Activity</a></li>@endif
    @if($visible('inbound'))<li><a href="#services" data-services-nav>Services</a></li>@endif
    @if($visible('location'))<li><a href="#location">Locations</a></li>@endif
    @if($visible('footer'))<li><a href="#contact">Contact</a></li>@endif
  </ul>
  <div class="nav-right"><button type="button" class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navMobile">☰</button></div>
</nav>
<div class="nav-mobile" id="navMobile">
  @if($visible('collage_finale'))<a href="#working-activity" data-activity-nav>Activity</a>@endif
  @if($visible('inbound'))<a href="#services" data-services-nav>Services</a>@endif
  @if($visible('location'))<a href="#location">Locations</a>@endif
  @if($visible('footer'))<a href="#contact">Contact</a>@endif
</div>
