@php
  $wa = cms_field($cms['site_settings'], 'whatsapp_number');
  $legacyCards = cms_items($cms['services'])->filter(fn ($item) => str_starts_with($item['key'], 'card_'))->values();
  $legacyByTitle = $legacyCards->mapWithKeys(fn ($item) => [strtolower($item['value']['title'] ?? '') => $item['value']]);
  $serviceDefinitions = [
    'inbound' => ['label' => 'Inbound', 'section' => 'inbound', 'intro' => 'Awal yang tertata untuk setiap produk.', 'narrative' => 'Setiap produk yang tiba membawa rencana dan kepercayaan dari pemiliknya. Kami memulai dengan memantau permintaan inbound, menerima barang, lalu memeriksa isi dan kondisinya sebelum data diperbarui dan produk ditempatkan di lokasi penyimpanan.', 'outcome' => 'Produk masuk tercatat dan memiliki tempat yang jelas, sehingga proses berikutnya dapat dimulai dengan informasi yang lebih rapi.'],
    'inventory' => ['label' => 'Inventory', 'section' => null, 'image_section' => 'history', 'intro' => 'Penataan dan kontrol stok harian.', 'narrative' => 'Inventory yang rapi bukan hanya tentang meletakkan barang di rak. Setiap seller dan SKU membutuhkan lokasi yang jelas agar produk mudah ditemukan, dihitung, dan diproses ketika order masuk. Penataan dan pergerakan stok diperiksa secara berkala untuk menjaga konsistensi operasional.', 'outcome' => 'Produk tersimpan secara terstruktur, proses picking lebih efisien, dan seller memiliki informasi stok yang lebih dapat diandalkan.', 'steps' => [['Location Mapping', 'Area penyimpanan diatur berdasarkan kebutuhan seller dan SKU.'], ['Seller & SKU Slotting', 'Setiap produk mendapat lokasi yang jelas untuk membantu mengurangi risiko tertukar.'], ['Storage', 'Produk disimpan sesuai kapasitas dan kebutuhan inventory.'], ['Daily Control', 'Lokasi dan pergerakan stok diperiksa secara berkala.']]],
    'pick-pack' => ['label' => 'Pick & Pack', 'section' => null, 'image_section' => 'outbound', 'intro' => 'Dari rak, dipersiapkan dengan perhatian.', 'narrative' => 'Saat pesanan masuk, tim mengambil produk sesuai order dan memeriksa SKU, varian, serta jumlahnya. Produk kemudian dikemas mengikuti kebutuhan dan prosedur yang berlaku, sebelum disortir untuk perjalanan berikutnya.', 'outcome' => 'Setiap order dipersiapkan melalui langkah yang jelas sebelum diserahkan ke tahap pengiriman.', 'steps' => [['Order Processing', 'Pesanan diterima dan disiapkan untuk diproses.'], ['Picking', 'Produk diambil sesuai detail pesanan.'], ['QC Verification', 'SKU, varian, dan jumlah diperiksa sebelum pengemasan.'], ['Packing', 'Produk dikemas sesuai kebutuhan dan prosedur.'], ['Sorting', 'Paket dikelompokkan untuk proses pengiriman.']]],
    'outbound' => ['label' => 'Outbound', 'section' => 'outbound', 'intro' => 'Pesanan bergerak menuju pelanggan.', 'narrative' => 'Setelah pesanan diproses, produk diambil, diperiksa, dikemas, dan disortir berdasarkan layanan pengiriman. Paket diserahkan kepada mitra ekspedisi, lalu data outbound diperbarui agar perjalanan setiap order tetap tercatat.', 'outcome' => 'Pesanan siap diserahkan ke ekspedisi melalui alur yang teratur dan dapat dipantau.'],
    'return' => ['label' => 'Return', 'section' => 'return', 'intro' => 'Barang kembali tetap mendapat perhatian.', 'narrative' => 'Paket yang kembali diterima dari ekspedisi, dibuka, dan diperiksa kondisi serta isinya. Hasil pemeriksaan dicatat, lalu produk yang sesuai dapat ditempatkan kembali ke inventory untuk langkah berikutnya.', 'outcome' => 'Kondisi barang kembali terdokumentasi sehingga seller dapat menentukan tindak lanjut dengan lebih jelas.'],
  ];
