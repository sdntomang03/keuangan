<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper {
            position: relative;
            z-index: 50 !important;
        }

        .ts-dropdown {
            z-index: 100 !important;
            border-radius: 1rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.5rem !important;
            border-color: #e5e7eb !important;
            background: white !important;
        }

        .ts-wrapper.multi .ts-control>div {
            border-radius: 5px !important;
            background: #ecfdf5 !important;
            color: #065f46 !important;
            border: 1px solid #10b981 !important;
        }

        @media print {

            /* Sembunyikan semua elemen dengan class no-print */
            .no-print {
                display: none !important;
            }

            /* Menghilangkan padding abu-abu di latar belakang saat cetak */
            .py-8 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            body {
                background-color: white !important;
            }

            /* Memastikan tabel mengambil lebar penuh kertas */
            .max-w-7xl {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Menghilangkan shadow/border agar lebih bersih di kertas */
            .shadow-sm,
            .shadow-xl {
                shadow: none !important;
                box-shadow: none !important;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Laporan Persediaan Barang — <span class="text-emerald-600 font-black tracking-tighter">TW {{
                    $triwulanAktif }}</span>
            </h2>
            <button onclick="window.print()"
                class="bg-white border px-4 py-2 rounded-lg text-xs font-bold uppercase shadow-sm hover:bg-gray-50 transition-all">Cetak</button>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 no-print">
                <div
                    class="bg-emerald-600 rounded-[2rem] p-8 shadow-xl shadow-emerald-200 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-emerald-100 text-xs font-black uppercase tracking-[0.2em] mb-2">Total Akumulasi
                            Bruto</p>
                        <div class="flex items-baseline gap-3">
                            <span class="text-2xl font-medium opacity-70">Rp</span>
                            <h1 class="text-5xl font-black tracking-tighter">
                                {{ number_format($totalNilai, 0, ',', '.') }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-1 rounded-3xl shadow-sm border border-gray-200 mb-8 relative z-40 no-print">
                <div class="bg-gray-50/50 p-6 rounded-[22px]">
                    <form action="{{ route('persediaan.index') }}" method="GET"
                        class="flex flex-col lg:flex-row gap-4 items-end">
                        <div class="w-full lg:w-1/4">
                            <label
                                class="text-[11px] font-black text-emerald-900 uppercase mb-2 ml-1 flex items-center gap-2">Cari
                                Barang</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama..."
                                class="w-full pl-4 pr-4 py-2.5 bg-white border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-2xl text-sm transition-all">
                        </div>

                        <div class="w-full lg:flex-1">
                            <label
                                class="text-[11px] font-black text-emerald-900 uppercase mb-2 ml-1 flex items-center gap-2">Filter
                                Rekening</label>
                            <select id="select-korek" name="korek[]" multiple autocomplete="off" class="w-full">
                                @foreach($listKorek as $k)
                                <option value="{{ $k->kodeakun }}" {{ is_array($filterKorek) && in_array($k->kodeakun,
                                    $filterKorek) ? 'selected' : '' }}>
                                    {{ $k->kodeakun }} - {{ $k->korek->singkat ?? 'Detail' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div
                            class="w-full lg:w-auto flex flex-col sm:flex-row gap-2 bg-white p-1.5 rounded-2xl border border-gray-200 shadow-sm">
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                class="border-none focus:ring-0 text-xs font-bold text-gray-600 bg-transparent py-1 w-full sm:w-32">
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                class="border-none focus:ring-0 text-xs font-bold text-gray-600 bg-transparent py-1 w-full sm:w-32">
                        </div>

                        <button type="submit"
                            class="w-full lg:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg active:scale-95 transition-all">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 relative z-10 print:border-none print:shadow-none">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 font-bold text-gray-500 uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">Tgl</th>
                            <th class="px-6 py-4 text-left">Bukti</th>
                            <th class="px-6 py-4 text-left">Uraian</th>
                            <th class="px-6 py-4 text-left">Spek</th>
                            <th class="px-6 py-4 text-center">Vol</th>
                            <th class="px-6 py-4 text-right">Harga</th>
                            <th class="px-6 py-4 text-right bg-emerald-50 print:bg-gray-50">Total Bruto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold">{{
                                    \Carbon\Carbon::parse($item->belanja->tanggal)->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[10px] text-gray-400 uppercase font-mono">{{ $item->belanja->no_bukti }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-black text-gray-800 uppercase text-xs">{{ $item->namakomponen }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[10px] text-gray-400 uppercase font-mono">{{ $item->spek }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 px-2 py-1 rounded-md font-bold text-xs">{{ $item->volume
                                    }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td
                                class="px-6 py-4 text-right font-black text-emerald-700 bg-emerald-50/30 print:bg-transparent">
                                {{ number_format($item->total_bruto, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 italic uppercase text-xs">Data tidak
                                ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-emerald-900 text-slate-600 font-bold uppercase tracking-widest print:bg-gray-800">
                        <tr>
                            <td colspan="6" class="px-6 py-5 text-right text-xs uppercase">Total Akumulasi
                            </td>
                            <td
                                class="px-6 py-5 text-right text-base font-black border-l border-emerald-800 print:border-gray-600">
                                {{ number_format($totalNilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="p-4 border-t border-gray-100 no-print">
                    {{ $items->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect("#select-korek", {
            plugins: ['remove_button'],
            placeholder: "Semua Rekening",
            maxOptions: 100,
            dropdownParent: 'body'
        });
    </script>
</x-app-layout>