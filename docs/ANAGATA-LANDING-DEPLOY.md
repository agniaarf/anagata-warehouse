# Desain baru: CMS, preview, dan rilis

Desain baru ada di `anagata-site/` (React/Vite), dengan Laravel sebagai backend CMS.
Di lokal, `/` menampilkan desain baru. Di production, `/` tetap memakai desain lama
secara default. Desain baru juga tersedia di `/preview` untuk admin yang sudah login;
halaman ini `noindex`, tidak disimpan di cache, dan tidak menghitung analytics.

## Mengelola konten

Masuk ke `/bojong` → **Konten**. Daftar utama yang tersedia:

- **Cerita pembuka**: lima judul, cerita, gambar, dan posisi teks.
- **Lima layanan**: nama, ringkasan, cerita, manfaat, tahapan, dan gambar.
- **People, Process, Purpose**: teks dan gambar kartu aktivitas.
- **Kolase foto & video**: foto dan keterangannya. Jumlah dan posisi dikunci agar
  komposisi tetap konsisten; setiap foto dapat diganti. Isi link atau ID YouTube
  pada slot mana pun untuk menjadikannya video; foto tetap dipakai sebagai sampul.
  Kosongkan ID agar slot kembali menjadi foto. Video dimuat setelah ditekan.
- **Teks dan video**: opening, Sekapur Sirih, Arti Anagata, Sejarah,
  video dan kontak. `|` di judul menandai awal bagian berwarna violet. Untuk video,
  isi link atau ID YouTube dan perbarui sumber/kreditnya.

Lokasi dan mitra pengiriman tetap memakai daftar CMS yang sudah ada. Identitas,
nomor WhatsApp, email, Instagram, dan SEO memakai menu **Pengaturan**. Daftar
urutan dan visibilitas lama di **Halaman utama** masih untuk desain lama; pengaturan
itu belum mengubah urutan desain baru.

## Menyiapkan perubahan di server tanpa mengganti homepage publik

Untuk lebih dari satu server aplikasi, ikuti `docs/LOAD-BALANCING.md` sebelum
menambah replica. Periksa juga sitemap, domain canonical, pengaturan proxy dan
indeksasi pada domain production yang sebenarnya.

1. Pastikan akses hosting dan lokasi project Laravel sudah terverifikasi. Backup
   database, kode, `.env` dan file upload `storage/app/public`. Jangan menyalin
   database lokal di atas database production.
2. Di mesin build, jalankan `npm ci` dan `npm run build` dari `anagata-site/`.
   Hasilnya ada di `public/anagata-assets/`; sertakan juga `public/anagata-images/`,
   `public/js/anagata-analytics.js`, `database/data/anagata-defaults.json`, perubahan
   Laravel, dan file project lain yang telah diperiksa. Jangan hapus upload server.
3. Pasang paket yang sudah ditinjau di staging atau server. Pertahankan `.env`
   production dan `APP_KEY`; arahkan document root ke `public/`. Pastikan PHP
   memiliki GD dengan WebP untuk upload gambar.
4. Periksa `php artisan migrate:status` sebelum migrasi. Migrasi lama
   `2026_10_30_000000_add_landing_v2_content` mengubah layout dan memanggil seeder;
   jangan jalankan otomatis tanpa meninjau dampaknya ke data production. Untuk
   memasukkan hanya data CMS desain baru yang belum ada, jalankan:

   ```sh
   php artisan migrate --path=database/migrations/2026_11_02_000000_add_figma_site_content.php --force
   php artisan migrate --path=database/migrations/2026_11_02_000001_add_figma_opening_content.php --force
   php artisan migrate --path=database/migrations/2026_11_02_000002_rename_anagata_content.php --force
   ```

   Migrasi ini menambah key baru saja dan tidak menimpa key yang sudah ada.
   **Jangan jalankan `db:seed` pada database yang sudah berisi konten admin.**
5. Tetapkan `ANAGATA_LANDING_ENABLED=false` di environment production dan refresh
   cache konfigurasi sesuai prosedur hosting. Login `/bojong`, buka `/preview`,
   dan cek CMS, lokasi, logo partner, seluruh layanan, peta, video, kontak, dan
   layout desktop/mobile. Data CMS lokal tidak otomatis ikut ke production; isi
   dan status lokasi perlu dicocokkan dengan data yang benar di server.
6. Ganti foto dan video referensi bila sudah tersedia. Jalankan audit keamanan
   dependency yang tercatat di `docs/CODEBASE.md` sebelum peluncuran.

## Setelah desain disetujui untuk live

Ubah `ANAGATA_LANDING_ENABLED=true` pada server, refresh cache konfigurasi, dan cek
halaman utama, `/bojong`, asset, analytics dan log. Untuk rollback tampilan,
kembalikan nilainya ke `false` dan refresh cache konfigurasi. Jangan rollback data
CMS dengan menghapus record yang mungkin sudah diedit admin.
