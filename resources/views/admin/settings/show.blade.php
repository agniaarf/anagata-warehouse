<x-app-layout>
    <div class="admin-header">
        <h2>{{ $tabs[$tab]['label'] }}</h2>
        <p>{{ [
            'branding' => 'Ubah logo dan warna yang digunakan di situs.',
            'contact' => 'Ubah nomor WhatsApp, alamat email, dan akun Instagram.',
            'seo' => 'Ubah judul dan deskripsi yang tampil di hasil Google dan saat tautan dibagikan.',
        ][$tab] }}</p>
    </div>

    <div class="settings-tabs">
        @foreach($tabs as $key => $def)
            <a href="{{ route('admin.settings.show', $key) }}" class="settings-tab {{ $tab === $key ? 'active' : '' }}">{{ $def['label'] }}</a>
        @endforeach
    </div>

    <div class="admin-card">
        <form method="POST" action="{{ route('admin.settings.update', $tab) }}">
            @csrf
            @method('PUT')

            <div class="fields-grid">
                @foreach($schema['fields'] as $key => $def)
                    @php
                        $raw = cms_field($rows, $key);
                        $display = cms_display_value($def['type'], $raw);
                    @endphp
                    @include('admin.partials.field', ['name' => $key, 'def' => $def, 'value' => $display, 'id' => 'field-'.$key])
                @endforeach
            </div>

            <div class="form-actions">
            <x-primary-button>Simpan perubahan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
