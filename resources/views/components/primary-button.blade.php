<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-admin-solid']) }}>
    {{ $slot }}
</button>
