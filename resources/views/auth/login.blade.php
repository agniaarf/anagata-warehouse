<x-guest-layout>
    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="field">
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label class="section-nav-checkbox">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Tetap masuk</span>
        </label>

        <div class="form-row-between">
            @if (Route::has('password.request'))
                <a class="form-link" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button>Masuk</x-primary-button>
        </div>
    </form>
</x-guest-layout>
