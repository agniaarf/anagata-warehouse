<?php

// Registry section homepage. Content/order/visibility yang dapat diubah admin
// tetap disimpan di cms_contents pada collection page_layout.

return [
    'intro' => [
        'label' => 'Opening Anagata',
        'description' => 'Atur wordmark, slogan, poster, dan video pembuka perjalanan.',
        'view' => 'partials.intro', 'anchor' => '#intro', 'locked' => true,
        'default_order' => 1, 'default_visible' => true, 'default_show_in_nav' => false,
        'default_nav_label' => 'Opening', 'schema_section' => 'intro',
    ],
    'meaning' => [
        'label' => 'Arti Anagata',
        'description' => 'Cerita tentang arti nama dan filosofi pertumbuhan Anagata.',
        'view' => 'partials.meaning', 'anchor' => '#meaning', 'locked' => false,
        'default_order' => 2, 'default_visible' => true, 'default_show_in_nav' => false,
        'default_nav_label' => 'Arti Anagata', 'schema_section' => 'meaning',
    ],
    'history' => [
        'label' => 'Sejarah Anagata',
        'description' => 'Kelola cerita perjalanan dan milestone Anagata.',
        'view' => 'partials.history', 'anchor' => '#history', 'locked' => false,
        'default_order' => 3, 'default_visible' => true, 'default_show_in_nav' => false,
        'default_nav_label' => 'Sejarah', 'schema_section' => 'history',
    ],
    'team_note' => [
        'label' => 'Sekapur Sirih',
        'description' => 'Pesan hangat dari Tim Anagata Warehouse.',
        'view' => 'partials.team-note', 'anchor' => '#team-note', 'locked' => false,
        'default_order' => 4, 'default_visible' => true, 'default_show_in_nav' => false,
        'default_nav_label' => 'Sekapur Sirih', 'schema_section' => 'team_note',
    ],
    'collage_finale' => [
        'label' => 'Collage Finale',
        'description' => 'Susun foto manusia dan aktivitas untuk penutup perjalanan cerita.',
        'view' => 'partials.collage-finale', 'anchor' => '#collage-finale', 'locked' => false,
        'default_order' => 5, 'default_visible' => true, 'default_show_in_nav' => false,
        'default_nav_label' => 'Cerita', 'schema_section' => 'collage_finale',
    ],
    'inbound' => [
        'label' => 'Inbound', 'description' => 'Cerita dan proses penerimaan barang.',
        'view' => 'partials.inbound', 'anchor' => '#inbound', 'locked' => false,
        'default_order' => 6, 'default_visible' => true, 'default_show_in_nav' => true,
        'default_nav_label' => 'Inbound', 'schema_section' => 'inbound',
    ],
    'outbound' => [
        'label' => 'Outbound', 'description' => 'Cerita dan proses pesanan sampai handover ekspedisi.',
        'view' => 'partials.outbound', 'anchor' => '#outbound', 'locked' => false,
        'default_order' => 7, 'default_visible' => true, 'default_show_in_nav' => true,
        'default_nav_label' => 'Outbound', 'schema_section' => 'outbound',
    ],
    'return' => [
        'label' => 'Return', 'description' => 'Cerita dan proses penanganan barang kembali.',
        'view' => 'partials.return', 'anchor' => '#return', 'locked' => false,
        'default_order' => 8, 'default_visible' => true, 'default_show_in_nav' => true,
        'default_nav_label' => 'Return', 'schema_section' => 'return',
    ],
    'location' => [
        'label' => 'Lokasi Gudang', 'description' => 'Kelola alamat, status, dan titik gudang di peta.',
        'view' => 'partials.location', 'anchor' => '#location', 'locked' => false,
        'default_order' => 9, 'default_visible' => true, 'default_show_in_nav' => true,
        'default_nav_label' => 'Lokasi', 'schema_section' => 'location',
    ],
    'footer' => [
        'label' => 'Kontak', 'description' => 'Ubah ajakan kerja sama dan informasi bagian bawah situs.',
        'view' => 'partials.footer', 'anchor' => '#contact', 'locked' => true,
        'default_order' => 10, 'default_visible' => true, 'default_show_in_nav' => true,
        'default_nav_label' => 'Kontak', 'schema_section' => 'footer',
    ],

    // Legacy V1 sections stay registered so existing CMS rows remain readable,
    // but the V2 migration and fresh-install seeder hide them by default.
    'hero' => [
        'label' => 'Banner utama (V1)', 'description' => 'Section lama, disimpan sebagai arsip.',
        'view' => 'partials.hero', 'anchor' => '#home', 'locked' => false,
        'default_order' => 101, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Beranda', 'schema_section' => 'hero',
    ],
    'marquee' => [
        'label' => 'Mitra Ekspedisi (V1)', 'description' => 'Section lama, disimpan sebagai arsip.',
        'view' => 'partials.marquee', 'anchor' => null, 'locked' => false,
        'default_order' => 102, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Mitra', 'schema_section' => 'marquee',
    ],
    'services' => [
        'label' => 'Layanan (V1)', 'description' => 'Section lama, digantikan tiga chapter layanan.',
        'view' => 'partials.services', 'anchor' => '#services', 'locked' => false,
        'default_order' => 103, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Layanan', 'schema_section' => 'services',
    ],
    'stats' => [
        'label' => 'Angka utama (V1)', 'description' => 'Section lama, disimpan sebagai arsip.',
        'view' => 'partials.stats', 'anchor' => null, 'locked' => false,
        'default_order' => 104, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Statistik', 'schema_section' => 'stats',
    ],
    'about' => [
        'label' => 'Tentang Anagata (V1)', 'description' => 'Section lama, digantikan chapter cerita.',
        'view' => 'partials.about', 'anchor' => '#about', 'locked' => false,
        'default_order' => 105, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Tentang', 'schema_section' => 'about',
    ],
    'value_prop' => [
        'label' => 'Alasan memilih Anagata (V1)', 'description' => 'Section lama, disimpan sebagai arsip.',
        'view' => 'partials.value-prop', 'anchor' => null, 'locked' => false,
        'default_order' => 106, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Kenapa Anagata', 'schema_section' => 'value_prop',
    ],
    'gallery' => [
        'label' => 'Galeri (V1)', 'description' => 'Section lama, digantikan collage finale.',
        'view' => 'partials.gallery', 'anchor' => '#gallery', 'locked' => false,
        'default_order' => 107, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Galeri', 'schema_section' => 'gallery',
    ],
    'testimonials' => [
        'label' => 'Testimoni (V1)', 'description' => 'Section lama, disimpan sebagai arsip.',
        'view' => 'partials.testimonials', 'anchor' => null, 'locked' => false,
        'default_order' => 108, 'default_visible' => false, 'default_show_in_nav' => false,
        'default_nav_label' => 'Testimoni', 'schema_section' => 'testimonials',
    ],
];
