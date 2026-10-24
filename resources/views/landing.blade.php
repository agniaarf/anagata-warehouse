@php
  $seo = $cms['seo'];
  $ss = $cms['site_settings'];
  $hubs = cms_items($cms['location'])->filter(fn ($i) => $i['value']['status'] === 'hub');
  $offers = cms_items($seo)->filter(fn ($i) => str_starts_with($i['key'], 'offer_'));
  $wa = cms_field($ss, 'whatsapp_number');
  $email = cms_field($ss, 'email');
  $ig = cms_field($ss, 'instagram_handle');
  $ogImage = \App\Support\Cms::imageUrl(cms_field($ss, 'logo_dark'));
  $favicon = \App\Support\Cms::imageUrl(cms_field($ss, 'favicon'));
  $jkt = $hubs->first();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ cms_field($seo, 'meta_title') }}</title>
<meta name="description" content="{{ cms_field($seo, 'meta_description') }}">
<meta name="keywords" content="{{ cms_field($seo, 'meta_keywords') }}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
<meta name="author" content="Anagata Warehouse">
<meta name="geo.region" content="ID-JK">
<meta name="geo.placename" content="Jakarta Timur">
<meta name="theme-color" content="#0a0710">
<link rel="canonical" href="{{ url('/') }}">
{{-- Legacy multi-size .ico stays as the fallback for old browsers/taskbars —
     it's hand-generated (see CLAUDE.md) and can't be reproduced by a simple
     admin upload. The CMS-uploaded favicon (if any) is what modern browsers
     actually use, taking priority via source order. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@if($favicon)
  <link rel="icon" href="{{ $favicon }}" type="image/png" sizes="256x256">
  <link rel="apple-touch-icon" href="{{ $favicon }}">
@else
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" sizes="256x256">
  <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
@endif

<meta property="og:type" content="website">
<meta property="og:site_name" content="Anagata Warehouse">
<meta property="og:title" content="{{ cms_field($seo, 'og_title') }}">
<meta property="og:description" content="{{ cms_field($seo, 'og_description') }}">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="Logo Anagata Warehouse">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ cms_field($seo, 'og_title') }}">
<meta name="twitter:description" content="{{ cms_field($seo, 'og_description') }}">
<meta name="twitter:image" content="{{ $ogImage }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com">
<link rel="preload" as="image" href="{{ \App\Support\Cms::imageUrl(cms_items($cms['hero'])->first()['value']['image'] ?? '') }}" fetchpriority="high">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
<style>
  :root {
    --purple: {{ cms_field($ss, 'brand_color_primary', '#610890') }};
    --purple-glow: {{ cms_field($ss, 'brand_color_glow', '#a855f7') }};
    --purple-deep: {{ cms_field($ss, 'brand_color_deep', '#2a0440') }};
  }
</style>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "WebSite",
      "@@id": "{{ url('/') }}#website",
      "url": "{{ url('/') }}",
      "name": "Anagata Warehouse",
      "inLanguage": "id-ID",
      "publisher": { "@@id": "{{ url('/') }}#business" }
    },
    {
      "@@type": ["LocalBusiness", "ProfessionalService"],
      "@@id": "{{ url('/') }}#business",
      "name": "Anagata Warehouse",
      "alternateName": "CV Anagata Satria Nusantara",
      "slogan": "#Let'sGrowTogether",
      "description": "Partner fulfillment warehouse di Jakarta dan Makassar untuk seller online: storage, inbound, inventory management, pemrosesan order, packing, outbound, handover ke ekspedisi, dan pengelolaan return.",
      "image": "{{ $ogImage }}",
      "logo": "{{ $ogImage }}",
      "url": "{{ url('/') }}",
      "telephone": "+{{ $wa }}",
      "email": "{{ $email }}",
      "foundingDate": "2024",
      "priceRange": "$$",
      "currenciesAccepted": "IDR",
      "address": {
        "@@type": "PostalAddress",
        "streetAddress": "{{ $jkt['value']['area'] ?? '' }}",
        "addressLocality": "Jakarta Timur",
        "addressRegion": "{{ $jkt['value']['region'] ?? '' }}",
        "addressCountry": "ID"
      },
      "geo": { "@@type": "GeoCoordinates", "latitude": {{ $jkt['value']['lat'] ?? 0 }}, "longitude": {{ $jkt['value']['lng'] ?? 0 }} },
      "openingHoursSpecification": [{
        "@@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
        "opens": "08:00",
        "closes": "17:00"
      }],
      "areaServed": [
        { "@@type": "City", "name": "Jakarta" },
        { "@@type": "City", "name": "Makassar" },
        { "@@type": "City", "name": "Surabaya" },
        { "@@type": "City", "name": "Bandung" },
        { "@@type": "City", "name": "Medan" },
        { "@@type": "Country", "name": "Indonesia" }
      ],
      "location": [
        @foreach($hubs as $hub)
        {
          "@@type": "Place",
          "name": "{{ $hub['value']['name'] }}",
          "address": { "@@type": "PostalAddress", "addressLocality": "{{ $hub['value']['area'] }}", "addressRegion": "{{ $hub['value']['region'] }}", "addressCountry": "ID" }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
      ],
      "hasOfferCatalog": {
        "@@type": "OfferCatalog",
        "name": "Layanan Fulfillment Anagata Warehouse",
        "itemListElement": [
          @foreach($offers as $offer)
          { "@@type": "Offer", "itemOffered": { "@@type": "Service", "name": "{{ $offer['value']['name'] }}", "description": "{{ $offer['value']['description'] }}" } }{{ !$loop->last ? ',' : '' }}
          @endforeach
        ]
      },
      "sameAs": [
        "https://instagram.com/{{ $ig }}",
        "https://wa.me/{{ $wa }}"
      ]
    }
  ]
}
</script>
</head>
<body data-analytics-enabled="{{ auth()->check() ? 'false' : 'true' }}" data-analytics-endpoint="{{ route('analytics.collect', [], false) }}" data-analytics-session-timeout="{{ config('analytics.session_timeout_minutes', 30) }}" data-analytics-visitor-days="{{ config('analytics.visitor_id_days', 90) }}">

@include('partials.nav')
@php
  $layout = cms_items($cms['page_layout'])->sortBy('order');
  $pageSections = config('page_sections');
@endphp
@foreach($layout as $entry)
  @continue(! $entry['value']['visible'])
  @include($pageSections[$entry['key']]['view'])
@endforeach

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

</body>
</html>
