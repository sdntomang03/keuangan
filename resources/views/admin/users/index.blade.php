<x-app-layout>
    <div x-data="{ selectedUser: {}, selectedRole: '',selectedSekolah: '' }">

        <x-slot name="header">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight italic">
                    {{ __('Manajemen User') }}
                </h2>
                <a href="{{ route('admin.users.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-5 rounded-lg transition shadow-md flex items-center border border-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah User
                </a>
            </div>
        </x-slot>

        <x-modal name="add-user-modal" focusable>
            <form action="{{ route('admin.users.store') }}" method="post" class="p-8">
                @csrf
                <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">Tambah User Baru</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                    <div class="md:col-span-2">
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full" required autofocus />
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600">{{ __('Simpan User') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                @if(session('success'))
                <div
                    class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded-r-lg shadow-sm">
                    {{ session('success') }}
                </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                <th class="p-4">Informasi User</th>
                                <th class="p-4">Asal Sekolah</th>
                                <th class="p-4">Role</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-indigo-50/30 transition duration-150">
                                <td class="p-4 text-sm font-bold text-gray-800">
                                    {{ $user->name }}
                                    <div class="text-[11px] text-indigo-500 font-medium">{{ $user->email }}</div>
                                </td>
                                <td class="p-4 text-sm text-gray-700">
                                    {{ $user->sekolah->nama_sekolah ?? 'Belum Diatur' }}
                                </td>
                                <td class="p-4 uppercase text-[10px] font-extrabold text-green-700">
                                    {{ $user->getRoleNames()->first() }}
                                </td>
                                <td class="p-4 text-right flex justify-end items-center gap-2">
                                    <button @click="
            selectedUser = { id: '{{ $user->id }}', name: '{{ $user->name }}' };
            selectedRole = '{{ $user->getRoleNames()->first() }}';
            selectedSekolah = '{{ $user->sekolah_id }}';
            $dispatch('open-modal', 'edit-role-modal');
        " class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition duration-200 shadow-sm group"
                                        title="Edit User">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>

                                    <button @click="
    selectedUser = { id: '{{ $user->id }}', name: '{{ $user->name }}' };
    $dispatch('open-modal', 'confirm-user-deletion');
" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition duration-300 shadow-sm active:scale-90"
                                        title="Hapus User">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="
        selectedUser = { id: '{{ $user->id }}', name: '{{ $user->name }}' };
        $dispatch('open-modal', 'confirm-password-reset');
    " class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-600 hover:text-white transition duration-200 shadow-sm"
                                        title="Reset Password ke 12345678">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4 border-t">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="add-user-modal" focusable>
            <form action="{{ route('admin.users.store') }}" method="post" class="p-8">
                @csrf
                <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">Tambah User Baru</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input name="email" type="email" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input name="password" type="password" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="sekolah_id" :value="__('Asal Sekolah')" />
                        <select name="sekolah_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm">
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($sekolahs as $sekolah)
                            <option value="{{ $sekolah->id }}">{{ $sekolah->nama_sekolah }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select name="role" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600">{{ __('Simpan User') }}</x-primary-button>
                </div>
            </form>
        </x-modal>

        <x-modal name="edit-role-modal" focusable>
            <form :action="'{{ url('admin/users') }}/' + selectedUser.id" method="post" class="p-8">
                @csrf
                @method('patch')
                <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">
                    Ubah Akses: <span class="text-indigo-600" x-text="selectedUser.name"></span>
                </h2>
                <div class="space-y-5">
                    <div>
                        <x-input-label for="edit_sekolah" value="Pindah/Atur Sekolah"
                            class="text-[10px] font-bold uppercase text-gray-400" />
                        <select id="edit_sekolah" name="sekolah_id" x-model="selectedSekolah"
                            class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm py-3 text-sm font-semibold">
                            <option value="">-- Tanpa Sekolah (Admin Pusat) --</option>
                            @foreach($sekolahs as $sekolah)
                            <option value="{{ $sekolah->id }}">{{ $sekolah->nama_sekolah }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="edit_role" value="Pilih Jabatan Baru"
                            class="text-[10px] font-bold uppercase text-gray-400" />
                        <select id="edit_role" name="role" x-model="selectedRole"
                            class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm py-3 text-sm font-semibold">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600">{{ __('Update Role') }}</x-primary-button>
                </div>
            </form>
        </x-modal>
        <x-modal name="confirm-user-deletion" focusable>
            <form :action="'{{ url('admin/users') }}/' + selectedUser.id" method="post" class="p-8 text-center">
                @csrf
                @method('delete')

                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900">
                    Hapus User <span class="text-red-600" x-text="selectedUser.name"></span>?
                </h2>

                <p class="mt-3 text-sm text-gray-500 italic">
                    Tindakan ini tidak dapat dibatalkan. Semua data akses user ini akan dihapus secara permanen dari
                    sistem.
                </p>

                <div class="mt-8 flex justify-center gap-4">
                    <button type="button" x-on:click="$dispatch('close')"
                        class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-xl transition">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-red-200 transition active:scale-95">
                        Ya, Hapus Permanen
                    </button>
                </div>
            </form>
        </x-modal>
        <x-modal name="confirm-password-reset" focusable>
            <form :action="'{{ url('admin/users') }}/' + selectedUser.id + '/reset-password'" method="post"
                class="p-8 text-center">
                @csrf
                @method('patch')

                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-6">
                    <svg class="h-10 w-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900">
                    Reset Password <span class="text-amber-600" x-text="selectedUser.name"></span>?
                </h2>

                <p class="mt-3 text-sm text-gray-500">
                    Password user ini akan diatur ulang menjadi default: <span
                        class="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded">12345678</span>
                </p>

                <div class="mt-8 flex justify-center gap-4">
                    <button type="button" x-on:click="$dispatch('close')"
                        class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase">Batal</button>
                    <button type="submit"
                        class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase rounded-xl shadow-lg shadow-amber-200 transition">
                        Ya, Reset Sekarang
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>