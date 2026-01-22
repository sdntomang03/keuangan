<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight italic">
                {{ __('Pendaftaran User Baru') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8">

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="name" :value="__('Nama Lengkap')"
                                class="text-xs font-bold uppercase text-gray-400" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full py-3 shadow-sm"
                                :value="old('name')" required autofocus placeholder="Masukkan nama lengkap..." />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Alamat Email')"
                                class="text-xs font-bold uppercase text-gray-400" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full py-3 shadow-sm"
                                :value="old('email')" required placeholder="email@contoh.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="password" :value="__('Password')"
                                    class="text-xs font-bold uppercase text-gray-400" />
                                <x-text-input id="password" name="password" type="password"
                                    class="mt-1 block w-full py-3 shadow-sm" required />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="role" :value="__('Role / Jabatan')"
                                    class="text-xs font-bold uppercase text-gray-400" />
                                <select id="role" name="role"
                                    class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-sm py-3"
                                    required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role')==$role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="sekolah_id" :value="__('Asal Sekolah')"
                                class="text-xs font-bold uppercase text-gray-400" />
                            <select id="sekolah_id" name="sekolah_id"
                                class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-sm py-3">
                                <option value="">-- Pilih Sekolah (Opsional) --</option>
                                @foreach($sekolahs as $sekolah)
                                <option value="{{ $sekolah->id }}" {{ old('sekolah_id')==$sekolah->id ? 'selected' : ''
                                    }}>
                                    {{ $sekolah->nama_sekolah }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-[10px] text-gray-400 italic font-medium">* Kosongkan jika user adalah
                                Admin Pusat.</p>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end gap-3 border-t pt-8">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-widest hover:text-gray-700 transition">
                            Batal
                        </a>
                        <x-primary-button class="px-10 py-3 bg-indigo-600 hover:bg-indigo-700 shadow-lg transition">
                            {{ __('Simpan User') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>