<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-gray-800 leading-tight uppercase tracking-widest">
            Cetak Cover LPJ (Kertas Biru)
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('surat.cover_lpj.generate') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200 p-6 mb-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload Logo (Opsional)</label>
                            <input type="file" name="logo" accept="image/png, image/jpeg"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor SPJ</label>
                            <input type="text" name="nomor_spj" required placeholder="Contoh: 01"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="mb-8 border-b pb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Bantuan</label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="jenis_bantuan" value="BOP" checked
                                    class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 font-bold">BOP</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="jenis_bantuan" value="BOSP"
                                    class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 font-bold">BOSP</span>
                            </label>
                        </div>
                    </div>

                    <label class="block text-lg font-black text-gray-800 mb-4">Pilih Dokumen & Kode Rekening</label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value=" " checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Cover Utama (Tanpa Label)</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="REKENING KORAN" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Rekening Koran</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="BUKU KAS UMUM" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Buku Kas Umum</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="BUKU BANK" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Buku Bank</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="REKAP PAJAK" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Rekap Pajak</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]"
                                value="FORM MONITORING PENGELUARAN TRIWULAN" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Form Monitoring Pengeluaran Triwulan</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="FORM MONITORING TRANSFER" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Form Monitoring Transfer</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="SPTJM" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">SPTJM</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="BUKTI PENGEMBALIAN" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Bukti Pengembalian</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="BA KAS" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">BA KAS</span>
                        </label>

                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]" value="FORMAT 1A s/d 1E" checked
                                class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="ml-3 font-semibold text-gray-800">Format 1A s/d 1E</span>
                        </label>

                        @if(isset($listRekening) && count($listRekening) > 0)
                        <div class="col-span-1 md:col-span-2 mt-4 mb-2">
                            <label class="block text-sm font-bold text-gray-700">Kode Rekening Anggaran</label>
                        </div>

                        @foreach($listRekening as $rek)
                        <label
                            class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                            <input type="checkbox" name="rekening_terpilih[]"
                                value="{{ $rek->korek->ket ?? 'Rincian Belanja' }}"
                                class="mt-0.5 w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                checked>
                            <div class="ml-3">

                                <span class="block text-xs text-gray-500">{{ $rek->korek->ket ?? 'Rincian Belanja'
                                    }}</span>
                            </div>
                        </label>
                        @endforeach
                        @endif
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit"
                            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Generate Cover PDF
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
