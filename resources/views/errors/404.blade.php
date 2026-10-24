<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Halaman Tidak Ditemukan | Anagata Warehouse</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --purple:#610890; --purple-glow:#a855f7; --purple-deep:#2a0440;
    --bg:#0a0710; --ink:#f4f1f7; --muted:#ad9fc2; --line:rgba(255,255,255,.10);
  }
  *{box-sizing:border-box;}
  body{
    margin:0; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;
    background:var(--bg); color:var(--ink); font-family:Manrope, ui-sans-serif, system-ui, sans-serif;
    text-align:center; padding:32px 20px;
  }
  .logo{height:40px; margin-bottom:36px;}
  .code{
    font-family:'Instrument Serif', Georgia, serif; font-size:clamp(72px,14vw,140px); line-height:1;
    color:var(--purple-glow); margin:0;
  }
  h1{font-family:'Instrument Serif', Georgia, serif; font-weight:400; font-size:clamp(22px,3vw,30px); margin:18px 0 10px;}
  p{color:var(--muted); font-size:15px; max-width:420px; margin:0 0 28px;}
  .btn{
    display:inline-flex; align-items:center; gap:8px; background:var(--purple); color:#fff;
    text-decoration:none; padding:12px 26px; border-radius:8px; font-size:14px; font-weight:600;
    transition:background .15s;
  }
  .btn:hover{background:var(--purple-deep);}
</style>
</head>
<body>
  <img src="{{ asset('images/logo-light.png') }}" alt="Anagata Warehouse" class="logo" loading="eager" decoding="async">
  <p class="code">404</p>
  <h1>Halaman yang kamu cari tidak ditemukan.</h1>
  <p>Mungkin link-nya salah ketik, atau halamannya sudah dipindahkan. Yuk balik ke beranda.</p>
  <a href="{{ route('home') }}" class="btn">← Kembali ke Beranda</a>
</body>
</html>
