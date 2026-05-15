<body>
    @php
    // 1. Deteksi perbaikan
    $isPerbaikan = str_contains(strtolower($belanja->uraian), 'perbaikan') ||
    str_contains(strtolower($belanja->uraian), 'pemeliharaan');

    // 2. Mengurutkan foto (Sebelum -> Proses -> Setelah)
    $bobotStatus = ['sebelum' => 1, 'proses' => 2, 'setelah' => 3, 'umum' => 4];

    $allFotos = $belanja->fotos->sortBy(function($foto) use ($bobotStatus) {
    $status = strtolower($foto->status ?? 'umum');
    return $bobotStatus[$status] ?? 5;
    })->values();

    $totalFotos = $allFotos->count();

    // 3. Pisahkan Foto Pertama dan Sisanya
    $fotoPertama = $allFotos->first();
    $fotoSisaChunks = $allFotos->slice(1)->chunk(2);

    // 4. Helper untuk Judul TABEL (Standar tanpa teks Lanjutan)
    function getJudulTabel($status, $isPerbaikan) {
    $status = strtolower($status ?? 'umum');

    if (!$isPerbaikan) return "FOTO PEKERJAAN/BARANG";

    if ($status == 'sebelum') return "FOTO SEBELUM PERBAIKAN";
    if ($status == 'proses') return "FOTO PROSES PERBAIKAN";
    if ($status == 'setelah') return "FOTO SETELAH PERBAIKAN";

    return "DOKUMENTASI PERBAIKAN";
    }
    @endphp

    {{-- ========================================================== --}}
    {{-- HALAMAN 1: KOP, INFO, & FOTO PERTAMA --}}
    {{-- ========================================================== --}}
    <div class="halaman">
        <x-kop :sekolah="$sekolah" />

        <div class="judul-dokumen">DOKUMENTASI {{ $isPerbaikan ? 'PERBAIKAN' : 'BARANG/PEKERJAAN' }}</div>

        <table class="table-info">
            <tr>
                <td class="label-col">Nama Sekolah</td>
                <td class="sep-col">:</td>
                <td>{{ $sekolah->nama_sekolah }}</td>
            </tr>
            <tr>
                <td>Jenis Anggaran</td>
                <td>:</td>
                <td>{{ $belanja->anggaran->nama_anggaran ?? 'BOSP' }} Tahun {{ $belanja->anggaran->tahun }}</td>
            </tr>
            <tr>
                <td>Kegiatan Belanja</td>
                <td>:</td>
                <td>{{ $belanja->uraian }}</td>
            </tr>
            <tr>
                <td>Triwulan / Tahun</td>
                <td>:</td>
                <td>{{ $triwulan }} / {{ $tahun }}</td>
            </tr>
        </table>
        <br>

        {{-- Tabel Foto Pertama --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; page-break-inside: avoid;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    {{ getJudulTabel($fotoPertama->status, $isPerbaikan) }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 10px; vertical-align: middle;">
                    @php
                    $path1 = storage_path('app/public/' . $fotoPertama->path);
                    $src1 = file_exists($path1) ? $path1 : public_path('images/no-image.jpg');
                    @endphp
                    <img src="{{ $src1 }}"
                        style="max-width: 100%; height: auto; max-height: 350px; object-fit: contain;">
                    @if($fotoPertama->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{ $fotoPertama->keterangan }}
                    </p>
                    @endif
                </td>
            </tr>
        </table>

        @if($totalFotos == 1)
        <div class="ttd-container" style="margin-top: 20px; page-break-inside: avoid;">
            <table style="width: 100%;">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" class="text-center">
                        Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                        Kepala {{ $sekolah->nama_sekolah }},
                        <br><br><br><br><br>
                        <b><u>{{ $sekolah->nama_kepala_sekolah }}</u></b><br>
                        NIP. {{ $sekolah->nip_kepala_sekolah }}
                    </td>
                </tr>
            </table>
        </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- HALAMAN 2 DST: SISANYA (GROUPING BERDASARKAN STATUS) --}}
    {{-- ========================================================== --}}
    @if($totalFotos > 1)
    <div class="page-break"></div>

    @foreach($fotoSisaChunks as $chunk)
    <div class="halaman">
        <div style="height: 10px;"></div>

        @php
        // KUNCI UTAMA: Kelompokkan max 2 foto di halaman ini berdasarkan statusnya
        $groupedByStatus = $chunk->groupBy(function($item) {
        return strtolower($item->status ?? 'umum');
        });
        @endphp

        {{-- Buka tag table HANYA 1 KALI di luar looping status --}}
        <table
            style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid;">

            @foreach($groupedByStatus as $status => $fotosInGroup)

            {{-- Baris Judul Header --}}
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03; {{ !$loop->first ? 'border-top: 1px solid #000;' : '' }}">
                    {{ getJudulTabel($status, $isPerbaikan) }}
                </td>
            </tr>

            {{-- Looping baris foto --}}
            @foreach($fotosInGroup as $fotoSisa)
            <tr>
                <td style="text-align: center; padding: 10px; vertical-align: middle;
                        {{-- Jika bukan foto terakhir di status ini, pakai garis putus-putus --}}
                        @if(!$loop->last) border-bottom: 1px dashed #888;
                        {{-- Jika foto terakhir di status ini, TAPI masih ada status lain di bawahnya, pakai garis solid hitam --}}
                        @elseif(!$loop->parent->last) border-bottom: 1px solid #000;
                        @endif">

                    @php
                    $pathSisa = storage_path('app/public/' . $fotoSisa->path);
                    $srcSisa = file_exists($pathSisa) ? $pathSisa : public_path('images/no-image.jpg');
                    @endphp

                    <img src="{{ $srcSisa }}"
                        style="max-width: 100%; height: auto; max-height: 270px; object-fit: contain;">

                    @if($fotoSisa->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{ $fotoSisa->keterangan }}
                    </p>
                    @endif
                </td>
            </tr>
            @endforeach

            @endforeach

        </table>
        {{-- Tutup tag table --}}

        {{-- TTD di halaman paling terakhir --}}
        @if($loop->last)
        <div class="ttd-container" style="margin-top: 15px; page-break-inside: avoid;">
            <table style="width: 100%;">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" class="text-center">
                        Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                        Kepala {{ $sekolah->nama_sekolah }},
                        <br><br><br><br><br>
                        <b><u>{{ $sekolah->nama_kepala_sekolah }}</u></b><br>
                        NIP. {{ $sekolah->nip_kepala_sekolah }}
                    </td>
                </tr>
            </table>
        </div>
        @endif
    </div>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
    @endif
</body>