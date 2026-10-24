@php
    $previewId = 'preview-'.$id;
    $resolvedUrl = $value ? \App\Support\Cms::imageUrl($value) : '';
@endphp
<div class="image-field">
    <img src="{{ $resolvedUrl ?: asset('images/placeholder-image.png') }}" class="image-field-preview" id="{{ $previewId }}" alt="" loading="lazy" decoding="async">

    <label for="{{ $id }}-file" class="dropzone" id="dropzone-{{ $id }}">
        <span class="dropzone-text">Seret gambar ke sini atau</span>
        <span class="dropzone-browse">Pilih file</span>
        <span class="dropzone-hint">JPG, JPEG, atau PNG. Maks. 2 MB</span>
    </label>
    <input type="file" id="{{ $id }}-file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="js-image-upload" data-target="{{ $id }}" data-preview="{{ $previewId }}" data-dropzone="dropzone-{{ $id }}" hidden>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
</div>
