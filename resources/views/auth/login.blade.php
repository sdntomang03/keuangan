<x-guest-layout>
    <div class="mb-8 text-center">
        <div
            class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200 mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            Selamat Datang Kembali
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Silakan masuk untuk mengakses sistem keuangan.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Instansi')"
                class="font-semibold text-gray-700 dark:text-gray-300" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <x-text-input id="email"
                    class="block w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition duration-200"
                    type="email" name="email" :value="old('email')" required autofocus
                    placeholder="nama@sekolah.sch.id" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Kata Sandi')"
                    class="font-semibold text-gray-700 dark:text-gray-300" />
                @if (Route::has('password.request'))
                <a class="text-xs font-bold text-indigo-600 hover:text-indigo-500 transition duration-150"
                    href="{{ route('password.request') }}">
                    {{ __('Lupa Sandi?') }}
                </a>
                @endif
            </div>
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <x-text-input id="password"
                    class="block w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition duration-200"
                    type="password" name="password" required placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-900"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="mt-6">
            <x-primary-button
                class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 rounded-xl text-sm font-bold tracking-widest transition-all duration-200 transform hover:scale-[1.01] shadow-lg shadow-indigo-100 dark:shadow-none">
                {{ __('Login') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
            Belum terdaftar?
            <a href="{{ route('register') }}"
                class="font-bold text-indigo-600 hover:text-indigo-500 transition duration-150">
                Daftarkan Instansi
            </a>
        </p>
        @endif
    </form>
</x-guest-layout>
