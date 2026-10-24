@php
  $collections = collect(content_collections())->where('section', $meta['schema_section']);
@endphp
<x-app-layout>
    <div class="admin-header">
        <h2>{{ $meta['label'] }}</h2>
        <p><a href="{{ route('admin.website.homepage') }}" class="form-link">Kembali ke halaman utama</a></p>
    </div>

    @if(count($schema['fields']))
        <div class="admin-card">
            <form method="POST" action="{{ route('admin.website.section.update', $slug) }}">
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
    @endif

    @if($collections->isNotEmpty())
        <div class="admin-card">
            <h3 style="margin-bottom:12px;">Konten bagian ini</h3>
            <p style="color:var(--muted);font-size:13.5px;margin-bottom:14px;">
                Foto, kartu, dan daftar untuk bagian ini dikelola di sini.
            </p>
            @foreach($collections as $c)
                <a href="{{ route('admin.content.show', [$c['section'], $c['group']]) }}" class="btn btn-admin-ghost btn-sm" style="margin-right:8px;margin-bottom:8px;">
                    Kelola {{ $c['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>
