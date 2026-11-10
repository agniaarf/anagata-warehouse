@php
    $previewId = 'preview-'.$id;
    $resolvedUrl = $value ? \App\Support\Cms::imageUrl($value) : '';
@endphp
<div class="image-field video-field">
    <video id="{{ $previewId }}" class="image-field-preview @if(!$resolvedUrl) is-empty @endif" @if($resolvedUrl) src="{{ $resolvedUrl }}" @endif muted playsinline controls preload="metadata"></video>

    <label for="{{ $id }}-file" class="dropzone" id="dropzone-{{ $id }}">
        <span class="dropzone-text">Seret video ke sini atau</span>
        <span class="dropzone-browse">Pilih file</span>
        <span class="dropzone-hint">WebM atau MP4. Maks. 20 MB</span>
    </label>
    <input type="file" id="{{ $id }}-file" accept=".webm,.mp4,video/webm,video/mp4" class="js-video-upload" data-target="{{ $id }}" data-preview="{{ $previewId }}" data-dropzone="dropzone-{{ $id }}" hidden>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
</div>
