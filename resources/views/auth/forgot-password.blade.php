<x-guest-layout>
    <p class="auth-hint">
        Masukkan email akun admin untuk meminta tautan penggantian kata sandi.
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="form-actions">
            <x-primary-button>Kirim tautan</x-primary-button>
        </div>
    </form>
</x-guest-layout>
