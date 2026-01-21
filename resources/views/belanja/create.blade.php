<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen" x-data="belanjaForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div
                class="mb-6 p-5 rounded-2xl shadow-lg text-white flex justify-between items-center
    {{ $anggaran->singkatan == 'BOS' ? 'bg-gradient-to-r from-indigo-600 to-blue-500' : 'bg-gradient-to-r from-emerald-600 to-teal-500' }}">

                <div class="flex items-center gap-4">
                    <div
                        class="p-3 bg-white/20 rounded-xl text-center min-w-[60px] backdrop-blur-sm border border-white/30">
                        <p class="text-[10px] font-bold uppercase leading-none">TW</p>
                        <p class="text-2xl font-black leading-none mt-1">{{ $sekolah->triwulan_aktif }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase font-bold opacity-80 tracking-widest">Input BKU Belanja</p>
                        <h2 class="text-2xl font-black uppercase tracking-tight">
                            {{ $anggaran->singkatan }} — TAHUN {{ $anggaran->tahun }}
                        </h2>
                    </div>
                </div>

                <div class="text-right border-l border-white/20 pl-6 hidden md:block">
                    <p class="text-sm font-bold">{{ $sekolah->nama_sekolah }}</p>
                    <p class="text-[10px] opacity-75 italic tracking-wide uppercase">NPSN: {{ $sekolah->npsn }}</p>
                    <p class="text-xs opacity-90 mt-1">Bendahara: {{ $sekolah->nama_bendahara }}</p>
                </div>
            </div>

            <form action="{{ route('belanja.store') }}" method="POST" class="space-y-6">
                @csrf

                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detail Nota</label>
                        <input type="date" name="tanggal" class="w-full rounded-xl border-gray-200 focus:ring-blue-500"
                            required>
                        <input type="text" name="no_bukti" placeholder="Nomor Bukti"
                            class="w-full rounded-xl border-gray-200" required>
                    </div>
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedName: '',
                        rekanans: {{ json_encode($rekanans) }},
                        get filteredRekans() {
                            return this.rekanans.filter(i => i.nama_rekanan.toLowerCase().includes(this.search.toLowerCase()))
                        }
                    }" class="relative">

                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Penerima / Toko</label>

                        <div @click="open = !open"
                            class="cursor-pointer border border-gray-200 rounded-xl p-2 mt-4 bg-white text-sm">
                            <span x-text="selectedName ? selectedName : 'Pilih Rekanan...'"></span>
                            <input type="hidden" name="rekanan_id" :value="selectedId">
                        </div>

                        <div x-show="open" @click.away="open = false"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2">
                            <input type="text" x-model="search" placeholder="Cari..."
                                class="w-full mb-2 border-gray-100 rounded-lg text-sm focus:ring-indigo-500">
                            <template x-for="r in filteredRekans" :key="r.id">
                                <div @click="selectedId = r.id; selectedName = r.nama_rekanan; open = false; search = ''"
                                    class="p-2 hover:bg-indigo-50 cursor-pointer rounded-lg text-sm">
                                    <span x-text="r.nama_rekanan"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="space-y-4 border-l border-gray-100 md:pl-4">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Uraian Transaksi</label>
                        <textarea name="uraian" rows="3"
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500" required></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 bg-gray-50/80 border-b border-gray-200 flex flex-col md:flex-row gap-6">
                        <div class="flex-1">
                            <label class="text-[11px] font-bold text-gray-500 uppercase block mb-2 tracking-widest">1.
                                Pilih Kegiatan</label>
                            <select x-model="selectedIdbl" @change="fetchRekening()"
                                class="w-full rounded-xl border-gray-200">
                                <option value="">-- Cari Kegiatan --</option>
                                @foreach ($listKegiatan as $k)
                                <option value="{{ $k->idbl }}">{{ $k->namagiat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="text-[11px] font-bold text-gray-500 uppercase block mb-2 tracking-widest">2.
                                Pilih Kode Akun</label>
                            <select x-model="selectedRekening" @change="fetchKomponen()" :disabled="!rekenings.length"
                                class="w-full rounded-xl border-gray-200">
                                <option value="">-- Pilih Rekening --</option>
                                <template x-for="rek in rekenings" :key="rek.koderekening">
                                    <option :value="rek.koderekening"
                                        x-text="rek.koderekening + ' - ' + rek.namarekening"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-800 text-white uppercase text-[10px] tracking-widest">
                                <tr>
                                    <th class="px-6 py-4 text-left">Komponen RKAS</th>
                                    <th class="px-6 py-4 text-center w-32">Volume</th>

                                    <th class="px-6 py-4 text-right w-44">
                                        <div class="flex items-center justify-end gap-2">
                                            <span>Harga Satuan</span>
                                            <button type="button" @click="roundAllPricesDown()"
                                                x-show="items.length > 0" title="Bulatkan semua harga"
                                                class="bg-red-500 hover:bg-red-400 text-white p-1 rounded transition-all shadow-sm active:rotate-180 duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-right w-40">Subtotal</th>
                                    <th class="px-6 py-4 text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-blue-50/30 transition">
                                        <td class="px-6 py-4">
                                            <input type="text" :name="'items[' + index + '][namakomponen]'"
                                                x-model="item.namakomponen"
                                                class="w-full border-gray-200 rounded-lg text-sm font-bold p-1.5 mb-1">
                                            <input type="text" :name="'items[' + index + '][spek]'" x-model="item.spek"
                                                class="w-full border-gray-200 rounded-lg text-[11px] italic text-blue-600 p-1.5">
                                            <input type="hidden" :name="'items[' + index + '][idblrinci]'"
                                                x-model="item.idblrinci">
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="number" step="any" :name="'items[' + index + '][volume]'"
                                                x-model="item.volume" @input="validateVolume(item); calculateTotal()"
                                                class="w-20 border-gray-200 rounded-lg text-center font-bold text-blue-700 p-1">

                                            <div class="mt-1 flex flex-col items-center">
                                                <span
                                                    class="text-[10px] font-black text-gray-500 uppercase tracking-tighter"
                                                    x-text="item.satuan"></span>

                                                <p class="text-[8px] text-gray-400 uppercase tracking-tighter">
                                                    Maks: <span x-text="item.max_volume" class="font-bold"></span>
                                                </p>
                                            </div>

                                            <input type="hidden" :name="'items[' + index + '][satuan]'"
                                                :value="item.satuan">
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="relative flex items-center">
                                                <span class="absolute left-2 text-gray-400 text-xs">Rp</span>

                                                <div class="relative">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 text-xs">Rp</span>
                                                    </div>
                                                    <input type="number" step="any"
                                                        :name="'items[' + index + '][harga_satuan]'"
                                                        x-model="item.harga_satuan" @input="calculateTotal()"
                                                        class="w-full pl-8 pr-3 py-1.5 border-gray-200 rounded-lg text-right text-sm font-bold focus:ring-blue-500 shadow-sm">
                                                </div>

                                                <button type="button" @click="roundDownThousand(item)"
                                                    x-show="item.harga_satuan >= 1000" title="Bulatkan ke ribuan bawah"
                                                    class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-all duration-300 active:rotate-180">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="mt-1 px-1 flex justify-between items-center">
                                                <span class="text-[9px] text-gray-400 uppercase font-bold">Pagu
                                                    ARKAS:</span>
                                                <span class="text-[10px] text-gray-500 italic font-medium"
                                                    x-text="formatRupiah(item.harga_asli)">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900"
                                            x-text="formatRupiah(item.volume * item.harga_satuan)"></td>
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" @click="removeItem(index)"
                                                class="text-red-300 hover:text-red-600">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 self-start">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pengaturan Pajak</h3>
                            <button type="button" @click="addPajak()"
                                class="text-[10px] bg-orange-100 text-orange-600 px-4 py-1.5 rounded-full font-bold hover:bg-orange-200">+
                                TAMBAH PAJAK</button>
                        </div>

                        <template x-for="(pajak, pIndex) in pajaks" :key="pIndex">
                            <div class="flex gap-3 mb-4 items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <input type="hidden" :name="'pajaks[' + pIndex + '][id_master]'"
                                    :value="pajak.id_master">
                                <input type="hidden" :name="'pajaks[' + pIndex + '][nominal]'" :value="pajak.nominal">

                                <select x-model="pajak.id_master" @change="calculateTotal()"
                                    class="flex-1 rounded-xl border-gray-200 text-sm">
                                    <option value="">-- Pilih Jenis Pajak --</option>
                                    <template x-for="master in masterPajaks" :key="master.id">
                                        <option :value="master.id"
                                            x-text="master.nama_pajak + ' (' + master.persen + '%)'"></option>
                                    </template>
                                </select>

                                <div class="w-32 text-sm font-bold text-orange-700 text-right"
                                    x-text="formatRupiah(pajak.nominal)"></div>

                                <button type="button" @click="removePajak(pIndex)"
                                    class="text-red-400 font-bold px-2">&times;</button>
                            </div>
                        </template>
                        <p class="text-[10px] text-gray-400 italic mt-4">* Pajak berjenis 'Penambah' (seperti PPN) akan
                            menaikkan nilai Bruto BKU.</p>
                    </div>

                    <div class="bg-gray-900 p-8 rounded-3xl shadow-2xl text-white">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-gray-400 border-b border-gray-800 pb-2">
                                <span class="text-[10px] uppercase font-bold tracking-widest">Sub Total</span>
                                <span class="text-lg font-medium" x-text="formatRupiah(totalHargaBersih)"></span>
                            </div>

                            <div class="flex justify-between items-center text-blue-400">
                                <span class="text-[10px] uppercase font-bold tracking-widest">+ PPN</span>
                                <span class="text-lg" x-text="formatRupiah(totalPenambah)"></span>
                            </div>

                            <div
                                class="flex justify-between items-center bg-blue-600/20 p-4 rounded-2xl border border-blue-500/50 shadow-inner my-4">
                                <div>
                                    <span
                                        class="text-[10px] uppercase font-black tracking-[0.2em] block text-blue-300">Total</span>
                                    <span class="text-[9px] text-blue-200 opacity-70">Nilai realisasi</span>
                                </div>
                                <span class="text-3xl font-black text-blue-400"
                                    x-text="formatRupiah(totalBruto)"></span>
                            </div>

                            <div
                                class="flex justify-between items-center text-orange-400 border-b border-gray-800 pb-4">
                                <div class="flex flex-col">
                                    <span class="text-xs uppercase font-bold tracking-widest">Total PPH</span>
                                    <span class="text-[10px] text-gray-500 italic">Potongan Pajak Pengurang</span>
                                </div>
                                <span class="text-xl font-bold" x-text="'- ' + formatRupiah(totalPajak)"></span>
                            </div>

                            <div class="pt-4 flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] text-emerald-400 uppercase font-bold tracking-[0.2em] mb-1">
                                        Diterima Rekanan (Netto Transfer)</p>
                                    <h3 class="text-4xl font-black text-emerald-400"
                                        x-text="formatRupiah(totalTransfer)"></h3>
                                </div>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-2xl font-black transition-all transform hover:scale-105 shadow-xl uppercase tracking-tighter">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_total" :value="totalHargaBersih">
                <input type="hidden" name="ppn" :value="totalPenambah">
                <input type="hidden" name="pph" :value="totalPajak">
                <input type="hidden" name="transfer" :value="totalTransfer">
                <input type="hidden" name="idbl" x-model="selectedIdbl">
                <input type="hidden" name="kodeakun" x-model="selectedRekening">
            </form>
        </div>
    </div>

    <script>
        function belanjaForm() {
            return {
                selectedIdbl: '',
                selectedRekening: '',
                rekenings: [],
                items: [],
                pajaks: [],

                totalHargaBersih: 0,
                totalPenambah: 0,
                totalBruto: 0,
                totalPajak: 0,
                totalTransfer: 0,

                masterPajaks: @json($listPajak),

                async fetchRekening() {
                    if (!this.selectedIdbl) return;
                    const res = await fetch(`/api/get-rekening?idbl=${this.selectedIdbl}`);
                    this.rekenings = await res.json();
                    this.selectedRekening = '';
                    this.items = [];
                    this.calculateTotal();
                },

                async fetchKomponen() {
                    if (!this.selectedRekening) return;
                    const res = await fetch(
                        `/api/get-komponen?idbl=${this.selectedIdbl}&koderekening=${this.selectedRekening}`);
                    const data = await res.json();
                    this.items = data.map(komp => ({
                        rkas_id: komp.id,
                        idblrinci: komp.idblrinci,
                        namakomponen: komp.namakomponen,
                        spek: komp.spek || '-',
                        volume: komp.volume_bulan,
                        satuan: komp.satuan,
                        max_volume: komp.volume_bulan,
                        harga_satuan: komp.hargasatuan,
                        harga_asli: komp.hargasatuan
                    }));
                    this.calculateTotal();
                },

                addPajak() {
                    this.pajaks.push({
                        id_master: '',
                        nominal: 0
                    });
                },

                removePajak(index) {
                    this.pajaks.splice(index, 1);
                    this.calculateTotal();
                },

                validateVolume(item) {
                    if (parseFloat(item.volume) > parseFloat(item.max_volume)) {
                        alert(`Maksimal volume adalah ${item.max_volume}`);
                        item.volume = item.max_volume;
                    }
                },

                roundAllPricesDown() {
                    if (confirm('Bulatkan semua harga satuan sesuai ketentuan (Ribuan/Puluh Ribu/Ratus Ribu)?')) {
                        this.items.forEach(item => {
                            if (item.harga_satuan) {
                                let harga = parseFloat(item.harga_satuan);

                                if (harga < 100000) {
                                    // Di bawah 100.000 -> Bulatkan ke 1.000 bawah
                                    item.harga_satuan = Math.floor(harga / 1000) * 1000;
                                } else if (harga >= 100000 && harga <= 5000000) {
                                    // Sampai 5.000.000 -> Bulatkan ke 10.000 bawah
                                    item.harga_satuan = Math.floor(harga / 10000) * 10000;
                                } else if (harga > 5000000) {
                                    // Di atas 5.000.000 -> Bulatkan ke 100.000 bawah
                                    item.harga_satuan = Math.floor(harga / 50000) * 50000;
                                }
                            }
                        });
                        this.calculateTotal();
                    }
                },
                roundDownThousand(item) {
                    if (item.harga_satuan) {
                        let harga = parseFloat(item.harga_satuan);

                        if (harga < 100000) {
                            item.harga_satuan = Math.floor(harga / 1000) * 1000;
                        } else if (harga <= 5000000) {
                            item.harga_satuan = Math.floor(harga / 10000) * 10000;
                        } else {
                            item.harga_satuan = Math.floor(harga / 50000) * 50000;
                        }

                        this.calculateTotal();
                    }
                },

                removeItem(index) {

                        this.items.splice(index, 1);
                        this.calculateTotal();

                },

                calculateTotal() {
                    // 1. Hitung Dasar Harga Bersih (Total item belanja)
                    this.totalHargaBersih = this.items.reduce((sum, item) =>
                        sum + (parseFloat(item.volume || 0) * parseFloat(item.harga_satuan || 0)), 0);

                    let penambah = 0;
                    let potonganPPH = 0; // Inisialisasi khusus untuk PPH/Pengurang

                    // 2. Hitung Pajak berdasarkan DasarPajak
                    this.pajaks.forEach(p => {
                        const master = this.masterPajaks.find(m => m.id == p.id_master);
                        if (master) {
                            // Hitung nominal pajak: Dasar x Persen
                            p.nominal = Math.round(this.totalHargaBersih * (parseFloat(master.persen) / 100));

                            // Logika Pemisahan:
                            if (master.jenis === 'penambah') {
                                // Biasanya PPN: Menambah total tagihan (Bruto)
                                penambah += p.nominal;
                            } else if (master.jenis === 'pengurang') {
                                // Biasanya PPH: Mengurangi pembayaran ke toko/penerima
                                potonganPPH += p.nominal;
                            }
                        }
                    });

                    // 3. Set nilai ke variabel state
                    this.totalPenambah = penambah;

                    // Total Bruto: Harga Bersih + PPN (Nilai Kwitansi)
                    this.totalBruto = this.totalHargaBersih + this.totalPenambah;

                    // Total Pajak: Sekarang hanya berisi akumulasi pajak pengurang (PPH)
                    this.totalPajak = potonganPPH;

                    // Total Transfer (Netto): Uang yang benar-benar dibayarkan
                    this.totalTransfer = this.totalHargaBersih - this.totalPajak;
                },

                formatRupiah(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(val || 0);
                }
            }
        }
    </script>
</x-app-layout>