<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSiteSettings();
        $this->seedPageLayout();
        $this->seedHero();
        $this->seedAbout();
        $this->seedServices();
        $this->seedStats();
        $this->seedValueProp();
        $this->seedLocation();
        $this->seedMarquee();
        $this->seedGallery();
        $this->seedTestimonials();
        $this->seedFooter();
        $this->seedSeo();
    }

    private function seedSiteSettings(): void
    {
        Content::putField('site_settings', 'whatsapp_number', '6281930041391');
        Content::putField('site_settings', 'email', 'anagatafulfill@gmail.com');
        Content::putField('site_settings', 'instagram_handle', 'anagatawarehouse');
        // Logo/favicon seeded as absolute URLs to the existing static files (same
        // passthrough bridge used for marquee logos) — admin can replace them with
        // an upload later without needing a day-one re-hosting pass.
        Content::putField('site_settings', 'logo_light', asset('images/logo-light.png'));
        Content::putField('site_settings', 'logo_dark', asset('images/logo-dark.png'));
        Content::putField('site_settings', 'favicon', asset('images/favicon.png'));
        Content::putField('site_settings', 'brand_color_primary', '#610890');
        Content::putField('site_settings', 'brand_color_glow', '#a855f7');
        Content::putField('site_settings', 'brand_color_deep', '#2a0440');
    }

    /**
     * The "Website > Homepage" assembly data — order/visibility/nav mapping for
     * each of the 9 fixed sections. Structural facts (label, anchor, locked)
     * live in config/page_sections.php; only the admin-mutable state lives here.
     */
    private function seedPageLayout(): void
    {
        foreach (config('page_sections') as $slug => $meta) {
            Content::putItem('page_layout', $slug, [
                'visible' => true,
                'show_in_nav' => $meta['default_show_in_nav'],
                'nav_label' => $meta['default_nav_label'],
            ], $meta['default_order']);
        }
    }

    private function seedHero(): void
    {
        Content::putField('hero', 'tag', 'Fulfillment Partner untuk Seller Indonesia');
        Content::putField('hero', 'headline', "Kelola gudang dan operasional<br>fulfillment-mu bersama kami,");
        Content::putField('hero', 'brand', 'ANAGATA WAREHOUSE');
        Content::putField('hero', 'paragraph', 'Layanan warehouse dan fulfillment <strong>end-to-end</strong> yang mengurus seluruh proses dari penerimaan produk, inventory management, pemrosesan order, packing, hingga handover ke 3PL dan pengelolaan return.');
        Content::putField('hero', 'cta_primary_label', 'Mulai Kerja Sama');
        Content::putField('hero', 'cta_primary_href', '#contact');
        Content::putField('hero', 'cta_secondary_label', 'Lihat Layanan');
        Content::putField('hero', 'cta_secondary_href', '#services');
        Content::putField('hero', 'hashtag', "#Let'sGrowTogether");

        $slides = [
            'https://images.unsplash.com/photo-1644079446600-219068676743?w=1600&h=1000&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1700165644892-3dd6b67b25bc?w=1600&h=1000&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1613690399151-65ea69478674?w=1600&h=1000&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1714627798569-b3e36d409c4b?w=1600&h=1000&q=80&auto=format&fit=crop',
        ];
        foreach ($slides as $i => $image) {
            Content::putItem('hero', 'slide_'.($i + 1), ['image' => $image], $i + 1);
        }
    }

    private function seedAbout(): void
    {
        Content::putField('about', 'eyebrow', 'Tentang Anagata');
        Content::putField('about', 'headline', 'Lahir dari komitmen menjadi<br><em>sahabat seller.</em>');
        Content::putField('about', 'paragraph_1', 'Anagata Warehouse berdiri di bawah bendera <strong>CV Anagata Satria Nusantara</strong> sejak 2024, bergerak di lini fulfillment dan home packing. Nama "Anagata" berarti harapan yang baik, untuk seller, kurir, buyer, dan setiap orang yang terlibat di dalamnya.');
        Content::putField('about', 'paragraph_2', 'Kami hadir untuk mempermudah seller mengelola inventory, data, packing, hingga urusan shipping, supaya seller bisa fokus mengembangkan bisnisnya, bukan sibuk mengurus gudang.');
        Content::putField('about', 'photo_image', 'https://images.unsplash.com/photo-1664382953403-fc1ac77073a0?w=900&q=80&auto=format&fit=crop');
        Content::putField('about', 'photo_alt', 'Tim operasional Anagata Warehouse menangani proses packing dan storage di gudang Jakarta');
        Content::putField('about', 'photo_caption', 'Tim operasional Anagata di dalam gudang.');
        Content::putField('about', 'visi_title', 'Visi');
        Content::putField('about', 'visi_text', 'Menjadi mitra fulfillment terpercaya yang menghubungkan seller Indonesia dengan operasional gudang yang rapi, cepat, dan transparan di seluruh penjuru negeri.');
        Content::putField('about', 'misi_title', 'Misi');
        Content::putField('about', 'misi_bullets', json_encode([
            'Memberikan layanan fulfillment end-to-end yang akurat dan tepat waktu',
            'Menjaga keamanan data dan produk milik setiap seller',
            'Memperluas jangkauan gudang ke seluruh wilayah Indonesia',
            'Membangun proses kerja yang transparan lewat data yang selalu update',
        ], JSON_UNESCAPED_UNICODE));
    }

    private function seedServices(): void
    {
        Content::putField('services', 'eyebrow', 'Layanan Kami');
        Content::putField('services', 'headline', 'Empat pilar operasional gudang.');
        Content::putField('services', 'paragraph', 'Dari barang masuk sampai retur, tiap tahap dikerjakan pakai SOP yang jelas supaya stok selalu akurat dan pesanan selalu tepat waktu.');

        $cards = [
            [
                'title' => 'Inbound',
                'flow_steps' => ['Request', 'Unboxing', 'QC Report', 'Racking'],
                'hint' => 'Penerimaan dan pencatatan produk masuk',
                'back_steps' => [
                    ['title' => 'Monitor Inbound Request', 'description' => 'Monitoring dan validasi request inbound dari seller'],
                    ['title' => 'Receiving', 'description' => 'Penerimaan produk dari seller/supplier'],
                    ['title' => 'Checking & Unboxing', 'description' => 'Pemeriksaan jumlah dan kondisi produk saat inbound'],
                    ['title' => 'Counting & Update Inbound Data', 'description' => 'Pencatatan dan update data inbound ke sistem'],
                    ['title' => 'Putaway', 'description' => 'Penempatan produk ke lokasi penyimpanan'],
                ],
            ],
            [
                'title' => 'Inventory',
                'flow_steps' => ['Mapping', 'Slotting', 'Storage', 'Daily Control'],
                'hint' => 'Penataan dan kontrol stok harian',
                'back_steps' => [
                    ['title' => 'Location Mapping', 'description' => 'Pengaturan lokasi penyimpanan berdasarkan seller dan SKU'],
                    ['title' => 'Storage', 'description' => 'Penyimpanan produk sesuai kapasitas dan kebutuhan inventory'],
                ],
            ],
            [
                'title' => 'Outbound',
                'flow_steps' => ['Verifikasi', 'Picking', 'Packing', 'Sorting', 'Handover 3PL'],
                'hint' => 'Proses order sampai ke ekspedisi',
                'back_steps' => [
                    ['title' => 'Order Processing', 'description' => 'Penerimaan dan pemrosesan order outbound seller'],
                    ['title' => 'QC', 'description' => 'Verifikasi SKU, varian, dan quantity sebelum packing'],
                    ['title' => 'Picking', 'description' => 'Pengambilan produk sesuai order'],
                    ['title' => 'Packing', 'description' => 'Pengemasan produk sesuai SOP fulfillment'],
                    ['title' => 'Sortir', 'description' => 'Penyortiran paket berdasarkan layanan & 3PL'],
                    ['title' => '3PL Handover', 'description' => 'Serah terima paket kepada pihak 3PL'],
                    ['title' => 'Counting & Update Outbound Data', 'description' => 'Pencatatan dan update status outbound'],
                ],
            ],
            [
                'title' => 'Return',
                'flow_steps' => ['Return', 'Unboxing', 'QC Report', 'Restock / Klaim'],
                'hint' => 'Pengelolaan produk kembali',
                'back_steps' => [
                    ['title' => 'Receiving', 'description' => 'Penerimaan paket return dari 3PL'],
                    ['title' => 'Checking & Unboxing', 'description' => 'Pemeriksaan kondisi dan isi paket return'],
                    ['title' => 'Counting & Update Return Data', 'description' => 'Pencatatan dan update data return'],
                    ['title' => 'Putaway', 'description' => 'Penempatan kembali produk return ke inventory'],
                ],
            ],
        ];
        foreach ($cards as $i => $card) {
            Content::putItem('services', 'card_'.($i + 1), $card, $i + 1);
        }
    }

    private function seedStats(): void
    {
        // 4th stat ("5 Kota") is the real count of cities referenced in Location
        // (2 active hubs + 3 coming-soon) — arithmetic on existing real data,
        // not a new claim — kept as a plain editable stat rather than an
        // auto-computed one so admin can still correct it directly later.
        $stats = [
            ['number' => '500+', 'label' => 'Seller aktif menitipkan pengelolaan stok dan pengirimannya ke Anagata.'],
            ['number' => '50rb+', 'label' => 'Paket diproses dan diserahkan ke ekspedisi setiap bulannya.'],
            ['number' => '3+ Tahun', 'label' => 'Beroperasi melayani fulfillment untuk seller online di berbagai kategori.'],
            ['number' => '5 Kota', 'label' => 'Jakarta, Makassar, Medan, Yogyakarta, dan Banjarmasin, aktif maupun segera hadir.'],
        ];
        foreach ($stats as $i => $stat) {
            Content::putItem('stats', 'stat_'.($i + 1), $stat, $i + 1);
        }
    }

    private function seedTestimonials(): void
    {
        Content::putField('testimonials', 'eyebrow', 'Kata Seller Kami');
        Content::putField('testimonials', 'headline', 'Dari yang sudah <em>merasakan sendiri.</em>');

        $testimonials = [
            ['quote' => 'Sejak pindah ke Anagata, stok jarang selisih dan resi selalu kelar hari itu juga. Tim jadi bisa fokus jualan, bukan ngurus packing.', 'initials' => 'RD', 'name' => 'Rani D.', 'role' => 'Owner, Fashion Online Shop'],
            ['quote' => 'Laporan stok real-time-nya bikin tenang, kelihatan jelas barang masuk-keluar tanpa harus telepon gudang tiap hari.', 'initials' => 'AH', 'name' => 'Andi H.', 'role' => 'Founder, Skincare Brand'],
            ['quote' => 'Proses return-nya rapi dan cepat balik ke stok. Untuk seller dengan volume tinggi, itu penting banget buat jaga cash flow.', 'initials' => 'SW', 'name' => 'Sari W.', 'role' => 'Operations Lead, Home & Living Store'],
        ];
        foreach ($testimonials as $i => $testimonial) {
            Content::putItem('testimonials', 'testimonial_'.($i + 1), $testimonial, $i + 1);
        }
    }

    private function seedValueProp(): void
    {
        Content::putField('value_prop', 'headline', 'Ini alasan seller pilih <em>Anagata&nbsp;Warehouse.</em>');
        Content::putField('value_prop', 'subtext', 'Empat komitmen yang kami pegang di setiap resi yang masuk ke gudang kami.');

        $stats = [
            ['number' => '1 Hari', 'label' => 'Resi yang masuk hari ini, dipacking & diserahkan ke ekspedisi hari itu juga.'],
            ['number' => 'Real-time', 'label' => 'Data stock, inbound, outbound, dan return selalu update dan bisa diakses kapan pun.'],
            ['number' => '100%', 'label' => 'Kerahasiaan data penjualan & produk seller jadi prioritas utama kami.'],
            ['number' => 'Terpisah', 'label' => 'Produk tiap seller tertata dan terpisah rapi dari seller lainnya.'],
        ];
        foreach ($stats as $i => $stat) {
            Content::putItem('value_prop', 'stat_'.($i + 1), $stat, $i + 1);
        }
    }

    private function seedLocation(): void
    {
        Content::putField('location', 'eyebrow', 'Lokasi Gudang');
        Content::putField('location', 'headline', 'Gudang di banyak titik, <em>kiriman lebih dekat.</em>');
        Content::putField('location', 'paragraph', 'Titip stok di gudang yang paling deket sama basis pelangganmu, atau pakai beberapa sekaligus buat pangkas waktu kirim ke barat, tengah, dan timur.');
        Content::putField('location', 'stat_gudang', '2');
        Content::putField('location', 'stat_gudang_label', 'gudang aktif');
        Content::putField('location', 'stat_kota', '3');
        Content::putField('location', 'stat_kota_label', 'kota dalam ekspansi');
        Content::putField('location', 'stat_pulau', '2');
        Content::putField('location', 'stat_pulau_label', 'pulau');

        $cards = [
            // 'area' is the short "kecamatan, kota" form the footer's Gudang column
            // shows — kept alongside the full street 'address' used by the loc-card
            // itself, both on the same item, so there's still one place to edit a
            // warehouse's location instead of a separate footer-only copy.
            ['badge' => 'JKT', 'name' => 'Warehouse Jakarta', 'address' => 'Jl. Bojong Indah Raya No.4, Duren Sawit, Jakarta Timur 13450', 'area' => 'Duren Sawit, Jakarta Timur', 'region' => 'DKI Jakarta', 'lat' => -6.225, 'lng' => 106.900, 'status' => 'hub', 'hub_ref' => null, 'thumbnail' => 'https://images.unsplash.com/photo-1601598505513-7489a6272d2a?w=200&h=200&q=70&auto=format&fit=crop'],
            ['badge' => 'MKS', 'name' => 'Warehouse Makassar', 'address' => 'Jl. Cilallang Jaya VII No.17, Rappocini, Makassar 90222', 'area' => 'Rappocini, Makassar', 'region' => 'Sulawesi Selatan', 'lat' => -5.147, 'lng' => 119.432, 'status' => 'hub', 'hub_ref' => null, 'thumbnail' => 'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=200&h=200&q=70&auto=format&fit=crop'],
            ['badge' => 'MDN', 'name' => 'Warehouse Medan', 'address' => 'Medan, Sumatera Utara. Alamat detail menyusul.', 'area' => 'Medan, Sumatera Utara', 'region' => 'Sumatera Utara', 'lat' => 3.595, 'lng' => 98.672, 'status' => 'coming', 'hub_ref' => 'JKT', 'thumbnail' => null],
            ['badge' => 'JOG', 'name' => 'Warehouse Yogyakarta', 'address' => 'Yogyakarta, DI Yogyakarta. Alamat detail menyusul.', 'area' => 'Yogyakarta, DI Yogyakarta', 'region' => 'DI Yogyakarta', 'lat' => -7.797, 'lng' => 110.370, 'status' => 'coming', 'hub_ref' => 'JKT', 'thumbnail' => null],
            ['badge' => 'BJM', 'name' => 'Warehouse Banjarmasin', 'address' => 'Banjarmasin, Kalimantan Selatan. Segera hadir.', 'area' => 'Banjarmasin, Kalimantan Selatan', 'region' => 'Kalimantan Selatan', 'lat' => -3.319, 'lng' => 114.590, 'status' => 'coming', 'hub_ref' => 'MKS', 'thumbnail' => null],
        ];
        foreach ($cards as $i => $card) {
            Content::putItem('location', 'card_'.($i + 1), $card, $i + 1);
        }
    }

    private function seedMarquee(): void
    {
        // Seeded as absolute URLs (not bare relative paths) since these are static files
        // under public/partner-logos/, not Storage-disk admin uploads — Cms::imageUrl()
        // passes absolute URLs through unchanged, same bridge used for the legacy
        // Unsplash image URLs elsewhere in this seeder.
        $logos = [
            ['path' => asset('partner-logos/jne.png'), 'alt' => 'JNE Express'],
            ['path' => asset('partner-logos/spx-express.png'), 'alt' => 'SPX Express'],
            ['path' => asset('partner-logos/sap-express.svg'), 'alt' => 'SAP Express'],
            ['path' => asset('partner-logos/anteraja.png'), 'alt' => 'AnterAja'],
            ['path' => asset('partner-logos/jt-express.svg'), 'alt' => 'J&T Express'],
            ['path' => asset('partner-logos/sicepat.svg'), 'alt' => 'SiCepat'],
            ['path' => asset('partner-logos/lion-parcel.svg'), 'alt' => 'Lion Parcel'],
            ['path' => asset('partner-logos/id-express.png'), 'alt' => 'ID Express'],
            ['path' => asset('partner-logos/pos-indonesia.svg'), 'alt' => 'Pos Indonesia'],
            ['path' => asset('partner-logos/ninja-xpress.png'), 'alt' => 'Ninja Xpress'],
        ];
        foreach ($logos as $i => $logo) {
            Content::putItem('marquee', 'logo_'.($i + 1), $logo, $i + 1);
        }
    }

    private function seedGallery(): void
    {
        Content::putField('gallery', 'eyebrow', 'Galeri');
        Content::putField('gallery', 'title', 'Aktivitas di dalam gudang.');
        Content::putField('gallery', 'description', 'Sekilas proses harian tim kami, dari rak penyimpanan sampai paket siap dikirim ke ekspedisi.');
        Content::putField('gallery', 'view_all_label', 'Lihat semua foto');

        $items = [
            ['id' => '1601598704991-eef6114775e0', 'cap' => 'Area penyimpanan inventory'],
            ['id' => '1740914994657-f1cdffdc418e', 'cap' => 'Proses picking barang sesuai order'],
            ['id' => '1700727448575-6f1680cd7d75', 'cap' => 'Quality control sebelum packing'],
            ['id' => '1766040923580-16ad32fae8b4', 'cap' => 'Proses packing sesuai SOP'],
            ['id' => '1608822101624-23542c56db89', 'cap' => 'Sortir paket per ekspedisi'],
            ['id' => '1717457779749-7a6707d042ad', 'cap' => 'Handover ke mitra ekspedisi'],
            ['id' => '1736507748808-31bb64ce6fcc', 'cap' => 'Rak penyimpanan produk seller'],
            ['id' => '1664382953403-fc1ac77073a0', 'cap' => 'Tim operasional gudang'],
            ['id' => '1573376670774-4427757f7963', 'cap' => 'Proses unboxing barang inbound'],
            ['id' => '1601598505513-7489a6272d2a', 'cap' => 'Gudang Jakarta, Duren Sawit'],
            ['id' => '1605745341112-85968b19335b', 'cap' => 'Gudang Makassar, Rappocini'],
            ['id' => '1644079446600-219068676743', 'cap' => 'Stok tersusun rapi di rak'],
        ];
        foreach ($items as $i => $item) {
            Content::putItem('gallery', 'item_'.($i + 1), [
                'src' => "https://images.unsplash.com/photo-{$item['id']}?w=600&h=600&q=80&auto=format&fit=crop",
                'caption' => $item['cap'],
            ], $i + 1);
        }
    }

    private function seedFooter(): void
    {
        Content::putField('footer', 'eyebrow', 'Hubungi Kami');
        Content::putField('footer', 'headline', "Siap kerja sama? <em>Let's talk.</em>");
        Content::putField('footer', 'tagline', "#Let'sGrowTogether");
        Content::putField('footer', 'cta_label', 'Konsultasi Disini');
        Content::putField('footer', 'brand_tagline', 'Homepacking dan fulfillment berbasis lokal, standar kualitas ekspor.');
        Content::putField('footer', 'copyright_holder', 'Anagata Warehouse. All Rights Reserved.');
        // Gudang list is deliberately NOT duplicated here — footer.blade.php renders it
        // straight from location's active hub cards (single source of truth).
    }

    private function seedSeo(): void
    {
        Content::putField('seo', 'meta_title', 'Fulfillment Warehouse Jakarta | Partner Packing, Storage & Order Fulfillment | Anagata Warehouse');
        Content::putField('seo', 'meta_description', 'Anagata Warehouse, partner fulfillment warehouse Jakarta & Makassar untuk seller online. Storage, inbound, inventory, proses order, packing, outbound, sampai handover ekspedisi dan return, dikerjakan end-to-end dengan SOP rapi.');
        Content::putField('seo', 'meta_keywords', 'fulfillment warehouse jakarta, warehouse fulfillment, partner fulfillment, jasa fulfillment jakarta, fulfillment center jakarta, gudang fulfillment jakarta, jasa packing online shop, jasa storage barang jakarta, sewa gudang seller online, inbound outbound gudang, order fulfillment indonesia, home packing jakarta, fulfillment makassar, third party logistics jakarta');
        Content::putField('seo', 'og_title', 'Fulfillment Warehouse Jakarta | Partner Packing, Storage & Order Fulfillment');
        Content::putField('seo', 'og_description', 'Partner fulfillment warehouse Jakarta & Makassar untuk seller online, dengan storage, inbound, inventory, order, packing, outbound, sampai handover ke ekspedisi.');
        // og_image is deliberately NOT its own field — landing.blade.php uses
        // site_settings.logo_dark, so there's one place to update the OG/share
        // image instead of two fields that could drift out of sync.
        Content::putField('seo', 'image_alt_suffix', 'Anagata Warehouse, fulfillment warehouse Jakarta & Makassar');

        // JSON-LD hasOfferCatalog — kept as its own 5-entry list rather than derived
        // from the 4 Services flip-cards: the catalog splits "Outbound" into two
        // distinct SEO-facing offers (Order Processing & Packing / Outbound & Handover),
        // so forcing a 1:1 mapping would lose real catalog detail, not remove duplication.
        $offers = [
            ['name' => 'Inbound & Penerimaan Barang', 'description' => 'Penerimaan, pengecekan kondisi, dan pencatatan stok masuk ke gudang.'],
            ['name' => 'Storage & Inventory Management', 'description' => 'Penyimpanan produk terpisah per seller dengan data stok real-time.'],
            ['name' => 'Order Processing & Packing', 'description' => 'Picking, quality control, dan packing pesanan sesuai SOP, same day.'],
            ['name' => 'Outbound & Handover Ekspedisi', 'description' => 'Sortir per ekspedisi dan handover ke mitra 3PL seperti JNE, J&T, SiCepat, SPX, AnterAja.'],
            ['name' => 'Return Handling', 'description' => 'Pengelolaan paket retur, pengecekan ulang, dan pengembalian ke stok.'],
        ];
        foreach ($offers as $i => $offer) {
            Content::putItem('seo', 'offer_'.($i + 1), $offer, $i + 1);
        }
    }
}
