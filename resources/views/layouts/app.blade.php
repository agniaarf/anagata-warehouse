<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Pengelola situs | Anagata Warehouse</title>

        <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    </head>
    <body>
        <div class="admin-shell">
            @include('layouts.navigation')

            <main class="admin-main">
                <div class="admin-main-inner">
                    <div class="admin-topbar"><div><span class="admin-topbar-mark">AW</span><span>ANAGATA <i>/</i> PENGELOLA WEBSITE</span></div><a href="{{ route('landing.preview') }}" target="_blank" rel="noopener noreferrer">LIHAT PREVIEW <span aria-hidden="true">↗</span></a></div>
                    @if (session('status'))
                        <div class="field-status">{{ session('status') }}</div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>

        <script src="{{ asset('js/admin.js') }}"></script>
    </body>
</html>
