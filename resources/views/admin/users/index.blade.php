<x-app-layout>
    <div x-data="{
        activeTab: 'users', // Default tab yang terbuka

        // State untuk User
        selectedUser: {},
        selectedRole: '',
        selectedSekolah: '',

        // State untuk Role
        selectedRoleData: { id: '', name: '', permissions: [] },

        // State untuk Permission
        selectedPermission: { id: '', name: '' }
    }">

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight italic">
                {{ __('Manajemen Akses & User') }}
            </h2>
        </x-slot>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                {{-- TABS NAVIGATION DIPINDAHKAN KE SINI AGAR TERBACA OLEH ALPINE.JS --}}
                <div
                    class="mb-6 flex overflow-x-auto bg-gray-100 p-1.5 rounded-xl shadow-sm border border-gray-200 w-fit">
                    <button @click="activeTab = 'users'"
                        :class="activeTab === 'users' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'"
                        class="px-5 py-2.5 rounded-lg text-sm transition-all duration-200 whitespace-nowrap">
                        👨‍💼 Data User
                    </button>
                    <button @click="activeTab = 'roles'"
                        :class="activeTab === 'roles' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'"
                        class="px-5 py-2.5 rounded-lg text-sm transition-all duration-200 whitespace-nowrap">
                        🛡️ Manajemen Role
                    </button>
                    <button @click="activeTab = 'permissions'"
                        :class="activeTab === 'permissions' ? 'bg-white text-indigo-700 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'"
                        class="px-5 py-2.5 rounded-lg text-sm transition-all duration-200 whitespace-nowrap">
                        🔑 Hak Akses (Permission)
                    </button>
                </div>
                <div class="py-8">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                        {{-- ALERT SUCCESS --}}
                        @if(session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded-r-lg shadow-sm flex justify-between items-center"
                            x-data="{ show: true }" x-show="show">
                            {{ session('success') }}
                            <button @click="show = false"
                                class="text-emerald-500 hover:text-emerald-700">&times;</button>
                        </div>
                        @endif
                        @if(session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-lg shadow-sm flex justify-between items-center"
                            x-data="{ show: true }" x-show="show">
                            {{ session('error') }}
                            <button @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
                        </div>
                        @endif
                        @if($errors->any())
                        <div
                            class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-lg shadow-sm">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- ======================================================= --}}
                        {{-- TAB 1: MANAJEMEN USER --}}
                        {{-- ======================================================= --}}
                        <div x-show="activeTab === 'users'" x-transition.opacity style="display: none;">
                            <div class="flex justify-end mb-4">
                                <button x-on:click="$dispatch('open-modal', 'add-user-modal')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-5 rounded-lg transition shadow-md flex items-center border border-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah User Baru
                                </button>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                            <th class="p-4">Informasi User</th>
                                            <th class="p-4">Asal Sekolah</th>
                                            <th class="p-4">Role Aktif</th>
                                            <th class="p-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($users as $user)
                                        <tr class="hover:bg-indigo-50/30 transition duration-150">
                                            <td class="p-4 text-sm font-bold text-gray-800">
                                                {{ $user->name }}
                                                <div class="text-[11px] text-indigo-500 font-medium">{{ $user->email }}
                                                </div>
                                            </td>
                                            <td class="p-4 text-sm text-gray-700">
                                                {{ $user->sekolah->nama_sekolah ?? 'Tanpa Sekolah / Pusat' }}
                                            </td>
                                            <td class="p-4 uppercase text-[10px] font-extrabold text-emerald-600">
                                                <span class="bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                                    {{ $user->getRoleNames()->first() ?? 'Tidak Ada Role' }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-right flex justify-end items-center gap-2">
                                                {{-- Tombol Edit User --}}
                                                <button @click="
                                            selectedUser = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}' };
                                            selectedRole = '{{ $user->getRoleNames()->first() }}';
                                            selectedSekolah = '{{ $user->sekolah_id }}';
                                            $dispatch('open-modal', 'edit-role-modal');"
                                                    class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition shadow-sm"
                                                    title="Edit Role & Sekolah">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </button>
                                                {{-- Tombol Reset Password --}}
                                                <button @click="
                                            selectedUser = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}' };
                                            $dispatch('open-modal', 'confirm-password-reset');"
                                                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-600 hover:text-white transition shadow-sm"
                                                    title="Reset Password ke 12345678">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                        </path>
                                                    </svg>
                                                </button>
                                                {{-- Tombol Hapus --}}
                                                <button @click="
                                            selectedUser = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}' };
                                            $dispatch('open-modal', 'confirm-user-deletion');"
                                                    class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm"
                                                    title="Hapus User">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
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

                        {{-- ======================================================= --}}
                        {{-- TAB 2: MANAJEMEN ROLE --}}
                        {{-- ======================================================= --}}
                        <div x-show="activeTab === 'roles'" x-transition.opacity style="display: none;">
                            <div class="flex justify-end mb-4">
                                <button x-on:click="$dispatch('open-modal', 'add-role-modal')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-5 rounded-lg transition shadow-md flex items-center border border-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Role Baru
                                </button>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                            <th class="p-4 w-1/4">Nama Role</th>
                                            <th class="p-4 w-2/4">Hak Akses (Permissions)</th>
                                            <th class="p-4 w-1/4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($roles as $role)
                                        <tr class="hover:bg-indigo-50/30 transition duration-150">
                                            <td class="p-4 text-sm font-bold text-gray-800 uppercase">{{ $role->name }}
                                            </td>
                                            <td class="p-4">
                                                <div class="flex flex-wrap gap-1.5">
                                                    @forelse($role->permissions as $perm)
                                                    <span
                                                        class="bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold px-2 py-1 rounded-md">
                                                        {{ $perm->name }}
                                                    </span>
                                                    @empty
                                                    <span class="text-xs text-gray-400 italic">Belum ada
                                                        permission</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="p-4 text-right flex justify-end items-center gap-2">
                                                <button @click="
                                            selectedRoleData = {
                                                id: '{{ $role->id }}',
                                                name: '{{ addslashes($role->name) }}',
                                                permissions: {{ json_encode($role->permissions->pluck('name')) }}
                                            };
                                            $dispatch('open-modal', 'edit-role-permission-modal');"
                                                    class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition shadow-sm"
                                                    title="Atur Permissions">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                    </svg>
                                                </button>

                                                @if($role->name !== 'Super Admin')
                                                <button @click="
                                            selectedRoleData = { id: '{{ $role->id }}', name: '{{ addslashes($role->name) }}' };
                                            $dispatch('open-modal', 'confirm-role-deletion');"
                                                    class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm"
                                                    title="Hapus Role">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ======================================================= --}}
                        {{-- TAB 3: MANAJEMEN PERMISSION --}}
                        {{-- ======================================================= --}}
                        <div x-show="activeTab === 'permissions'" x-transition.opacity style="display: none;">
                            <div class="flex justify-end mb-4">
                                <button x-on:click="$dispatch('open-modal', 'add-permission-modal')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-5 rounded-lg transition shadow-md flex items-center border border-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Permission Baru
                                </button>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                            <th class="p-4 w-3/4">Nama Permission</th>
                                            <th class="p-4 w-1/4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($permissions as $perm)
                                        <tr class="hover:bg-indigo-50/30 transition duration-150">
                                            <td class="p-4 text-sm font-bold text-gray-800 tracking-wide font-mono">{{
                                                $perm->name }}</td>
                                            <td class="p-4 text-right flex justify-end items-center gap-2">
                                                <button @click="
                                            selectedPermission = { id: '{{ $perm->id }}', name: '{{ addslashes($perm->name) }}' };
                                            $dispatch('open-modal', 'confirm-permission-deletion');"
                                                    class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm"
                                                    title="Hapus Permission">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- AREA MODALS (POPUP) --}}
                {{-- ========================================================================= --}}

                {{-- Modal Tambah User --}}
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
                                <select name="sekolah_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                    <option value="">-- Tanpa Sekolah / Pusat --</option>
                                    @foreach($sekolahs as $sekolah)
                                    <option value="{{ $sekolah->id }}">{{ $sekolah->nama_sekolah }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="role" :value="__('Role')" />
                                <select name="role"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                                    required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-primary-button class="bg-indigo-600">Simpan User</x-primary-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modal Edit Role & Sekolah User --}}
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
                            <x-primary-button class="bg-indigo-600">Update Role</x-primary-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modal Tambah Role --}}
                <x-modal name="add-role-modal" focusable>
                    <form action="{{ route('admin.roles.store') }}" method="post" class="p-8">
                        @csrf
                        <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">Tambah Role Baru</h2>
                        <div class="mb-4">
                            <x-input-label for="role_name" :value="__('Nama Role')" />
                            <x-text-input name="name" id="role_name" type="text" class="mt-1 block w-full"
                                placeholder="ex: Kepala Sekolah" required />
                        </div>
                        <div class="mb-4">
                            <x-input-label value="Pilih Permission Awal (Opsional)" class="mb-2" />
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-100 rounded bg-gray-50/50">
                                @foreach($permissions as $perm)
                                <label
                                    class="flex items-center space-x-2 text-sm cursor-pointer hover:bg-gray-100 p-1 rounded">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 font-mono text-xs">{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-primary-button class="bg-indigo-600">Simpan Role</x-primary-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modal Edit Role Permission --}}
                <x-modal name="edit-role-permission-modal" focusable>
                    <form :action="'{{ url('admin/roles') }}/' + selectedRoleData.id + '/permissions'" method="post"
                        class="p-8">
                        @csrf
                        @method('put')
                        <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">
                            Atur Permission Role: <span class="text-indigo-600 uppercase"
                                x-text="selectedRoleData.name"></span>
                        </h2>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-3 border border-gray-200 rounded-xl bg-gray-50/50">
                            @foreach($permissions as $perm)
                            <label
                                class="flex items-center space-x-3 text-sm cursor-pointer hover:bg-white p-2 rounded-lg border border-transparent hover:border-gray-200 transition shadow-sm">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                    x-bind:checked="selectedRoleData.permissions.includes('{{ $perm->name }}')">
                                <span class="text-gray-700 font-bold font-mono text-[11px]">{{ $perm->name }}</span>
                            </label>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-primary-button class="bg-indigo-600">Simpan Perubahan</x-primary-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modal Tambah Permission --}}
                <x-modal name="add-permission-modal" focusable>
                    <form action="{{ route('admin.permissions.store') }}" method="post" class="p-8">
                        @csrf
                        <h2 class="text-xl font-extrabold text-gray-900 border-b pb-4 mb-6">Tambah Permission</h2>
                        <div>
                            <x-input-label for="perm_name"
                                value="Nama Permission (Gunakan format: buat-laporan, edit-transaksi)" />
                            <x-text-input name="name" id="perm_name" type="text"
                                class="mt-1 block w-full font-mono text-sm" placeholder="ex: akses-laporan-keuangan"
                                required />
                        </div>
                        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                            <x-primary-button class="bg-indigo-600">Simpan Permission</x-primary-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modal Konfirmasi Hapus (Re-usable layout) --}}
                <x-modal name="confirm-user-deletion" focusable>
                    <form :action="'{{ url('admin/users') }}/' + selectedUser.id" method="post" class="p-8 text-center">
                        @csrf @method('delete')
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Hapus User <span class="text-red-600"
                                x-text="selectedUser.name"></span>?</h2>
                        <div class="mt-8 flex justify-center gap-4">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-xl transition">Batal</button>
                            <button type="submit"
                                class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-red-200 transition active:scale-95">Ya,
                                Hapus</button>
                        </div>
                    </form>
                </x-modal>

                <x-modal name="confirm-role-deletion" focusable>
                    <form :action="'{{ url('admin/roles') }}/' + selectedRoleData.id" method="post"
                        class="p-8 text-center">
                        @csrf @method('delete')
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Hapus Role <span class="text-red-600 uppercase"
                                x-text="selectedRoleData.name"></span>?</h2>
                        <div class="mt-8 flex justify-center gap-4">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-xl transition">Batal</button>
                            <button type="submit"
                                class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-red-200 transition active:scale-95">Ya,
                                Hapus</button>
                        </div>
                    </form>
                </x-modal>

                <x-modal name="confirm-permission-deletion" focusable>
                    <form :action="'{{ url('admin/permissions') }}/' + selectedPermission.id" method="post"
                        class="p-8 text-center">
                        @csrf @method('delete')
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Hapus Permission <span
                                class="text-red-600 font-mono" x-text="selectedPermission.name"></span>?</h2>
                        <div class="mt-8 flex justify-center gap-4">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-xl transition">Batal</button>
                            <button type="submit"
                                class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-red-200 transition active:scale-95">Ya,
                                Hapus</button>
                        </div>
                    </form>
                </x-modal>

                <x-modal name="confirm-password-reset" focusable>
                    <form :action="'{{ url('admin/users') }}/' + selectedUser.id + '/reset-password'" method="post"
                        class="p-8 text-center">
                        @csrf @method('patch')
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-6">
                            <svg class="h-10 w-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Reset Password <span class="text-amber-600"
                                x-text="selectedUser.name"></span>?</h2>
                        <div class="mt-8 flex justify-center gap-4">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase">Batal</button>
                            <button type="submit"
                                class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase rounded-xl shadow-lg shadow-amber-200 transition">Ya,
                                Reset</button>
                        </div>
                    </form>
                </x-modal>

            </div>
</x-app-layout>