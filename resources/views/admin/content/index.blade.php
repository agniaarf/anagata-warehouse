@php
    $order = ['anagata_site:story' => 1, 'anagata_site:section' => 2, 'anagata_site:activity' => 3, 'anagata_site:service' => 4, 'anagata_site:photo' => 5, 'location:card' => 6, 'marquee:logo' => 7];
    $active = collect($collections)->filter(fn ($c) => array_key_exists($c['section'].':'.$c['group'], $order))->sortBy(fn ($c) => $order[$c['section'].':'.$c['group']]);
    $archive = collect($collections)->reject(fn ($c) => $c['section'] === 'anagata_site' || ($c['section'] === 'location' && $c['group'] === 'card') || ($c['section'] === 'marquee' && $c['group'] === 'logo'));
    $descriptions = [
        'anagata_site:story' => 'Judul, cerita, dan foto pembuka.',
        'anagata_site:section' => 'Opening, cerita Anagata, dan video.',
        'anagata_site:activity' => 'Orang dan kegiatan di balik layar.',
        'anagata_site:service' => 'Penjelasan dan tahapan tiap layanan.',
        'anagata_site:photo' => 'Atur foto, sampul, dan video YouTube.',
        'location:card' => 'Alamat dan titik lokasi gudang.',
        'marquee:logo' => 'Logo mitra pengiriman.',
    ];
@endphp
<x-app-layout>
    <div class="admin-header">
        <h2>Konten</h2>
        <p>Pilih daftar yang ingin dikelola, seperti foto, layanan, lokasi gudang, atau mitra ekspedisi.</p>
    </div>

    <div class="admin-section-grid">
        @foreach($active as $c)
            <a href="{{ route('admin.content.show', [$c['section'], $c['group']]) }}" class="admin-section-card content-dashboard-card">
                <span class="content-card-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} <span aria-hidden="true">↗</span></span>
                <b>{{ $c['label'] }}</b>
                <span class="content-card-description">{{ $descriptions[$c['section'].':'.$c['group']] }}</span>
                <span class="admin-section-card-action">Kelola konten →</span>
            </a>
        @endforeach
        <a href="{{ route('admin.settings.show', 'seo') }}" class="admin-section-card content-dashboard-card content-dashboard-seo">
            <span class="content-card-number">08 <span aria-hidden="true">↗</span></span>
            <b>SEO &amp; Google</b><span class="content-card-description">Judul, deskripsi, dan tampilan saat dibagikan.</span><span class="admin-section-card-action">Kelola SEO →</span>
        </a>
    </div>
    <details class="admin-archive">
        <summary>Konten versi sebelumnya</summary>
        <p>Daftar ini digunakan oleh website versi sebelumnya.</p>
        <div class="admin-section-grid">
            @foreach($archive as $c)
                <a href="{{ route('admin.content.show', [$c['section'], $c['group']]) }}" class="admin-section-card">
                    <b>{{ $c['label'] }}</b><span>{{ $c['parent_label'] }}</span><span class="admin-section-card-action">Buka daftar</span>
                </a>
            @endforeach
        </div>
    </details>
</x-app-layout>