@endphp
<section id="services" class="services-experience" data-service-experience aria-label="Layanan Anagata">
  <div class="wrap">
    <div class="service-experience-heading"><span class="story-kicker">EXPLORE OUR SERVICES</span><h2>FULFILLMENT,<br><em>MADE CONNECTED.</em></h2><p>Dari produk tiba hingga pesanan bergerak menuju pelanggan, setiap langkah terhubung dalam satu alur.</p></div>
    <div class="service-switcher" role="tablist" aria-label="Pilih layanan">
      @foreach($serviceDefinitions as $slug => $service)
        <button type="button" role="tab" id="tab-{{ $slug }}" aria-controls="{{ $slug }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-service-tab="{{ $slug }}">{{ $service['label'] }}</button>
      @endforeach
    </div>
    @foreach($serviceDefinitions as $slug => $service)
      @php
        $section = $service['section'] ? $cms[$service['section']] : null;
        $imageSection = $cms[$service['image_section'] ?? $service['section']];
        $legacy = $legacyByTitle->get(strtolower($service['label']), []);
        $steps = $service['steps'] ?? collect($legacy['back_steps'] ?? [])->map(fn ($step) => [$step['title'] ?? '', $step['description'] ?? ''])->all();
        if (!count($steps) && $section) $steps = collect(json_decode(cms_field($section, 'steps', '[]'), true) ?: [])->map(fn ($step) => [$step, ''])->all();
      @endphp
      <article id="{{ $slug }}" class="service-detail" role="tabpanel" aria-labelledby="tab-{{ $slug }}" data-service-panel="{{ $slug }}" data-analytics-section="{{ $service['section'] ?? 'inbound' }}">
        <div class="service-opening">
          <div class="service-opening-copy"><span class="story-kicker">OUR SERVICES</span><h3>{{ $service['label'] }}</h3><h4>{{ $service['intro'] }}</h4><div class="service-opening-body">@if($section){!! cms_rich_text(cms_field($section, 'body')) !!}@else{{ $service['narrative'] }}@endif</div><div class="service-tags"><span>WAREHOUSE OPERATIONS</span><span>CLEAR SOP</span><span>SELLER FOCUSED</span></div></div>
          <figure class="service-detail-image"><img src="{{ \App\Support\Cms::largeUrl(cms_field($imageSection, 'image')) }}" alt="{{ cms_field($imageSection, 'image_alt') }}" loading="lazy" decoding="async"></figure>
        </div>
        <div class="service-longform"><div><span class="story-kicker">BAGAIMANA PROSESNYA BEKERJA</span><h4>{{ $service['intro'] }}</h4><p>{{ $service['narrative'] }}</p><p>{{ $service['outcome'] }}</p></div><ol class="service-timeline">@foreach($steps as $step)<li><span aria-hidden="true"></span><div><h5>{{ $step[0] }}</h5>@if(!empty($step[1]))<p>{{ $step[1] }}</p>@endif</div></li>@endforeach</ol></div>
        <div class="service-closing"><span class="story-kicker">SIAP MERAPIKAN OPERASIONALMU?</span><h4>BIARKAN KAMI MENGURUS PROSESNYA.<br><em>KAMU FOKUS MENUMBUHKAN BRAND.</em></h4><a class="service-cta" href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo Anagata, saya ingin berdiskusi tentang layanan fulfillment.') }}" target="_blank" rel="noopener" data-analytics-contact="whatsapp">DISKUSIKAN KEBUTUHANMU ↗</a></div>
      </article>
    @endforeach
  </div>
</section>
