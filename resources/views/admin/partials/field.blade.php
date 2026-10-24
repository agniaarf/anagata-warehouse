@php $type = $def['type']; @endphp

@if($type === 'checkbox')
    <div class="field field--checkbox">
        <label class="field-checkbox">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value)>
            {{ $def['label'] ?? $name }}
        </label>
    </div>
@else
    <div class="field field--{{ $type }}">
        <x-input-label :for="$id" :value="$def['label'] ?? $name" />

        @switch($type)
            @case('textarea')
            @case('list')
            @case('delimited_list')
                <textarea id="{{ $id }}" name="{{ $name }}" class="field-textarea">{{ $value }}</textarea>
                @break

            @case('select')
                <select id="{{ $id }}" name="{{ $name }}" class="field-select">
                    @foreach($def['options'] as $optVal => $optLabel)
                        <option value="{{ $optVal }}" @selected((string) $value === (string) $optVal)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('number')
                <input type="number" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" step="{{ $def['step'] ?? '1' }}" class="field-input">
                @break

            @case('color')
                <input type="color" id="{{ $id }}" name="{{ $name }}" value="{{ $value ?: '#610890' }}" class="field-color">
                @break

            @case('image')
                @include('admin.partials.field-image', ['name' => $name, 'value' => $value, 'id' => $id])
                @break

            @default
                <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" class="field-input">
        @endswitch

        @if(!empty($def['hint']))
            <div class="field-hint">{{ $def['hint'] }}</div>
        @endif
    </div>
@endif
