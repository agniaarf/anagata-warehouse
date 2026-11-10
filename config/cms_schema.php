<?php

// Labels and field types used by the content editor.

return [
    'anagata_site' => [
        'label' => 'Website',
        'fields' => [],
        'repeaters' => [
            'story' => ['label' => 'Cerita pembuka', 'locked' => true, 'fields' => [
                'name' => ['type' => 'text', 'label' => 'Penanda item'],
                'label' => ['type' => 'text', 'label' => 'Judul kecil'],
                'text' => ['type' => 'textarea', 'label' => 'Cerita'],
                'image' => ['type' => 'image', 'label' => 'Foto'],
                'position' => ['type' => 'select', 'label' => 'Posisi teks', 'options' => ['left' => 'Kiri', 'right' => 'Kanan', 'center' => 'Tengah']],
            ]],
            'service' => ['label' => 'Lima layanan', 'locked' => true, 'fields' => [
                'name' => ['type' => 'text', 'label' => 'Nama layanan'],
                'subtitle' => ['type' => 'text', 'label' => 'Ringkasan'],
                'body' => ['type' => 'textarea', 'label' => 'Penjelasan'],
                'narrative' => ['type' => 'textarea', 'label' => 'Cerita proses'],
                'outcome' => ['type' => 'textarea', 'label' => 'Manfaat untuk seller'],
                'steps' => ['type' => 'delimited_list', 'label' => 'Tahapan', 'hint' => 'Satu baris per tahap: Judul | Penjelasan'],
                'image' => ['type' => 'image', 'label' => 'Foto layanan'],
            ]],
            'activity' => ['label' => 'People, Process, Purpose', 'locked' => true, 'fields' => [
                'name' => ['type' => 'text', 'label' => 'Judul'],
                'tagline' => ['type' => 'text', 'label' => 'Kalimat utama'],
                'body' => ['type' => 'textarea', 'label' => 'Cerita'],
                'image' => ['type' => 'image', 'label' => 'Foto'],
            ]],
            'photo' => ['label' => 'Kolase foto & video', 'locked' => true, 'fields' => [
                'name' => ['type' => 'text', 'label' => 'Keterangan foto'],
                'image' => ['type' => 'image', 'label' => 'Foto atau sampul video'],
                'youtube_id' => ['type' => 'text', 'label' => 'Link atau ID YouTube (opsional)', 'hint' => 'Tempel link YouTube atau ID video jika slot ini ingin menjadi video. Kosongkan untuk foto.'],
            ]],
            'section' => ['label' => 'Teks dan video', 'locked' => true, 'fields' => [
                'name' => ['type' => 'text', 'label' => 'Nama bagian'],
                'headline' => ['type' => 'text', 'label' => 'Judul', 'hint' => 'Tambahkan | sebelum bagian judul yang ingin diberi warna violet.'],
                'body_1' => ['type' => 'textarea', 'label' => 'Paragraf pertama'],
                'body_2' => ['type' => 'textarea', 'label' => 'Paragraf kedua'],
                'body_3' => ['type' => 'textarea', 'label' => 'Paragraf ketiga'],
                'image' => ['type' => 'image', 'label' => 'Foto'],
                'youtube_id' => ['type' => 'text', 'label' => 'Link atau ID video YouTube', 'hint' => 'Tempel link YouTube atau ID video, misalnya TprgJuLRPFY.'],
                'credit' => ['type' => 'text', 'label' => 'Keterangan sumber video'],
            ]],
        ],
    ],
    'intro' => [
        'label' => 'Opening Anagata',
        'fields' => [
            'brand' => ['type' => 'text', 'label' => 'Wordmark utama', 'required' => true],
            'subbrand' => ['type' => 'text', 'label' => 'Wordmark kedua', 'required' => true],
            'hashtag' => ['type' => 'text', 'label' => 'Hashtag'],
            'cta_label' => ['type' => 'text', 'label' => 'Teks tombol', 'required' => true],
            'poster_image' => ['type' => 'image', 'label' => 'Poster opening', 'required' => true],
            'video_webm' => ['type' => 'video', 'label' => 'Video WebM', 'hint' => 'Disarankan untuk browser modern. Maks. 20 MB.', 'required' => false],
            'video_mp4' => ['type' => 'video', 'label' => 'Video MP4 fallback', 'hint' => 'Fallback untuk browser yang tidak mendukung WebM. Maks. 20 MB.', 'required' => false],
            'overlay_opacity' => ['type' => 'number', 'label' => 'Overlay gelap (0-100)', 'step' => '1'],
            'playback_rate' => ['type' => 'number', 'label' => 'Kecepatan video', 'step' => '0.1', 'hint' => 'Gunakan 0.6 untuk gerak sinematik pelan.'],
            'show_once' => ['type' => 'checkbox', 'label' => 'Tampilkan sekali per browser session'],
        ],
        'repeaters' => [],
    ],

    'meaning' => [
        'label' => 'Arti Anagata',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Cerita'],
            'image' => ['type' => 'image', 'label' => 'Foto manusia/aktivitas'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto untuk pembaca layar'],
            'image_position' => ['type' => 'select', 'label' => 'Fokus foto', 'options' => ['center' => 'Tengah', 'left' => 'Kiri', 'right' => 'Kanan']],
            'overlay_opacity' => ['type' => 'number', 'label' => 'Overlay violet (0-100)', 'step' => '1'],
        ],
        'repeaters' => [],
    ],

    'history' => [
        'label' => 'Sejarah Anagata',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Pengantar'],
            'image' => ['type' => 'image', 'label' => 'Foto utama'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto untuk pembaca layar'],
            'image_position' => ['type' => 'select', 'label' => 'Fokus foto', 'options' => ['center' => 'Tengah', 'left' => 'Kiri', 'right' => 'Kanan']],
            'overlay_opacity' => ['type' => 'number', 'label' => 'Overlay violet (0-100)', 'step' => '1'],
        ],
        'repeaters' => [
            'milestone' => [
                'label' => 'Milestone sejarah',
                'add_label' => 'Tambah milestone',
                'fields' => [
                    'year' => ['type' => 'text', 'label' => 'Tahun/periode'],
                    'title' => ['type' => 'text', 'label' => 'Judul'],
                    'description' => ['type' => 'textarea', 'label' => 'Cerita singkat'],
                    'image' => ['type' => 'image', 'label' => 'Foto'],
                    'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
                ],
            ],
        ],
    ],

    'team_note' => [
        'label' => 'Sekapur Sirih',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Pesan tim'],
            'signature' => ['type' => 'text', 'label' => 'Penanda tangan'],
            'image' => ['type' => 'image', 'label' => 'Foto tim'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto untuk pembaca layar'],
            'image_position' => ['type' => 'select', 'label' => 'Fokus foto', 'options' => ['center' => 'Tengah', 'left' => 'Kiri', 'right' => 'Kanan']],
            'overlay_opacity' => ['type' => 'number', 'label' => 'Overlay violet (0-100)', 'step' => '1'],
        ],
        'repeaters' => [
            'photo' => [
                'label' => 'Potongan collage chapter', 'add_label' => 'Tambah foto',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Foto'],
                    'alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
                ],
            ],
        ],
    ],

    'collage_finale' => [
        'label' => 'Collage Finale',
        'fields' => [
            'headline' => ['type' => 'textarea', 'label' => 'Kalimat utama'],
            'hashtag' => ['type' => 'text', 'label' => 'Hashtag'],
        ],
        'repeaters' => [
            'photo' => [
                'label' => 'Foto collage finale', 'add_label' => 'Tambah foto',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Foto'],
                    'alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
                ],
            ],
        ],
    ],

    'inbound' => [
        'label' => 'Inbound',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Cerita layanan'],
            'image' => ['type' => 'image', 'label' => 'Foto aktivitas'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
            'steps' => ['type' => 'list', 'label' => 'Tahapan singkat', 'hint' => 'Satu tahap per baris.'],
            'cta_label' => ['type' => 'text', 'label' => 'Teks CTA', 'required' => false],
        ], 'repeaters' => [],
    ],
    'outbound' => [
        'label' => 'Outbound',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Cerita layanan'],
            'image' => ['type' => 'image', 'label' => 'Foto aktivitas'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
            'steps' => ['type' => 'list', 'label' => 'Tahapan singkat', 'hint' => 'Satu tahap per baris.'],
            'cta_label' => ['type' => 'text', 'label' => 'Teks CTA', 'required' => false],
        ], 'repeaters' => [],
    ],
    'return' => [
        'label' => 'Return',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'body' => ['type' => 'textarea', 'label' => 'Cerita layanan'],
            'image' => ['type' => 'image', 'label' => 'Foto aktivitas'],
            'image_alt' => ['type' => 'text', 'label' => 'Deskripsi foto'],
            'steps' => ['type' => 'list', 'label' => 'Tahapan singkat', 'hint' => 'Satu tahap per baris.'],
            'cta_label' => ['type' => 'text', 'label' => 'Teks CTA', 'required' => false],
        ], 'repeaters' => [],
    ],

    'hero' => [
        'label' => 'Banner utama',
        'fields' => [
            'tag' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul utama', 'hint' => 'Gunakan <br> untuk pindah baris.'],
            'brand' => ['type' => 'text', 'label' => 'Nama brand'],
            'paragraph' => ['type' => 'textarea', 'label' => 'Deskripsi'],
            'cta_primary_label' => ['type' => 'text', 'label' => 'Teks tombol utama'],
            'cta_primary_href' => ['type' => 'text', 'label' => 'Tujuan tombol utama', 'hint' => 'Masukkan URL atau ID bagian, misalnya #contact.'],
            'cta_secondary_label' => ['type' => 'text', 'label' => 'Teks tombol kedua'],
            'cta_secondary_href' => ['type' => 'text', 'label' => 'Tujuan tombol kedua', 'hint' => 'Masukkan URL atau ID bagian, misalnya #services.'],
            'hashtag' => ['type' => 'text', 'label' => 'Hashtag'],
        ],
        'repeaters' => [
            'slide' => [
                'label' => 'Foto banner',
                'add_label' => 'Tambah foto',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Foto'],
                ],
            ],
        ],
    ],

    'about' => [
        'label' => 'Tentang Kami',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'paragraph_1' => ['type' => 'textarea', 'label' => 'Paragraf 1'],
            'paragraph_2' => ['type' => 'textarea', 'label' => 'Paragraf 2'],
            'photo_image' => ['type' => 'image', 'label' => 'Foto'],
            'photo_alt' => ['type' => 'text', 'label' => 'Deskripsi foto untuk pembaca layar'],
            'photo_caption' => ['type' => 'text', 'label' => 'Keterangan foto'],
            'visi_title' => ['type' => 'text', 'label' => 'Judul Visi'],
            'visi_text' => ['type' => 'textarea', 'label' => 'Isi Visi'],
            'misi_title' => ['type' => 'text', 'label' => 'Judul Misi'],
            'misi_bullets' => ['type' => 'list', 'label' => 'Poin misi', 'hint' => 'Tulis satu poin pada setiap baris.'],
        ],
        'repeaters' => [],
    ],

    'services' => [
        'label' => 'Layanan',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'text', 'label' => 'Judul'],
            'paragraph' => ['type' => 'textarea', 'label' => 'Deskripsi'],
        ],
        'repeaters' => [
            'card' => [
                'label' => 'Layanan',
                'add_label' => 'Tambah layanan',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Judul'],
                    'hint' => ['type' => 'text', 'label' => 'Ringkasan layanan'],
                    'flow_steps' => ['type' => 'list', 'label' => 'Langkah singkat', 'hint' => 'Tulis satu langkah pada setiap baris.'],
                    'back_steps' => ['type' => 'delimited_list', 'label' => 'Rincian langkah', 'hint' => 'Tulis satu langkah per baris dengan format: Judul | Penjelasan.'],
                ],
            ],
        ],
    ],

    'stats' => [
        'label' => 'Angka utama',
        'fields' => [],
        'repeaters' => [
            'stat' => [
                'label' => 'Angka dan keterangan',
                'add_label' => 'Tambah angka',
                'fields' => [
                    'number' => ['type' => 'text', 'label' => 'Angka'],
                    'label' => ['type' => 'textarea', 'label' => 'Keterangan'],
                ],
            ],
        ],
    ],

    'testimonials' => [
        'label' => 'Testimoni pelanggan',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
        ],
        'repeaters' => [
            'testimonial' => [
                'label' => 'Testimoni pelanggan',
                'add_label' => 'Tambah testimoni',
                'fields' => [
                    'quote' => ['type' => 'textarea', 'label' => 'Kutipan'],
                    'initials' => ['type' => 'text', 'label' => 'Inisial nama'],
                    'name' => ['type' => 'text', 'label' => 'Nama'],
                    'role' => ['type' => 'text', 'label' => 'Toko atau jabatan'],
                ],
            ],
        ],
    ],

    'value_prop' => [
        'label' => 'Alasan memilih Anagata',
        'fields' => [
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'subtext' => ['type' => 'textarea', 'label' => 'Deskripsi'],
        ],
        'repeaters' => [
            'stat' => [
                'label' => 'Poin keunggulan',
                'add_label' => 'Tambah poin',
                'fields' => [
                    'number' => ['type' => 'text', 'label' => 'Angka'],
                    'label' => ['type' => 'textarea', 'label' => 'Keterangan'],
                ],
            ],
        ],
    ],

    'location' => [
        'label' => 'Lokasi gudang',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'paragraph' => ['type' => 'textarea', 'label' => 'Deskripsi'],
            'stat_gudang' => ['type' => 'number', 'label' => 'Jumlah gudang aktif'],
            'stat_gudang_label' => ['type' => 'text', 'label' => 'Label statistik gudang'],
            'stat_kota' => ['type' => 'number', 'label' => 'Jumlah kota ekspansi'],
            'stat_kota_label' => ['type' => 'text', 'label' => 'Label statistik kota'],
            'stat_pulau' => ['type' => 'number', 'label' => 'Jumlah pulau'],
            'stat_pulau_label' => ['type' => 'text', 'label' => 'Label statistik pulau'],
        ],
        'repeaters' => [
            'card' => [
                'label' => 'Gudang atau kota',
                'add_label' => 'Tambah lokasi',
                'fields' => [
                    'badge' => ['type' => 'text', 'label' => 'Kode kota', 'hint' => 'Contoh: JKT.'],
                    'name' => ['type' => 'text', 'label' => 'Nama'],
                    'address' => ['type' => 'text', 'label' => 'Alamat lengkap'],
                    'area' => ['type' => 'text', 'label' => 'Kecamatan dan kota'],
                    'region' => ['type' => 'text', 'label' => 'Provinsi'],
                    'lat' => ['type' => 'number', 'label' => 'Latitude peta', 'step' => 'any', 'hint' => 'Koordinat titik pada peta.'],
                    'lng' => ['type' => 'number', 'label' => 'Longitude peta', 'step' => 'any', 'hint' => 'Koordinat titik pada peta.'],
                    'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['hub' => 'Gudang aktif', 'listed' => 'Tercantum (status belum dikonfirmasi)', 'coming' => 'Segera hadir', 'hidden' => 'Tidak ditampilkan']],
                    'hub_ref' => ['type' => 'text', 'label' => 'Kode gudang aktif terdekat', 'hint' => 'Isi untuk lokasi yang berstatus Segera hadir.'],
                    'thumbnail' => ['type' => 'image', 'label' => 'Foto lokasi', 'required' => false],
                ],
            ],
        ],
    ],

    'marquee' => [
        'label' => 'Mitra ekspedisi',
        'fields' => [],
        'repeaters' => [
            'logo' => [
                'label' => 'Mitra ekspedisi',
                'add_label' => 'Tambah mitra',
                'fields' => [
                    'path' => ['type' => 'image', 'label' => 'Logo'],
                    'alt' => ['type' => 'text', 'label' => 'Nama mitra'],
                ],
            ],
        ],
    ],

    'gallery' => [
        'label' => 'Galeri',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'title' => ['type' => 'text', 'label' => 'Judul'],
            'description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
            'view_all_label' => ['type' => 'text', 'label' => 'Teks tombol lihat semua foto', 'hint' => 'Muncul kalau foto lebih dari 8. Jumlah foto ditambahkan otomatis.'],
        ],
        'repeaters' => [
            'item' => [
                'label' => 'Foto galeri',
                'add_label' => 'Tambah foto',
                'fields' => [
                    'src' => ['type' => 'image', 'label' => 'Foto'],
                    'caption' => ['type' => 'text', 'label' => 'Keterangan foto'],
                ],
            ],
        ],
    ],

    'footer' => [
        'label' => 'Kontak',
        'fields' => [
            'eyebrow' => ['type' => 'text', 'label' => 'Teks kecil di atas judul'],
            'headline' => ['type' => 'textarea', 'label' => 'Judul'],
            'tagline' => ['type' => 'text', 'label' => 'Kalimat singkat'],
            'cta_label' => ['type' => 'text', 'label' => 'Teks tombol konsultasi'],
            'brand_tagline' => ['type' => 'textarea', 'label' => 'Deskripsi di bawah logo'],
            'copyright_holder' => ['type' => 'text', 'label' => 'Nama pemilik hak cipta', 'hint' => 'Tahun akan ditambahkan otomatis.'],
        ],
        'repeaters' => [],
    ],

    'seo' => [
        'label' => 'Pencarian Google',
        'fields' => [
            'meta_title' => ['type' => 'text', 'label' => 'Judul hasil pencarian', 'hint' => 'Judul yang muncul di tab browser dan hasil Google.'],
            'meta_description' => ['type' => 'textarea', 'label' => 'Deskripsi hasil pencarian', 'hint' => 'Ringkasan singkat yang dapat tampil di Google.'],
            'meta_keywords' => ['type' => 'textarea', 'label' => 'Kata kunci'],
            'og_title' => ['type' => 'text', 'label' => 'Judul saat dibagikan'],
            'og_description' => ['type' => 'textarea', 'label' => 'Deskripsi saat dibagikan'],
            'image_alt_suffix' => ['type' => 'text', 'label' => 'Keterangan tambahan foto', 'hint' => 'Ditambahkan ke deskripsi foto galeri.'],
        ],
        'repeaters' => [
            'offer' => [
                'label' => 'Layanan untuk Google',
                'add_label' => 'Tambah layanan',
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Nama layanan'],
                    'description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                ],
            ],
        ],
    ],

    'site_settings' => [
        'label' => 'Pengaturan situs',
        'fields' => [
            'whatsapp_number' => ['type' => 'text', 'label' => 'Nomor WhatsApp', 'hint' => 'Gunakan format internasional, misalnya 6281234567890.'],
            'email' => ['type' => 'text', 'label' => 'Email'],
            'instagram_handle' => ['type' => 'text', 'label' => 'Username Instagram', 'hint' => 'Tulis tanpa tanda @.'],
            'logo_light' => ['type' => 'image', 'label' => 'Logo terang', 'hint' => 'Dipakai pada menu dan bagian bawah situs.'],
            'logo_dark' => ['type' => 'image', 'label' => 'Logo gelap', 'hint' => 'Dipakai saat situs dibagikan.'],
            'favicon' => ['type' => 'image', 'label' => 'Ikon tab browser', 'hint' => 'Gunakan gambar persegi berukuran minimal 256 x 256 piksel.', 'required' => false],
            'brand_color_primary' => ['type' => 'color', 'label' => 'Warna utama brand'],
            'brand_color_glow' => ['type' => 'color', 'label' => 'Warna aksen terang'],
            'brand_color_deep' => ['type' => 'color', 'label' => 'Warna gradient gelap'],
        ],
        'repeaters' => [],
    ],
];
