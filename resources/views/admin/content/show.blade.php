@php
  $imageKey = collect($repeaterDef['fields'])->search(fn ($f) => $f['type'] === 'image');
  $titleKey = collect($repeaterDef['fields'])->search(fn ($f) => in_array($f['type'], ['text']));
  $locked = $repeaterDef['locked'] ?? false;
@endphp
<x-app-layout>
    <div class="admin-header admin-header-row">
        <h2>{{ $repeaterDef['label'] }}</h2>
        <div class="admin-header-meta">
            {{ count($items) }} item
            <a href="{{ route('admin.content.index') }}" class="btn btn-admin-ghost btn-sm">Kembali</a>
        </div>
    </div>

    <p class="admin-help">Pilih item untuk mengubah isinya. Atur posisi dengan ikon di samping item.</p>

    <div class="admin-card">
        @forelse($items as $i => $item)
            @php
                $title = $titleKey ? ($item['value'][$titleKey] ?: 'Item '.($i + 1)) : 'Item '.($i + 1);
                $thumb = $imageKey ? \App\Support\Cms::imageUrl($item['value'][$imageKey] ?? '') : null;
            @endphp
            <div class="content-item-row">
                <details class="content-list-item">
                    <summary>
                        @if($imageKey)
                            <img src="{{ $thumb ?: asset('images/placeholder-image.png') }}" class="thumb" alt="" loading="lazy" decoding="async">
                        @endif
                        <span class="title">{{ $title }}</span>
                        <span class="meta">Ubah</span>
                    </summary>
                    <div class="content-list-body">
                        @unless($locked)
                            <div class="content-item-tools">
                                <form method="POST" action="{{ route('admin.content.items.destroy', [$section, $group, $item['key']]) }}" data-confirm="Hapus item ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-admin-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        @endunless

                        <form method="POST" action="{{ route('admin.content.items.update', [$section, $group, $item['key']]) }}">
                            @csrf
                            @method('PUT')
                            <div class="fields-grid">
                                @foreach($repeaterDef['fields'] as $fkey => $fdef)
                                    @php $display = cms_display_value($fdef['type'], $item['value'][$fkey] ?? ''); @endphp
                                    @include('admin.partials.field', ['name' => $fkey, 'def' => $fdef, 'value' => $display, 'id' => 'item-'.$item['key'].'-'.$fkey])
                                @endforeach
                            </div>
                            <div class="form-actions">
                                <x-primary-button class="btn-sm">Simpan</x-primary-button>
                            </div>
                        </form>
                    </div>
                </details>

                @include('admin.partials.order-controls', [
                    'up' => $i > 0 ? route('admin.content.items.move', [$section, $group, $item['key'], 'up']) : null,
                    'down' => $i < count($items) - 1 ? route('admin.content.items.move', [$section, $group, $item['key'], 'down']) : null,
                    'subject' => 'item',
                    'class' => 'content-order-controls',
                ])
            </div>
        @empty
            <p class="empty-state">Belum ada konten di daftar ini.</p>
        @endforelse

        @unless($locked)
            <details class="content-add-item">
                <summary>{{ $repeaterDef['add_label'] ?? 'Tambah item' }}</summary>
                <div class="content-list-body">
                    <form method="POST" action="{{ route('admin.content.items.store', [$section, $group]) }}">
                        @csrf
                        <div class="fields-grid">
                            @foreach($repeaterDef['fields'] as $fkey => $fdef)
                                @include('admin.partials.field', ['name' => $fkey, 'def' => $fdef, 'value' => '', 'id' => 'new-'.$group.'-'.$fkey])
                            @endforeach
                        </div>
                        <div class="form-actions">
                            <x-primary-button class="btn-sm">Tambah</x-primary-button>
                        </div>
                    </form>
                </div>
            </details>
        @endunless
    </div>
</x-app-layout>
