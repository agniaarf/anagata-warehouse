# Menjalankan beberapa server aplikasi

Konfigurasi lokal bawaan memakai session dan cache berbasis file serta upload di
`storage/app/public`. Ini **belum siap multi-server**: setiap server akan memiliki
salinan berbeda, sehingga login, pembatasan percobaan login, perubahan CMS dan
gambar dapat tidak konsisten. Jangan aktifkan beberapa replica sebelum layanan
bersama berikut tersedia dan telah diuji di staging.

1. Gunakan database yang sama, `APP_KEY` yang sama dan `APP_URL` HTTPS yang benar
   untuk semua replica. Jangan membuat ulang `APP_KEY` production.
2. Siapkan Redis bersama, dengan autentikasi, akses jaringan privat, backup dan
   monitoring yang sesuai. Konfigurasikan `SESSION_DRIVER=redis`,
   `CACHE_STORE=redis` dan kredensial Redis yang sama pada setiap server. Cache
   bersama juga dibutuhkan untuk invalidasi konten CMS dan rate limiting.
   Verifikasi ekstensi PHP Redis serta perilaku koneksi pada lingkungan hosting.
3. Mount direktori upload bersama pada `storage/app/public` di setiap replica,
   termasuk thumbnail; atur permission dan backup. Konfigurasi disk S3 bawaan
   saja tidak otomatis memindahkan upload CMS yang memakai disk `public`.
   Penyimpanan bersama harus mendukung operasi yang digunakan aplikasi dan diuji
   untuk upload, penghapusan dan akses file. `public/storage` harus mengarah ke
   lokasi tersebut di setiap server.
4. Set `TRUSTED_PROXIES` hanya ke IP/CIDR proxy yang benar dan pastikan proxy
   menghapus header forwarding dari klien lalu memasang header yang benar.
   Pastikan HTTPS dideteksi dengan benar, cookie Secure bekerja dan pembatasan
   login menggunakan IP pengunjung yang sebenarnya. Jangan mempercayai semua
   proxy tanpa pembatasan jaringan yang sesuai.
5. Jalankan scheduler pada satu node saja, atau gunakan mekanisme distributed
   lock yang teruji. Deploy aset build yang identik ke semua node, atur health
   check dan koneksi database, lalu uji login, perubahan CMS, upload dan logout
   ketika permintaan berpindah antar-node. Jangan cache HTML admin atau response
   yang mengandung cookie/token di CDN.

Sticky sessions saja tidak menyelesaikan konsistensi cache, rate limit atau file.
Pengaturan ini adalah panduan infrastruktur, bukan bukti load balancer sudah aktif.
