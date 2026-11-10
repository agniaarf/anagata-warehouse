@php
  $seo = $cms['seo'];
  $ss = $cms['site_settings'];
  $locations = cms_items($cms['location'])->filter(fn ($item) => in_array($item['value']['status'] ?? '', ['hub', 'coming', 'listed'], true))->map(fn ($item) => [
    'name' => $item['value']['name'] ?? '',
    'area' => $item['value']['area'] ?? '',
    'address' => $item['value']['address'] ?? '',
    'status' => $item['value']['status'],
    'lat' => isset($item['value']['lat']) && is_numeric($item['value']['lat']) ? (float) $item['value']['lat'] : null,
    'lng' => isset($item['value']['lng']) && is_numeric($item['value']['lng']) ? (float) $item['value']['lng'] : null,
  ])->values();
  $partners = cms_items($cms['marquee'])->map(function ($item) {
    $path = $item['value']['path'] ?? '';
    $parts = parse_url($path);
    if (is_array($parts) && in_array(strtolower($parts['host'] ?? ''), ['localhost', '127.0.0.1'], true) && str_starts_with($parts['path'] ?? '', '/partner-logos/')) {
      $path = $parts['path'];
    }
    return ['name' => $item['value']['alt'] ?? '', 'image' => \App\Support\Cms::imageUrl($path)];
  })->filter(fn ($partner) => $partner['name'] && $partner['image'])->values();
  $anagataContent = [];
  foreach (['story', 'service', 'activity', 'photo', 'section'] as $group) {
    $anagataContent[$group] = cms_items($cms['anagata_site'])
      ->filter(fn ($item) => str_starts_with($item['key'], $group.'_'))
      ->map(function ($item) use ($group) {
        $value = $item['value'];
        if (! empty($value['image'])) $value['image'] = $group === 'photo' ? \App\Support\Cms::thumbUrl($value['image']) : \App\Support\Cms::imageUrl($value['image']);
        return $value;
      })->values();
  }
  $ogImage = \App\Support\Cms::imageUrl(cms_field($ss, 'logo_dark'));
  $anagataSettings = [
    'logo' => \App\Support\Cms::imageUrl(cms_field($ss, 'logo_light')),
    'email' => cms_field($ss, 'email'),
    'whatsapp' => cms_field($ss, 'whatsapp_number'),
    'instagram' => cms_field($ss, 'instagram_handle'),
  ];
  $organization = [
    '@context' => 'https://schema.org', '@type' => 'Organization',
    'name' => 'Anagata Warehouse', 'url' => url('/'),
    'logo' => $ogImage, 'email' => $anagataSettings['email'],
    'telephone' => $anagataSettings['whatsapp'] ? '+'.$anagataSettings['whatsapp'] : '',
  ];
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#080908">
  @if($isPreview ?? false)<meta name="robots" content="noindex, nofollow">@endif
  <title>{{ cms_field($seo, 'meta_title') }}</title>
  <meta name="description" content="{{ cms_field($seo, 'meta_description') }}">
  <meta name="keywords" content="{{ cms_field($seo, 'meta_keywords') }}">
  <link rel="canonical" href="{{ url('/') }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ cms_field($seo, 'og_title') }}">
  <meta property="og:description" content="{{ cms_field($seo, 'og_description') }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta property="og:url" content="{{ url('/') }}">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="{{ cms_field($seo, 'og_title') }}">
  <meta name="twitter:description" content="{{ cms_field($seo, 'og_description') }}">
  <meta name="twitter:image" content="{{ $ogImage }}">
  <script type="application/ld+json">@json($organization)</script>
  <link rel="stylesheet" href="{{ asset('anagata-assets/app.css') }}?v={{ filemtime(public_path('anagata-assets/app.css')) }}">
  <script type="module" src="{{ asset('anagata-assets/app.js') }}?v={{ filemtime(public_path('anagata-assets/app.js')) }}"></script>
</head>
<body data-analytics-enabled="{{ auth()->check() || ($isPreview ?? false) ? 'false' : 'true' }}" data-analytics-endpoint="{{ route('analytics.collect', [], false) }}" data-analytics-session-timeout="{{ config('analytics.session_timeout_minutes', 30) }}" data-analytics-visitor-days="{{ config('analytics.visitor_id_days', 90) }}">
  <div id="root"></div>
  <script>window.__ANAGATA_LOCATIONS__ = @json($locations);</script>
  <script>window.__ANAGATA_PARTNERS__ = @json($partners);</script>
  <script>window.__ANAGATA_CONTENT__ = @json($anagataContent);</script>
  <script>window.__ANAGATA_SETTINGS__ = @json($anagataSettings);</script>
  <script defer src="{{ asset('js/anagata-analytics.js') }}?v={{ filemtime(public_path('js/anagata-analytics.js')) }}"></script>
  <noscript>
    <main style="max-width: 70rem; margin: 3rem auto; padding: 0 1.5rem; color: #fff; font-family: sans-serif; line-height: 1.6">
      <h1>Anagata Warehouse</h1>
      <p>{{ cms_field($seo, 'meta_description') }}</p>
      <h2>Layanan kami</h2>
      @foreach ($anagataContent['service'] as $service)
        <section>
          <h3>{{ $service['name'] ?? '' }}</h3>
          <p>{{ $service['subtitle'] ?? '' }}</p>
          <p>{{ $service['body'] ?? '' }}</p>
        </section>
      @endforeach
      <h2>Lokasi</h2>
      @foreach ($locations as $location)
        <section>
          <h3>{{ $location['name'] }}</h3>
          @if ($location['status'] === 'coming') <p>Segera hadir</p> @endif
          <p>{{ $location['address'] }}</p>
        </section>
      @endforeach
      @if ($anagataSettings['email']) <p>Kontak: <a href="mailto:{{ $anagataSettings['email'] }}">{{ $anagataSettings['email'] }}</a></p> @endif
      <p>Aktifkan JavaScript untuk melihat pengalaman website lengkap.</p>
    </main>
  </noscript>
</body>
</html>
