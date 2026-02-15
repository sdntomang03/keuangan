<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input NPD Massal - Triwulan {{ $triwulanAktif }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('npd.store_massal') }}" method="POST" id="form-massal">
                @csrf

                <div
                    class="bg-white p-4 rounded-lg shadow-sm mb-4 border-l-4 border-indigo-500 flex justify-between items-center text-sm">
                    <div class="flex items-center gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Tanggal NPD</label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                                class="mt-1 border-gray-300 rounded-md shadow-sm text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Nomor Start</label>
                            <span class="font-mono font-bold text-lg text-indigo-700">{{ $nomorNpd }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-indigo-600">Anggaran: {{ $anggaran->nama_anggaran }}</span>
                        <span class="text-xs text-gray-400 italic">*Sisa = Pagu - (Realisasi + Pending)</span>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-800 text-white uppercase font-bold text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left w-1/3">Kegiatan & Rekening</th>
                                    <th class="px-4 py-3 text-right text-gray-300">Pagu</th>
                                    <th class="px-4 py-3 text-right text-red-300">Realisasi</th>
                                    <th class="px-4 py-3 text-right text-orange-300">Pending</th>
                                    <th class="px-4 py-3 text-right text-green-300">Sisa Dana</th>
                                    <th class="px-4 py-3 text-right w-48">Minta (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $globalIndex = 0; @endphp

                                @forelse($listAnggaran as $idbl => $items)
                                <tr class="bg-gray-100">
                                    <td colspan="6" class="px-4 py-2 text-xs font-bold text-gray-700 uppercase">
                                        {{ $items->first()->kegiatan_nama }} </td>
                                </tr>

                                @foreach($items as $item)
                                <tr class="hover:bg-indigo-50 transition">
                                    <td class="px-4 py-3 pl-10">
                                        <div class="text-xs">
                                            <span class="font-mono font-bold text-indigo-900">{{ $item->korek_kode
                                                }}</span><br>
                                            <span class="text-gray-500">{{ $item->korek_uraian }}</span>
                                        </div>

                                        <input type="hidden" name="items[{{ $globalIndex }}][idbl]"
                                            value="{{ $item->idbl }}">
                                        <input type="hidden" name="items[{{ $globalIndex }}][kodeakun]"
                                            value="{{ $item->kodeakun }}">
                                        <input type="hidden" name="items[{{ $globalIndex }}][pagu_hidden]"
                                            value="{{ $item->pagu }}" class="pagu-hidden">
                                        <input type="hidden" name="items[{{ $globalIndex }}][sisa_hidden]"
                                            value="{{ $item->sisa }}" class="sisa-hidden">
                                        <input type="hidden" name="items[{{ $globalIndex }}][nama_rekening_hidden]"
                                            value="{{ $item->korek_uraian }}">
                                    </td>

                                    <td class="px-4 py-3 text-right text-gray-500 text-xs">
                                        {{ number_format($item->pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-500 text-xs">
                                        {{ number_format($item->realisasi, 0, ',', '.') }}

                                        {{-- Input Hidden untuk Realisasi --}}
                                        <input type="hidden" name="items[{{ $globalIndex }}][realisasi]"
                                            value="{{ $item->realisasi }}" class="realisasi-hidden">
                                    </td>
                                    <td class="px-4 py-3 text-right text-orange-500 text-xs">
                                        {{ number_format($item->pending, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600 bg-green-50/30">
                                        {{ number_format($item->sisa, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text" name="items[{{ $globalIndex }}][nominal]"
                                            class="w-full border-gray-300 rounded font-bold text-right text-indigo-700 input-nominal text-sm"
                                            onkeyup="formatRupiah(this)" data-index="{{ $globalIndex }}"
                                            value="{{ $item->realisasi }}">

                                        <div id="error-{{ $globalIndex }}"
                                            class="text-red-500 text-[10px] text-right hidden font-bold">Melebihi Sisa!
                                        </div>
                                    </td>
                                </tr>
                                @php $globalIndex++; @endphp
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Tidak ada
                                        anggaran tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="p-4 bg-gray-100 flex justify-end items-center gap-6 sticky bottom-0 z-10 border-t shadow-inner">
                        <div class="text-right">
                            <span class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Total
                                Pengajuan</span>
                            <span class="text-2xl font-black text-indigo-700" id="grand-total">Rp 0</span>
                        </div>
                        <button type="submit" id="btn-submit" disabled
                            class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg opacity-50 cursor-not-allowed transition transform active:scale-95">
                            SIMPAN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Jalankan saat halaman pertama kali dimuat
    window.addEventListener('DOMContentLoaded', (event) => {
        // Trigger validasi untuk setiap input yang sudah ada nilainya
        document.querySelectorAll('.input-nominal').forEach(inp => {
            // Kita format dulu tampilannya agar ada titik ribuan saat load
            formatRupiah(inp);
        });
        // Hitung grand total pertama kali
        calculateGrandTotal();
    });

    function formatRupiah(input) {
        let value = input.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        input.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        validateRow(input);
    }

    function validateRow(input) {
        const row = input.closest('tr');
        const rawValue = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
        const pagu = parseFloat(row.querySelector('.pagu-hidden').value);
        const errorDiv = row.querySelector(`[id^="error-"]`);

        if (rawValue > pagu) {
            input.classList.add('border-red-500', 'text-red-600', 'bg-red-50');
            input.classList.remove('focus:ring-indigo-500', 'border-green-500', 'bg-green-50', 'text-green-700');
            input.dataset.valid = "false";
            errorDiv.classList.remove('hidden');
        } else {
            input.classList.remove('border-red-500', 'text-red-600', 'bg-red-50');
            input.classList.add('focus:ring-indigo-500');
            input.dataset.valid = "true";
            errorDiv.classList.add('hidden');

            if (rawValue > 0) {
                input.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
            } else {
                input.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
            }
        }
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        let allValid = true;
        let hasInput = false;

        document.querySelectorAll('.input-nominal').forEach(inp => {
            const val = parseFloat(inp.value.replace(/\./g, '').replace(',', '.')) || 0;
            if (val > 0) {
                hasInput = true;
                total += val;
                if (inp.dataset.valid === "false") allValid = false;
            }
        });

        document.getElementById('grand-total').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        const btn = document.getElementById('btn-submit');

        // Tombol aktif jika ada input dan tidak ada yang melebihi pagu
        btn.disabled = !(hasInput && allValid);
        btn.classList.toggle('opacity-50', btn.disabled);
        btn.classList.toggle('cursor-not-allowed', btn.disabled);
    }
    </script>
</x-app-layout>
