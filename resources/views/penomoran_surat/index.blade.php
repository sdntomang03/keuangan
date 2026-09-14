<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">Pengaturan Nomor Surat</h2>
                <p class="text-sm text-gray-500">Atur nomor urut awal surat untuk setiap triwulan.</p>
            </div>

            {{-- Alerts --}}
            {{-- Alerts Success --}}
            @if (session('success'))
            <div
                class="mb-4 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 rounded-xl shadow-sm text-sm font-bold">
                {{ session('success') }}
            </div>
            @endif

            {{-- Alert Error Database --}}
            @if (session('error'))
            <div
                class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm text-sm font-bold">
                {{ session('error') }}
            </div>
            @endif

            {{-- Alert Validasi Form --}}
            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm text-sm">
                <ul class="list-disc list-inside font-semibold">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Form Input / Update --}}
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Set / Update Nomor Awal</h3>

                        <form action="{{ route('penomoran-surat.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Tahun</label>
                                <input type="number" name="tahun" value="{{ date('Y') }}" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Triwulan</label>
                                <select name="triwulan" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-blue-500">
                                    <option value="1">Triwulan 1</option>
                                    <option value="2">Triwulan 2</option>
                                    <option value="3">Triwulan 3</option>
                                    <option value="4">Triwulan 4</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-1">Nomor Mulai
                                    Dari</label>
                                <input type="number" name="nomor_awal" min="1" placeholder="Contoh: 1 atau 150" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-blue-500 font-mono font-bold text-blue-600">
                            </div>

                            <button type="submit"
                                class="w-full px-4 py-2.5 bg-blue-600 text-white text-xs font-black uppercase rounded-xl hover:bg-blue-700 shadow-md transition">
                                Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Tabel Daftar Penomoran --}}
                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest font-black text-gray-500">
                                <tr>
                                    <th class="px-6 py-4">Tahun</th>
                                    <th class="px-6 py-4">Triwulan</th>
                                    <th class="px-6 py-4 text-center">Nomor Awal</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($penomorans as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-700">{{ $item->tahun }}</td>
                                    <td class="px-6 py-4">Triwulan {{ $item->triwulan }}</td>
                                    <td class="px-6 py-4 text-center font-mono font-bold text-blue-600">
                                        {{ str_pad($item->nomor_awal, 3, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('penomoran-surat.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus pengaturan penomoran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 font-bold inline-flex items-center gap-1 bg-red-50 px-2 py-1 rounded">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">
                                        Belum ada pengaturan penomoran surat. Sistem akan menggunakan nomor default
                                        (001).
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>