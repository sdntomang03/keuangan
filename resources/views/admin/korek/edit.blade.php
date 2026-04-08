<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight uppercase">
                    Edit Kode Rekening
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    Perbarui data untuk kode rekening: <span class="font-bold text-indigo-600">{{ $korek->kode }}</span>
                </p>
            </div>
            <a href="{{ route('admin.korek.index') }}"
                class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-lg text-sm font-bold transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Batal
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <form action="{{ route('admin.korek.update', $korek->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        {{-- Kode Rekening --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kode Rekening <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="kode" value="{{ old('kode', $korek->kode) }}" required
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 font-mono @error('kode') border-red-500 @enderror">
                            @error('kode') <span class="text-xs text-red-500 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Uraian Singkat --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Uraian / Nama Rekening</label>
                            <input type="text" name="uraian_singkat"
                                value="{{ old('uraian_singkat', $korek->uraian_singkat) }}"
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            @error('uraian_singkat') <span class="text-xs text-red-500 font-medium">{{ $message
                                }}</span> @enderror
                        </div>

                        {{-- Keterangan (Ket) --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Keterangan Tambahan</label>
                            <textarea name="ket" rows="3"
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('ket', $korek->ket) }}</textarea>
                        </div>

                        {{-- Singkatan --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Singkatan</label>
                            <input type="text" name="singkat" value="{{ old('singkat', $korek->singkat) }}"
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Jenis Belanja --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Belanja</label>
                            <select name="jenis_belanja"
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Pilih Jenis Belanja --</option>
                                @foreach($jenisBelanjaList as $jenis)
                                <option value="{{ $jenis }}" {{ old('jenis_belanja', $korek->jenis_belanja) == $jenis ?
                                    'selected' : '' }}>
                                    {{ strtoupper($jenis) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-black uppercase tracking-wider hover:bg-indigo-700 transition shadow-md">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>