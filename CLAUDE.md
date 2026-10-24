# Anagata Warehouse — Landing Page + CMS (Laravel)

Landing page Anagata Warehouse, aplikasi **Laravel** dengan CMS/admin panel penuh
di `/bojong` (sebelumnya single-file HTML statis `anagata-landing.html`, lalu
sempat jadi Laravel tanpa database sebelum CMS ditambahkan). Jalankan dengan:
```bash
composer install
php artisan migrate
php artisan db:seed
php artisan db:seed --class=AdminUserSeeder
php artisan serve
```
lalu buka `http://127.0.0.1:8000` (landing page) atau `http://127.0.0.1:8000/bojong`
(admin — login pakai `ADMIN_SEED_EMAIL`/`ADMIN_SEED_PASSWORD` dari `.env`).

**Baca juga** (dokumentasi lengkap, ditulis terpisah supaya file ini gak makin
membengkak):
- [`docs/CODEBASE.md`](docs/CODEBASE.md) — peta arsitektur lengkap: routing, request flow, direktori.
- [`docs/DATABASE.md`](docs/DATABASE.md) — skema tiap tabel, kolom, relasi.
- [`app/Http/Controllers/Admin/AGENTS.md`](app/Http/Controllers/Admin/AGENTS.md) — cara kerja CMS schema-driven (nambah field/section baru), aturan seeder, image handling.

## CMS & Admin Panel — konten sekarang dikelola lewat `/bojong`, BUKAN edit Blade

⚠️ **Penting**: sejak CMS ditambahkan, hampir semua konten (headline, foto, alamat
gudang, stat, testimoni, dll) **TIDAK LAGI hardcoded di file Blade** — semuanya
disimpan di tabel `cms_contents` dan diedit lewat admin panel `/bojong`. Kalau mau
ubah copy/foto/data gudang, login ke `/bojong` dan edit lewat form, JANGAN cari
teksnya di `resources/views/partials/*.blade.php` (partial itu sekarang cuma
"template kosong" yang membaca data dari CMS via helper `cms_field()`/`cms_items()`).
Detail lengkap ada di `app/Http/Controllers/Admin/AGENTS.md`.

Dua akun admin saat ini (tanpa role/permission — keduanya akses penuh yang sama):
`anagatafulfill@gmail.com` dan `anagatabojong@gmail.com` (password di `.env`/`.env.production`,
lihat `ADMIN_SEED_EMAIL`/`ADMIN_SEED_EMAIL_2`).

## Deploy ke production — Laravel penuh dengan database

Situs ini sekarang **butuh Laravel + MySQL aktif** (CMS, auth admin, dan analytics
semuanya baca/tulis database) — bukan lagi static-HTML-only seperti versi awal.
Kredensial database production ada di `.env.production` (jangan pernah commit file
ini ke git kalau nanti `git init` — sudah ada di `.gitignore`).

**Langkah deploy pertama kali:**
1. Pastikan hosting punya SSH + Composer + PHP yang kompatibel dengan
   `composer.json` (`laravel/framework: ^9.19`, butuh PHP 8.0.2+) dan MySQL/MariaDB.
   ⚠️ **InfinityFree free tier TIDAK BISA dipakai untuk ini** — inode limit-nya
   (jumlah file+folder per akun) jauh lebih kecil dari jumlah file
   `vendor/` Laravel (6.000+ file); upload lewat FTP pernah gagal persis di angka
   segitu. Kalau butuh hosting gratis/murah, cari yang eksplisit punya SSH+Composer
   (mis. Hostinger/Niagahoster shared hosting berbayar, ~Rp30-50rb/bulan).
