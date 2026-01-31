<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen" x-data="belanjaForm()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm relative">

                <div class="flex justify-between items-start">
                    {{-- KIRI: Icon & Pesan Error --}}
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">
                                Terdapat Kesalahan Input
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: Tombol Close --}}
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="show = false" type="button"
                            class="bg-red-50 rounded-md inline-flex text-red-500 hover:text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endif
            <div
                class="mb-6 p-5 rounded-2xl shadow-lg text-white flex justify-between items-center
                {{ $anggaran->singkatan == 'bos' ? 'bg-gradient-to-r from-indigo-600 to-blue-500' : 'bg-gradient-to-r from-emerald-600 to-teal-500' }}">
                <div class="flex items-center gap-4">
                    <div
                        class="p-3 bg-white/20 rounded-xl text-center min-w-[60px] backdrop-blur-sm border border-white/30">
                        <p class="text-[10px] font-bold uppercase leading-none">TW</p>
                        <p class="text-2xl font-black leading-none mt-1">{{ $sekolah->triwulan_aktif }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold opacity-80 tracking-widest">Mode Koreksi Belanja</p>
                        <h2 class="text-2xl font-black uppercase tracking-tight">{{ $anggaran->singkatan }} — ID: #{{
                            $belanja->id }}</h2>
                    </div>
                </div>
                <div class="text-right border-l border-white/20 pl-6 hidden md:block">
                    <p class="text-sm font-bold">{{ $sekolah->nama_sekolah }}</p>
                    <p class="text-[10px] opacity-75 italic tracking-wide uppercase">NPSN: {{ $sekolah->npsn }}</p>
                </div>
            </div>

            <form action="{{ route('belanja.update', $belanja->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="space-y-4">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detail Nota</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $belanja->tanggal) }}"
                            class="w-full rounded-xl border-gray-200 focus:ring-blue-500" required>
                        <input type="text" name="no_bukti" value="{{ old('no_bukti', $belanja->no_bukti) }}"
                            placeholder="Nomor Bukti" class="w-full rounded-xl border-gray-200" required>
                    </div>

                    <div x-data="{
        open: false,
        search: '',
        selectedId: '{{ old('rekanan_id', $belanja->rekanan_id) }}',
        selectedName: '{{ $belanja->rekanan->nama_rekanan ?? 'Pilih Rekanan...' }}',
        rekanans: {{ json_encode($rekanans) }},
        get filteredRekans() {
            return this.rekanans.filter(i => i.nama_rekanan.toLowerCase().includes(this.search.toLowerCase()))
        }
    }" class="relative">

                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Penerima / Toko</label>

                        <div @click="open = !open"
                            class="cursor-pointer border border-gray-200 rounded-xl p-2 mt-4 bg-white text-sm flex justify-between items-center shadow-sm">
                            <span x-text="selectedName"></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <input type="hidden" name="rekanan_id" :value="selectedId">
                        </div>

                        <div x-show="open" @click.away="open = false"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto p-2"
                            style="display: none;"> <input type="text" x-model="search" placeholder="Cari..."
                                class="w-full mb-2 border-gray-100 rounded-lg text-sm focus:ring-indigo-500">
                            <template x-for="r in filteredRekans" :key="r.id">
                                <div @click="selectedId = r.id; selectedName = r.nama_rekanan; open = false; search = ''"
                                    class="p-2 hover:bg-indigo-50 cursor-pointer rounded-lg text-sm">
                                    <span x-text="r.nama_rekanan"></span>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4">

                            <input type="text" name="rincian" value="{{ old('rincian', $belanja->rincian) }}"
                                placeholder="Input Rincian (Opsional)"
                                class="w-full rounded-xl border-gray-200 mt-2 text-sm focus:ring-blue-500 placeholder-gray-400">
                        </div>

                    </div>

                    <div class="space-y-4 border-l border-gray-100 md:pl-4">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Uraian Transaksi</label>
                        <textarea name="uraian" rows="3"
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500"
                            required>{{ old('uraian', $belanja->uraian) }}</textarea>
                    </div>

                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-6">
                    <div class="flex-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase block mb-2 tracking-widest">1.
                            Kegiatan (Terkunci)</label>
                        <div
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-600">
                            {{ $kegiatan->namagiat }}</div>
                    </div>
                    <div class="flex-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase block mb-2 tracking-widest">2. Kode
                            Rekening (Terkunci)</label>
                        <div
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-600">
                            <span class="text-blue-600 font-bold mr-2">{{ $belanja->kode_rekening }}</span> {{
                            $belanja->korek->ket ?? '' }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4 bg-gray-800 text-white flex justify-between items-center">
                                <h3 class="text-xs font-bold uppercase tracking-widest">Rincian Komponen</h3>
                                <span class="text-[10px] bg-white/20 px-3 py-1 rounded-full italic"><button
                                        type="button" @click="roundAllPricesDown()"
                                        class="text-[10px] bg-amber-500 hover:bg-amber-400 text-white px-4 py-1.5 rounded-full font-black transition shadow-lg flex items-center gap-2 uppercase tracking-tighter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        Auto Bulatkan Harga
                                    </button></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="hover:bg-blue-50/30 transition">
                                                <td class="p-4">
                                                    <input type="hidden" :name="'items[' + index + '][idblrinci]'"
                                                        :value="item.idblrinci">

                                                    <input type="text" :name="'items[' + index + '][namakomponen]'"
                                                        x-model="item.namakomponen"
                                                        class="w-full border-none font-bold text-gray-700 bg-transparent p-0 mb-1">

                                                    <input type="text" :name="'items[' + index + '][spek]'"
                                                        x-model="item.spek"
                                                        class="w-full border-none text-[11px] italic text-blue-500 bg-transparent p-0">
                                                </td>
                                                <td class="p-4 w-24">
                                                    <input type="number" step="any"
                                                        :name="'items[' + index + '][volume]'" x-model="item.volume"
                                                        @input="calculateTotal()"
                                                        class="w-full border-gray-200 rounded-lg text-center font-bold text-blue-700 p-1">
                                                </td>
                                                <td class="p-4 w-24 text-center">
                                                    <div class="flex flex-col items-center justify-center gap-1">
                                                        <span
                                                            class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-black uppercase tracking-widest border border-gray-200 shadow-sm"
                                                            x-text="item.satuan">
                                                        </span>
                                                    </div>


                                                </td>
                                                <td class="p-4 w-44">
                                                    <div class="relative group">
                                                        <input type="number" step="any"
                                                            :name="'items[' + index + '][harga_satuan]'"
                                                            x-model="item.harga_satuan" @input="calculateTotal()"
                                                            class="w-full border-gray-200 rounded-lg text-right font-bold p-1 pr-8 focus:ring-amber-500 focus:border-amber-500 transition-all"
                                                            placeholder="0">

                                                        <button type="button" @click="roundItem(index)"
                                                            title="Bulatkan Harga"
                                                            class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all duration-200 active:rotate-180">
                                                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pengaturan Pajak
                                </h3>
                                <button type="button" @click="addPajak()"
                                    class="text-[10px] bg-orange-100 text-orange-600 px-4 py-1.5 rounded-full font-bold hover:bg-orange-200">+
                                    TAMBAH PAJAK</button>
                            </div>
                            <template x-for="(pajak, pIndex) in pajaks" :key="pIndex">
                                <div
                                    class="flex gap-3 mb-3 items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <select x-model="pajak.id_master" @change="calculateTotal()"
                                        :name="'pajaks[' + pIndex + '][id_master]'"
                                        class="flex-1 rounded-xl border-gray-200 text-sm">
                                        <option value="">-- Pilih Jenis Pajak --</option>
                                        <template x-for="master in masterPajaks" :key="master.id">
                                            <option :value="master.id"
                                                x-text="master.nama_pajak + ' (' + master.persen + '%)'"></option>
                                        </template>
                                    </select>
                                    <div class="w-32 text-sm font-bold text-orange-700 text-right"
                                        x-text="formatRupiah(pajak.nominal)"></div>
                                    <input type="hidden" :name="'pajaks[' + pIndex + '][nominal]'"
                                        :value="pajak.nominal">
                                    <button type="button" @click="removePajak(pIndex)"
                                        class="text-red-400 font-bold px-2 hover:bg-red-100 rounded-lg transition">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 p-8 rounded-3xl shadow-2xl text-white sticky top-6">
                            <h3 class="text-center text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-8">
                                Ringkasan Pembayaran</h3>

                            <div class="space-y-5">
                                <div class="flex justify-between items-center text-gray-400">
                                    <span class="text-[10px] uppercase font-bold tracking-widest">Sub Total</span>
                                    <span class="text-lg font-medium" x-text="formatRupiah(totalHargaBersih)"></span>
                                </div>

                                <div class="flex justify-between items-center text-blue-400">
                                    <span class="text-[10px] uppercase font-bold tracking-widest">+ PPN</span>
                                    <span class="text-lg" x-text="formatRupiah(totalPenambah)"></span>
                                </div>

                                <div class="py-4 border-y border-gray-800">
                                    <span
                                        class="text-[10px] uppercase font-black tracking-widest block text-blue-300 mb-2">Total
                                        Bruto</span>
                                    <span class="text-4xl font-black text-blue-400 block"
                                        x-text="formatRupiah(totalBruto)"></span>
                                </div>

                                <div class="flex justify-between items-center text-orange-400">
                                    <span class="text-[10px] uppercase font-bold tracking-widest">Total PPH</span>
                                    <span class="text-xl font-bold" x-text="'- ' + formatRupiah(totalPajak)"></span>
                                </div>

                                <div class="pt-6 border-t border-gray-800">
                                    <p class="text-[10px] text-emerald-400 uppercase font-bold tracking-widest mb-1">
                                        Diterima Rekanan (Netto)</p>
                                    <h3 class="text-4xl font-black text-emerald-400 mb-8"
                                        x-text="formatRupiah(totalTransfer)"></h3>

                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl font-black transition-all transform hover:scale-[1.02] shadow-xl uppercase tracking-widest">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_total" :value="totalHargaBersih">
                <input type="hidden" name="ppn" :value="totalPenambah">
                <input type="hidden" name="pph" :value="totalPajak">
                <input type="hidden" name="transfer" :value="totalTransfer">
            </form>
        </div>
    </div>

    <script>
        function belanjaForm() {
            return {
                items: @json($items),
                pajaks: @json($pajaks),
                masterPajaks: @json($listPajak),
                totalHargaBersih: 0,
                totalPenambah: 0,
                totalBruto: 0,
                totalPajak: 0,
                totalTransfer: 0,

                init() { this.calculateTotal(); },

                addPajak() { this.pajaks.push({ id_master: '', nominal: 0 }); },
                removePajak(index) { this.pajaks.splice(index, 1); this.calculateTotal(); },
                removeItem(index) { if(confirm('Hapus item?')) { this.items.splice(index, 1); this.calculateTotal(); } },

                calculateTotal() {
                    // 1. Subtotal dengan pembulatan Math.round per baris jika perlu
                    this.totalHargaBersih = Math.round(this.items.reduce((sum, item) =>
                        sum + (parseFloat(item.volume || 0) * parseFloat(item.harga_satuan || 0)), 0));

                    let penambah = 0;
                    let pengurang = 0;

                    // 2. Pajak Loop dengan Math.round (Pembulatan)
                    this.pajaks.forEach(p => {
                        const master = this.masterPajaks.find(m => m.id == p.id_master);
                        if (master) {
                            // Pembulatan nominal pajak ke angka bulat terdekat
                            p.nominal = Math.round(this.totalHargaBersih * (parseFloat(master.persen) / 100));
                            if (master.jenis === 'penambah') penambah += p.nominal;
                            else if (master.jenis === 'pengurang') pengurang += p.nominal;
                        } else { p.nominal = 0; }
                    });

                    this.totalPenambah = penambah;
                    this.totalBruto = this.totalHargaBersih + this.totalPenambah;
                    this.totalPajak = pengurang;
                    // Transfer Netto = Bruto - Semua Pajak (Atau Subtotal - PPH)
                    this.totalTransfer = this.totalBruto - (this.totalPenambah + this.totalPajak);
                },

                formatRupiah(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(val || 0);
                },

                            roundItem(index) {
    // Ambil item berdasarkan index yang dikirim dari tombol
    let item = this.items[index];

    if (item && item.harga_satuan) {
        let harga = parseFloat(item.harga_satuan);

        if (harga < 100000) {
            item.harga_satuan = Math.floor(harga / 1000) * 1000;
        } else if (harga <= 5000000) {
            item.harga_satuan = Math.floor(harga / 10000) * 10000;
        } else {
            item.harga_satuan = Math.floor(harga / 50000) * 50000;
        }

        // Trigger perubahan agar UI Alpine.js terupdate
        this.items[index].harga_satuan = item.harga_satuan;
        this.calculateTotal();
    }
},
                roundAllPricesDown() {
    if (confirm('Apakah anda yakin akan membulatkan semua harga?')) {
        this.items.forEach(item => {
            if (item.harga_satuan) {
                let harga = parseFloat(item.harga_satuan);

                if (harga < 100000) {
                    // Di bawah 100.000 -> Bulatkan ke 1.000 bawah
                    item.harga_satuan = Math.floor(harga / 1000) * 1000;
                }
                else if (harga >= 100000 && harga <= 5000000) {
                    // Sampai 5.000.000 -> Bulatkan ke 10.000 bawah
                    item.harga_satuan = Math.floor(harga / 10000) * 10000;
                }
                else if (harga > 5000000) {
                    // Di atas 5.000.000 -> Bulatkan ke 50.000 bawah (Sesuai kode Anda)
                    item.harga_satuan = Math.floor(harga / 50000) * 50000;
                }
            }
        });
        this.calculateTotal(); // Refresh total harga, pajak, dan transfer
    }
},
            }
        }
    </script>
</x-app-layout>