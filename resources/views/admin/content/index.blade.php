<x-app-layout>
    <div class="admin-header">
        <h2>Konten</h2>
        <p>Pilih daftar yang ingin dikelola, seperti foto, layanan, lokasi gudang, atau mitra ekspedisi.</p>
    </div>

    <div class="admin-section-grid">
        @foreach($collections as $c)
            <a href="{{ route('admin.content.show', [$c['section'], $c['group']]) }}" class="admin-section-card">
                <b>{{ $c['label'] }}</b>
                <span>{{ $c['parent_label'] }}</span>
                <span class="admin-section-card-action">Buka daftar</span>
            </a>
        @endforeach
    </div>
</x-app-layout>
