<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ubah Role User') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">

                <div class="flex items-center mb-8 pb-6 border-b">
                    <div
                        class="h-14 w-14 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <form method="post" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="role" :value="__('Pilih Role / Jabatan Baru')" />
                        <select id="role" name="role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('role')" />
                        <p class="mt-2 text-xs text-gray-500 italic">
                            *Setiap user hanya diperbolehkan memiliki satu role utama dalam sistem ini.
                        </p>
                    </div>

                    <div class="flex items-center justify-end mt-6 gap-4">
                        <a href="{{ route('admin.users.index') }}"
                            class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>
                            {{ __('Simpan') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>