<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Masuk | Anagata Warehouse</title>

        <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    </head>
    <body>
        <div class="auth-page">
            <a href="{{ route('home') }}">
                <x-application-logo />
            </a>

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
