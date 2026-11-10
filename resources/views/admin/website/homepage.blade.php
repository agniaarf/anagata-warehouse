@php
  $allCollections = collect(content_collections());
@endphp
<x-app-layout>
    <div class="admin-header">
        <h2>Halaman utama</h2>
        <p>Kelola isi website dan lihat hasilnya sebelum dipublikasikan.</p>
    </div>

    <div class="admin-section-grid admin-home-shortcuts">
        <a class="admin-section-card" href="{{ route('admin.content.index') }}"><b>Kelola konten</b><span>Cerita, layanan, kolase, lokasi, dan mitra.</span><span class="admin-section-card-action">Buka konten →</span></a>
        <a class="admin-section-card" href="{{ route('admin.settings.show', 'contact') }}"><b>Informasi kontak</b><span>Atur WhatsApp, email, dan Instagram.</span><span class="admin-section-card-action">Buka pengaturan →</span></a>
        <a class="admin-section-card" href="{{ route('landing.preview') }}" target="_blank" rel="noopener noreferrer"><b>Preview website</b><span>Lihat tampilan halaman yang sedang dikelola.</span><span class="admin-section-card-action">Buka preview ↗</span></a>
    </div>
    <details class="admin-archive">
    <summary>Pengaturan versi sebelumnya</summary>
    <p>Pengaturan tampilan dan urutan di bawah berlaku untuk website sebelumnya.</p>
    <div class="section-list">
        @foreach($layout as $i => $entry)
            @php
                $slug = $entry['key'];
                $meta = $pageSections[$slug];
                $v = $entry['value'];
                $collections = $allCollections->where('section', $meta['schema_section']);
                $canMoveUp = !$meta['locked'] && $i > 0 && !$pageSections[$layout[$i - 1]['key']]['locked'];
                $canMoveDown = !$meta['locked'] && $i < count($layout) - 1 && !$pageSections[$layout[$i + 1]['key']]['locked'];
            @endphp
            <div class="section-card">
                <div class="section-card-row">
                    @include('admin.partials.order-controls', [
                        'up' => $canMoveUp ? route('admin.website.move', [$slug, 'up']) : null,
                        'down' => $canMoveDown ? route('admin.website.move', [$slug, 'down']) : null,
                        'subject' => 'bagian',
                        'class' => 'section-card-order',
                    ])

                    <div class="section-card-title">
                        <strong>{{ $meta['label'] }}</strong>
                        <span>{{ $meta['description'] }}</span>
                    </div>

                    @if(count(config("cms_schema.{$slug}.fields", [])))
                        <a href="{{ route('admin.website.section.edit', $slug) }}" class="section-card-edit-link">Ubah teks</a>
                    @endif

                    @unless($meta['locked'])
                        <form method="POST" action="{{ route('admin.website.toggle-visible', $slug) }}">
                            @csrf
                            <label class="section-visibility">
                                <span>{{ $v['visible'] ? 'Tampil' : 'Disembunyikan' }}</span>
                                <span class="switch">
                                    <input type="checkbox" class="js-auto-submit" aria-label="Tampilkan {{ $meta['label'] }}" {{ $v['visible'] ? 'checked' : '' }}>
                                    <span class="switch-track"></span>
                                </span>
                            </label>
                        </form>
                    @else
                        <span class="section-card-locked">Selalu tampil</span>
                    @endunless
                </div>

                @if($meta['anchor'])
                    <form method="POST" action="{{ route('admin.website.update-nav', $slug) }}" class="section-card-nav">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="show_in_nav" value="0">
                        <label class="section-nav-checkbox"><input type="checkbox" name="show_in_nav" value="1" class="js-auto-submit" {{ $v['show_in_nav'] ? 'checked' : '' }}> <span>Tampilkan sebagai menu navigasi</span></label>
                        <label for="nav-label-{{ $slug }}">Nama menu</label>
                        <input id="nav-label-{{ $slug }}" type="text" name="nav_label" value="{{ $v['nav_label'] }}" onblur="this.form.requestSubmit()">
                    </form>
                @endif

                @if($collections->isNotEmpty())
                    <div class="section-card-content-links">
                        @foreach($collections as $c)
                            <a href="{{ route('admin.content.show', [$c['section'], $c['group']]) }}" class="form-link">Kelola {{ $c['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    </details>
</x-app-layout>