2. Upload seluruh project (termasuk hasil `composer install --no-dev --optimize-autoloader`)
   dengan **document root diarahkan ke folder `public/`** — ini penting supaya
   `app/`, `config/`, `.env.production`, dll TIDAK bisa diakses langsung lewat URL.
   Kalau hosting panel tidak bisa mengarahkan document root ke subfolder (beberapa
   shared host cuma bisa serve dari `public_html/` apa adanya), upload seluruh
   folder Laravel ke `public_html/` dan pasang `deploy-app-htaccess.txt` (`Require
   all denied` / `Deny from all`) sebagai `.htaccess` di tiap folder non-public
   (`app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `storage/`, `vendor/`)
   untuk memblokir akses langsung — cara ini kurang ideal (folder-folder itu tetap
   ada di dalam webroot) tapi jadi jaring pengaman kalau document-root redirect
   tidak tersedia.
3. Salin `.env.production` jadi `.env` di server (atau set environment variables
   yang sama lewat hosting panel kalau tersedia).
4. `php artisan key:generate --force` **hanya kalau** `APP_KEY` di `.env.production`
   belum diisi (saat ini sudah diisi, jangan generate ulang — itu akan bikin semua
   sesi/cookie terenkripsi lama tidak valid).
5. `php artisan migrate --force` — bikin skema tabel di database production.
   **Ini satu-satunya perintah database yang boleh dijalankan ulang kapan pun**
   (idempotent, cuma menjalankan migration yang belum pernah jalan).
6. `php artisan db:seed --force` — **JALANKAN SEKALI SAJA**, saat pertama kali
   deploy, untuk mengisi `cms_contents` dengan konten awal (sama seperti yang ada
   di local dev sekarang — itu maksud "yang sekarang nanti ke production disamain").
   ⚠️ **JANGAN PERNAH jalankan ini lagi setelah admin mulai edit konten lewat UI**
   — `CmsContentSeeder` pakai `updateOrCreate`, jadi re-seed akan MENIMPA konten
   asli balik ke placeholder tanpa konfirmasi apa pun. Setelah go-live, satu-
   satunya cara ubah konten adalah lewat `/bojong`. Detail di `docs/DATABASE.md`.
7. `php artisan db:seed --force --class=AdminUserSeeder` — bikin 2 akun admin dari
   `ADMIN_SEED_EMAIL`/`ADMIN_SEED_EMAIL_2` di `.env.production`. Aman dijalankan
   ulang kapan pun (tidak menyentuh `cms_contents`) — misalnya kalau mau reset
   password lewat env var lalu re-run seeder ini.
8. `php artisan storage:link` — supaya file upload CMS (lewat `/bojong/uploads`)
   bisa diakses publik dari `public/storage/`.
   Kalau ada foto yang di-upload SEBELUM fitur thumbnail ada, jalankan juga
   `php artisan cms:thumbnails` (aman diulang kapan pun; cuma bikin `.thumb.webp`
   yang belum ada — halaman tetap jalan tanpa ini, cuma pakai file ukuran penuh).
9. Pasang **Laravel Scheduler** di crontab server: `* * * * * php /path/ke/artisan
   schedule:run >> /dev/null 2>&1` — ini yang menjalankan `analytics:prune` tiap
   hari jam 02:30 WIB untuk membersihkan data analytics >90 hari. Tanpa ini,
   analytics tetap jalan normal tapi data lama tidak pernah dihapus otomatis.
10. Segera setelah login pertama kali di `/bojong`, ganti kedua password admin
    lewat halaman profil, lalu hapus/ganti `ADMIN_SEED_PASSWORD`/`ADMIN_SEED_PASSWORD_2`
    di `.env.production` supaya password lama tidak tertinggal di file (seeder
    tetap aman dijalankan ulang, `updateOrCreate` di email yang sama hanya reset
    password kalau env var itu memang diisi ulang).

**⚠️ Sebelum/segera setelah deploy pertama** — cek `docs/CODEBASE.md`'s bagian
"Dependency/security note": `laravel/framework` masih di `^9.19` (security support
berakhir 2024-02-06), dan `composer audit` melaporkan 12 advisory di beberapa
paket Symfony yang masih di versi Januari 2023. Jalankan `composer update
symfony/mailer symfony/mime symfony/process symfony/routing symfony/http-kernel
symfony/http-foundation --with-all-dependencies` di lingkungan dengan akses
internet (bukan sandbox tanpa akses packagist), lalu `php artisan test`, sebelum
situs benar-benar publik.

**Mail belum dikonfigurasi** — `.env.production` masih `MAIL_MAILER=log`, artinya
email reset password ditulis ke log, bukan benar-benar terkirim. Ini tidak
menghalangi go-live (kedua akun admin sudah tahu passwordnya), tapi perlu SMTP
asli kalau nanti butuh fitur lupa-password beneran.

### Alternatif: static HTML export (kalau suatu saat butuh hosting gratis TANPA CMS/analytics)

Kalau situs pernah perlu dijalankan tanpa backend sama sekali (mis. demo cepat di
hosting gratis yang tidak punya SSH/Composer/MySQL), export statis MASIH bisa
dipakai — TAPI ini menonaktifkan CMS admin dan analytics sepenuhnya (snapshot HTML
sekali render, tidak nyambung ke database). Cocok hanya untuk skenario
demo/portofolio, bukan untuk deployment utama sekarang bahwa CMS sudah jadi cara
utama mengelola konten.

```bash
# 1. Pastikan dev server jalan
php artisan serve
# 2. Capture HTML hasil render (dari terminal lain)
curl -s http://127.0.0.1:8000/ -o ../anagata-static-export/index.html
# 3. Ganti semua URL localhost jadi domain asli (asset()/url() nempel host request saat di-capture)
sed -i 's|http://127.0.0.1:8000|https://anagatawarehouse.com|g' ../anagata-static-export/index.html
# Static export has no Laravel collector: disable the tracker and its notice in this exported copy.
sed -i 's/data-analytics-enabled="true"/data-analytics-enabled="false"/' ../anagata-static-export/index.html
sed -i '/class="analytics-disclosure"/d' ../anagata-static-export/index.html
# 4. Copy ulang css & js — WAJIB tiap kali, JANGAN di-skip biarpun cuma teks yang diubah.
cp public/css/app.css ../anagata-static-export/css/app.css
cp public/js/app.js ../anagata-static-export/js/app.js
cp public/robots.txt public/sitemap.xml public/favicon.ico ../anagata-static-export/
# Tile background halaman (dipakai app.css lewat ../images/silk-tile.svg) — WAJIB ikut.
cp public/images/silk-tile.svg ../anagata-static-export/images/
```
Zip pakai Python (bukan PowerShell `Compress-Archive` — itu pakai backslash di path
internal zip, gak dikenali unzip Linux):
```bash
python3 -c "
import zipfile, os
src = '../anagata-static-export'
with zipfile.ZipFile('../anagata-static-export.zip', 'w', zipfile.ZIP_DEFLATED) as zf:
    for root, dirs, files in os.walk(src):
        for f in files:
            full = os.path.join(root, f)
            rel = os.path.relpath(full, src).replace(os.sep, '/')
            zf.write(full, rel)
"
```
Upload ke hosting statis manapun (extract di document root). Hasil akhirnya HARUS
cuma `index.html`, `robots.txt`, `sitemap.xml`, folder `css/`, `js/`, `images/`,
`partner-logos/` — tidak ada `index.php`, tidak ada folder Laravel, tidak perlu
`.htaccess` sama sekali (situs statis satu halaman tidak butuh URL rewrite).

## Analytics pengunjung — perlu Laravel + database aktif (sudah aktif di dev & production)

Dashboard analytics ada di `/bojong/analytics`. Kode ini mencatat sesi serta
section yang dilihat dan kanal kontak yang diklik secara first-party (tanpa
menyimpan IP mentah); sesi/event disimpan selama 90 hari lalu dihapus otomatis
oleh `analytics:prune` (perlu cron scheduler aktif, lihat langkah 9 deploy di
atas). Fitur ini **tidak mengumpulkan data sama sekali kalau situs dijalankan
sebagai static export** (lihat bagian di atas) karena HTML statis tidak
menyediakan endpoint `/analytics/collect` maupun database. Detail skema tabel di
`docs/DATABASE.md`.

## Stack
- **Laravel 9** (Blade landing page dan admin CMS di `/bojong`). CMS, autentikasi
  admin, dan analytics memakai MySQL di semua environment (dev & production sama-
  sama `DB_CONNECTION=mysql`); session/cache/queue tetap driver `file`/`sync`
  (bukan database) di semua environment — lihat `docs/DATABASE.md` soal tabel
  Laravel default yang sudah dihapus karena gak pernah dipakai.
- **Wordmark hero** (`.hero-brand` di `public/css/app.css`): "ANAGATA WAREHOUSE"
  STATIS — violet (`--purple-glow`), ukuran `clamp(28px,3.8vw,46px)`, tanpa animasi
  sama sekali. Sempat ada rangkaian animasi (typewriter `steps()` → clip-path wipe
  + shine effect → glow pulsa `glowSoft`), semuanya dicabut atas permintaan klien.
- **TIDAK ADA lagi aksen `.glow-orb`** (blob radial-gradient ungu blur di pojok
  tiap section) — dihapus total dari semua section + CSS (`@keyframes orbDrift`)
  atas permintaan klien. Dikonfirmasi masih bersih per audit pre-production
  (2026-09) — kalau nanti ada rule CSS untuk class ini muncul lagi, itu regresi.
- **TIDAK ADA lagi background grid kotak-kotak** (`.grid-accent`, fixed full-page)
  — dihapus atas permintaan klien (2026-09). Gantinya aksen **"silk"**
  (`partials/_silk.blade.php` + `.silk` di `app.css`): sapuan cahaya lembut ala kain
  + grain halus, diambil dari background Business Profile klien (hitam, kilau abu,
  bocoran violet). Komponen `_silk` sekarang cuma di Hero, **tanpa garis tepi
  mint→lavender** (`'line' => false`, permintaan klien 2026-09-30; jangan
  dinyalakan lagi); background halaman pakai silk yang sama dalam bentuk tile
  `repeat-y` (lihat bawah). `_silk.blade.php` menerima `uid` (id SVG unik) dan `class`.
- **Background halaman SATU, menyambung dari atas sampai bawah** (permintaan klien
  2026-09-30: "berkesinambungan, ga keputus-putus, satu theme"). **Semua section
  transparan** — tidak ada lagi background/border per section (`.testi-section`/
  `footer` dulu `--bg-2` + border, "Section washes" per section dan `silk--section`
  di Services/About/Location/Gallery/Value Prop sudah dicabut karena bikin kesan
  terpotong-potong). Kalau section baru diberi background/border sendiri, patahannya
  balik lagi.
  **Final (dipilih klien 2026-10-01): tile silk `repeat-y`** —
  `public/images/silk-tile.svg` (1440×1600, latar transparan) dipasang di `body`
  dengan `background-repeat: repeat-y`, ikut ter-scroll bersama konten. Semua
  kilau/glow di tile sengaja jauh dari tepi atas-bawah supaya sambungan antar-tile
  selalu jatuh di area gelap polos (tidak ada seam). `background-size:
  max(100%, 1100px) auto` → di HP sisi kiri-kanan terpotong, silk tidak mengecil.
  Kalau edit tile: jangan pakai `--` di dalam komentar XML (bikin SVG invalid, tidak
  ter-render sama sekali), dan region filter blur (`userSpaceOnUse`) dihitung di
  koordinat LOKAL path yang di-`transform` — kalau terlalu sempit, blur terpotong
  jadi garis keras. (Kandidat lain yang ditolak: glow statis di `body`, gradasi
  nada, garis/pita cahaya menjalar, backdrop `position:fixed` + scroll-driven
  animation.)
  Catatan teknis: foto+overlay hero di-`mask-image` fade ke transparan di bagian
  bawah supaya melebur ke background di belakangnya (tanpa garis tepi). Jangan
  taruh glow di `<div>` overlay full-height (pernah dicoba: rasterize-nya sangat
  lambat) dan jangan tambah grain di `body` (bikin garis tipis di tepi bawah hero).
  Jangan dijadikan blob/orb di pojok (lihat `.glow-orb`).
  Elemen lain dari deck yang sama (juga sengaja terbatas): bracket sudut violet garis-dobel menyambung + sudut membulat di `.value-card::after` (penempatan dari sketsa klien: card 1 kiri-bawah, 2 kiri-atas, 3 kanan-bawah, 4 kanan-atas — terinspirasi slide "Kenapa Anagata?") dan
  flow vertikal di depan flip card Services dengan garis penghubung titik-titik
  (slide "Inbound Flow"). Watermark logo AW vertikal (`.contact-mark`) dan silk di
  Contact sudah **dicabut** atas permintaan klien (2026-09-30) — jangan dipasang lagi.
- **Aksesibilitas gerakan**: blanket rule `@media(prefers-reduced-motion: reduce)`
  di akhir `public/css/app.css`; count-up stats & fly-to peta di `app.js` juga cek
  `window.matchMedia('(prefers-reduced-motion: reduce)')` terpisah.
- CSS & JS **tidak** lewat Vite/build step — file statis di `public/css/app.css`
  dan `public/js/app.js`, di-load langsung via `asset()`. Scaffold Vite bawaan
  Laravel (`vite.config.js`, `resources/css/app.css`, dst) tidak pernah dipakai —
  boleh diabaikan/dihapus, tidak mempengaruhi apa pun.
- Font: **Instrument Serif** (display/heading) + **Manrope** (body) via Google Fonts.
- **Leaflet.js 1.9.4** (via cdnjs), tile **Esri `World_Light_Gray_Base`** (keyless,
  gratis). Peta selalu `fitBounds()` ke seluruh Indonesia, bukan `setView()` fixed-zoom.
- **Peta lokasi — data sekarang dari CMS, bukan hardcoded di Blade**: koordinat/
  alamat/status tiap gudang disimpan di `cms_contents` (section `location`, repeater
  `card`), diedit lewat `/bojong/content/location/card`. `location.blade.php`
  cuma me-render `data-lat`/`data-lng`/`data-hub`/class `.coming` dari data CMS ke
  DOM; `app.js` membaca atribut itu dari DOM saat map di-init — jadi kalau nambah/
  ubah gudang, edit lewat admin panel, **BUKAN** edit Blade file (lihat
  `app/Http/Controllers/Admin/AGENTS.md`). Hub aktif = card dengan `status: hub`
  (badge lingkaran, 3 radar-pulse ring `.hub-ring`); "segera hadir" = `status:
  coming` + `hub_ref` (dipakai gambar garis jangkauan bezier ke hub terdekat).
  `DECOR_REACH` di `app.js` murni dekoratif, tidak berasal dari CMS.
- Logo: `public/images/logo-light.png`/`logo-dark.png`, dipakai lewat field CMS
  `site_settings.logo_light`/`logo_dark` (bisa diganti admin lewat upload).
- Gambar hero & galeri: masih sebagian besar link `images.unsplash.com/photo-<id>`
  (placeholder, lihat "Catatan penting") — diedit lewat CMS repeater, bukan hardcode.
- Logo mitra ekspedisi: 10 file asli (PNG/SVG) di `public/partner-logos/`, diedit
  lewat CMS section `marquee` (repeater `logo`).
- **Nav menu**: link Home/About/Services/Location/Gallery/Contact — hamburger
  mobile di bawah 820px. Urutan/visibilitas/label nav tiap section bisa diatur
  lewat `/bojong/website` (homepage editor), bukan hardcode di `nav.blade.php`.
- **Tidak ada floating contact widget**. Kontak ada di Nav (`.nav-social`, 3 ikon
  inline SVG monokrom) dan Footer (tombol "Konsultasi Disini" `.foot-wa-btn`).
- **SEO** (`resources/views/landing.blade.php`): meta title/description/keywords,
  OG/Twitter tags, JSON-LD `@graph` (`WebSite` + `LocalBusiness`) — semua field-nya
  sekarang berasal dari CMS section `seo`, diedit lewat `/bojong/settings/seo`.
  ⚠️ Kalau edit JSON-LD di Blade: `@type`/`@context`/`@graph`/`@id` harus ditulis
  dobel (`@@type`) supaya Blade tidak menganggapnya directive.
  `public/robots.txt`/`public/sitemap.xml` URL-nya hardcoded (tidak dari CMS) —
  update manual kalau domain berubah.

## Struktur project

```
resources/views/
  landing.blade.php          <- shell utama: <head>, @include tiap partial (urutan dari config/page_sections.php)
  partials/                  <- SEMUA baca data dari CMS via cms_field()/cms_items(), tidak ada hardcoded copy
    nav.blade.php, hero.blade.php, marquee.blade.php, about.blade.php,
    services.blade.php (flip card 4 kolom — dikembalikan dari tabbed "pipeline"),
    stats.blade.php, value-prop.blade.php, location.blade.php,
    gallery.blade.php (preview bento 8 tile + lightbox <dialog>),
    testimonials.blade.php, footer.blade.php
  admin/                     <- CMS/admin panel views, lihat app/Http/Controllers/Admin/AGENTS.md
  auth/                      <- login, forgot/reset-password

app/                          <- lihat docs/CODEBASE.md untuk peta lengkap

config/
  cms_schema.php              <- setiap field CMS dideklarasikan di sini (lihat AGENTS.md CMS)
  page_sections.php           <- registry section homepage (urutan default, view path)
  analytics.php

database/
  migrations/                 <- lihat docs/DATABASE.md
  seeders/                    <- CmsContentSeeder (BAHAYA re-run setelah go-live), AdminUserSeeder (aman re-run)

public/
  css/app.css, js/app.js      <- statis, tanpa build step
  images/, partner-logos/

routes/
  web.php                     <- '/' (landing) + POST /analytics/collect, wraps /bojong
  auth.php                    <- login/forgot/reset-password (2 admin, tanpa registrasi publik)
  admin.php                   <- semua route /bojong (auth middleware)
```

File HTML statis lama disimpan sebagai referensi di `legacy/anagata-landing.html`
(project ini belum pakai git, disimpan manual).

## Struktur section (urutan di halaman, per `config/page_sections.php` default_order — bisa diubah admin lewat `/bojong/website`)

1. **Nav** (`partials/nav.blade.php`) — fixed, logo + menu + tombol Contact Us
2. **Hero** (`#home`) — **full-bleed background slider** (foto mengisi seluruh layar di belakang nav+copy, BUKAN foto di kolom kanan lagi — dikembalikan ke gaya static-HTML lama atas permintaan klien): carousel 4 foto cross-fade + slow zoom (autoplay 5 detik, di-skip kalau `prefers-reduced-motion`), overlay gradient supaya teks kiri tetap terbaca, headline italic emphasis, tagline `#Let'sGrowTogether`, stat+dots dipin di kanan-bawah (mobile: kiri-bawah sejajar dot)
3. **Marquee** — logo mitra ekspedisi, infinite scroll. Tile putih ukuran tetap (176×76 desktop), logo di-fit by width DAN height (`object-fit:contain`) supaya logo kotak (ID Express, Pos, SAP) tidak mengecil jadi 20px. Jarak atas (dari dots hero) = jarak bawah (ke judul Services): `padding-top` marquee + offset `.hero-meta` = `padding-top` section berikutnya, per breakpoint (lihat komentar di `app.css`).
4. **Services** (`#services`) — **flip card** (`.service-grid`/`.flip-card`), dikembalikan dari tabbed "pipeline" atas permintaan klien (2026-09-30). Depan: judul + flow vertikal (`flow_steps`) + hint + "Lihat detail →"; belakang: judul + `.step-list` (`back_steps`). Hover = flip di desktop (CSS, `(hover:hover) and (pointer:fine)`), tap/Enter/Space = toggle `.flipped` (`app.js`). Tinggi kartu tetap (perlu untuk flip 3D); list belakang yang panjang (Outbound, 7 langkah) scroll di dalam kartu. Data tetap repeater CMS `services.card`.
5. **Stats** (tanpa `id` anchor) — angka kredibilitas besar (repeater `stats.stat`, dulu bagian dari section "Credibility" yang sudah dipecah jadi `stats` + `testimonials` terpisah).
6. **About** (`#about`) — Visi & Misi + foto tim. Catatan: sekarang urutannya SETELAH Services & Stats, bukan sebelum seperti versi awal.
7. **Value Proposition** (tanpa `id` anchor) — heading "Ini alasan seller pilih *Anagata&nbsp;Warehouse.*" + 4 stat editorial.
8. **Location** (`#location`) — peta Leaflet + panel kanan, data 100% dari CMS repeater `location.card` (lihat Stack di atas).
9. **Gallery** (`#gallery`) — **preview bento tetap 8 tile** (`.gallery-bento`/`.bento-item`) + **lightbox** (`<dialog id="galleryLightbox">`). Tinggi section TIDAK bertambah walau foto banyak: tile ke-8 jadi "+N" dan tombol "Lihat semua foto (N)" (label dari field CMS `gallery.view_all_label`) membuka viewer layar penuh (panah/keyboard/swipe, strip thumbnail). Grid pakai `Cms::thumbUrl()` (file `.thumb.webp` 720px), lightbox pakai `Cms::largeUrl()` — foto besar baru diunduh saat viewer dibuka. Kurang dari 8 foto → grid rata biasa. (Riwayat: pagination 4-foto → masonry semua foto → bento+lightbox.)
10. **Testimonials** (tanpa `id` anchor) — kutipan pelanggan dengan avatar inisial, repeater `testimonials.testimonial` (dulu bagian dari "Credibility", sekarang section terpisah, posisinya SETELAH Gallery bukan sebelum Value Proposition seperti versi awal).
11. **Contact/Footer** (`#contact`) — heading + tombol "Konsultasi Disini", logo, kontak, daftar gudang, copyright.

## Design tokens (di `public/css/app.css`, `:root`)
| Token | Nilai | Peran |
|---|---|---|
| `--purple` | `#610890` | Primary brand color |
| `--purple-deep` | `#2a0440` | Gradient/shadow gelap |
| `--purple-glow` | `#a855f7` | Aksen terang, hover, italic emphasis, marker aktif |
| `--bg` | `#0a0710` | Background utama |
| `--bg-2` | `#120a1c` | Background section alternatif (marquee, value prop, footer) |
| `--ink` | `#f4f1f7` | Warna teks utama |
| `--muted` | `#ad9fc2` | Teks sekunder/deskripsi |

Warna brand (`brand_color_primary`/`_glow`/`_deep`) juga bisa diubah lewat
`/bojong/settings/branding` — tapi itu cuma mengubah *nilai yang disimpan*, CSS
custom property di atas tetap sumber kebenaran untuk styling actual saat ini
(field brand-color CMS belum di-wire ke inject inline `<style>` dinamis — kalau
diminta, itu kerjaan baru, bukan yang sudah ada).

## Cara kustomisasi cepat

**Ubah copy, foto, alamat gudang, stat, testimoni, logo mitra, SEO, kontak, dll:**
Login ke `/bojong` dan edit lewat form CMS. **Ini sekarang cara UTAMA** — jangan
cari teksnya di file Blade. Lihat `app/Http/Controllers/Admin/AGENTS.md` untuk
cara kerja schema-driven-nya kalau perlu nambah field/section baru yang belum ada
formnya di admin panel.

**Ganti foto hero (kurangi/hapus tint ungu):**
Cari `.hero-slide{...filter:...}` di `public/css/app.css` — ini styling, bukan
konten, jadi tetap edit CSS langsung (foto-nya sendiri diganti lewat CMS).

**Ubah menu nav (urutan/label/visibility) atau tambah section baru:**
Urutan, visibility, dan label nav tiap section diatur lewat `/bojong/website`
(homepage editor) — bukan edit `nav.blade.php` langsung. Untuk section BARU yang
belum ada sama sekali (bukan cuma reorder yang sudah ada), lihat langkah di
`app/Http/Controllers/Admin/AGENTS.md`.

## Catatan penting / known limitations
- Foto hero (4 slide), foto about (1), thumbnail lokasi (2), dan foto galeri (12)
  masih placeholder Unsplash — ganti ke foto asli gudang Anagata sebelum go-live,
  lewat CMS (bukan edit Blade/JS).
- 3 testimoni (`testimonials.testimonial`) masih placeholder (nama, jabatan,
  kutipan contoh, avatar inisial bukan foto asli) — ganti sebelum go-live. Avatar
  inisial aman dipertahankan kalau sudah ada testimoni asli tapi belum ada izin
  pakai foto klien.
- 3 dari 4 angka di `stats` (500+ seller, 3+ tahun, 50rb+ paket/bulan) masih
  klaim marketing yang perlu dikonfirmasi/diganti angka asli sebelum go-live
  (angka ke-4, jumlah kota, sudah derived dari data Location asli).
- Peta Leaflet butuh koneksi internet ke `server.arcgisonline.com` — pastikan
  hosting production tidak memblokirnya.
- Logo mitra ekspedisi di `public/partner-logos/` — pastikan daftar mitra memang
  masih kerja sama aktif sebelum go-live.
- Belum ada bagian **Fulfillment Fee** dan **Meet Our Team** (sengaja dikecualikan).
- `laravel/framework` masih `^9.19` (EOL security support 2024-02-06), 12 advisory
  di `composer audit` per audit pre-production (2026-09) — lihat bagian Deploy di
  atas dan `docs/CODEBASE.md` untuk remediasi yang tidak butuh upgrade major.
- Cleanup pre-production (2026-09): dihapus — Breeze scaffold yang tidak pernah
  dipakai (`dashboard.blade.php`, tombol danger/secondary, route+view+test
  konfirmasi password yang tidak pernah di-link ke mana pun), file prototype
  `public/withtawkto.html` yang sempat public-accessible, `database/database.sqlite`
  leftover, tabel default Laravel yang tidak pernah dipakai (`sessions`, `cache`,
  `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — semua driver di `.env`
  pakai `file`/`sync`, bukan `database`). `opencode.json` yang berisi API key
  plaintext (tidak terkait Laravel/proyek ini) juga sudah dihapus — kalau muncul
  lagi, jangan commit, itu bukan bagian dari aplikasi ini.

## Riwayat keputusan desain (ringkas)
- Warna primary `#610890` mengikuti referensi warna dari klien, bukan gold (karena versi logo yang dipakai adalah versi putih)
- Gaya tipografi & layout terinspirasi dari `backslash.id` (editorial, dark, tipografi jadi hero) dan `afs.backslash.id` (headline dengan italic emphasis)
- Struktur "how it works" card + stat besar terinspirasi dari `flexe.com`
- Section "3PL Partnership" di-rename jadi marquee logo mitra (bukan section "Our Client" statis) atas permintaan klien
- Tidak pakai numbered section (01/02/03) — dihindari sesuai permintaan klien
- Tidak ada badge/ornamen generik ("AI slop") — filler kekosongan pakai foto dummy relevan atau aksen "silk" di Hero/Contact saja + satu background gradient menyambung di `body` (grid tipis sudah dicabut), bukan dekorasi berlebihan
- Dikonversi dari single-file HTML ke Laravel supaya lebih mudah dikembangkan (routing, partial per section) — nav menu ditambahkan dengan label bahasa Inggris atas permintaan klien, isi konten section tetap bahasa Indonesia
- Section Services diganti dari flip-card ke tabbed "pipeline" saat CMS ditambahkan, lalu dikembalikan ke flip-card (2026-09-30, permintaan klien); Gallery diganti dari pagination ke masonry grid, lalu ke preview bento 8 tile + lightbox (supaya halaman gak makin panjang/berat saat foto bertambah); "Credibility" dipecah jadi dua section terpisah (`stats` + `testimonials`) dengan urutan halaman berubah (Services/Stats sekarang sebelum About, Testimonials pindah ke setelah Gallery) — semua ini perubahan struktural nyata, bukan cuma copy, jadi dicatat eksplisit di sini supaya dokumentasi ini tidak drift dari kode lagi seperti yang terjadi sebelum audit 2026-09.
