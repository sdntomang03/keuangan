<table>
    <thead>
        <tr>
            <th style="font-weight: bold; font-size: 14px;">LAPORAN REALISASI {{ strtoupper($anggaran->singkatan) }}
            </th>
            @for($i=0; $i<11; $i++) <th>
                </th> @endfor
        </tr>
        <tr>
            <th style="font-weight: bold; font-size: 12px;">TAHUN ANGGARAN {{ $anggaran->tahun }} - {{
                strtoupper($sekolah->nama_sekolah) }}</th>
            @for($i=0; $i<11; $i++) <th>
                </th> @endfor
        </tr>
        <tr>
            <th style="font-weight: bold;">PERIODE: {{ strtoupper($periodeText) }}</th>
            @for($i=0; $i<11; $i++) <th>
                </th> @endfor
        </tr>

        <tr>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Nama Komponen</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Spesifikasi</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Koefisien</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Kode Rekening</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Harga Satuan</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Vol Anggaran</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Vol Realisasi</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Satuan</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Pagu (A)</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Realisasi (B)</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">Sisa</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #f3f4f6;">%</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dataRkas as $idbl => $perKeterangan)
        @php
        $namaKegiatan = $perKeterangan->first()->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi';
        @endphp

        <tr>
            <td style="border: 1px solid #000; font-weight: bold; background-color: #c7d2fe; font-size: 12px;">
                KEGIATAN: {{ strtoupper($namaKegiatan) }}
            </td>
            @for($i=0; $i<11; $i++) <td style="border: 1px solid #000; background-color: #c7d2fe;">
                </td>
                @endfor
        </tr>

        @foreach($perKeterangan as $keterangan => $items)
        <tr>
            <td style="border: 1px solid #000; font-weight: bold; background-color: #e0e7ff; font-style: italic;">
                &nbsp;&nbsp;&nbsp; Keterangan: {{ $keterangan ?: 'Tanpa Keterangan' }}
            </td>
            @for($i=0; $i<11; $i++) <td style="border: 1px solid #000; background-color: #e0e7ff;">
                </td>
                @endfor
        </tr>

        @foreach($items as $item)
        @php
        $anggaranVal = $item->total_anggaran ?? 0;
        $realisasiVal = $item->total_realisasi ?? 0;
        $sisaVal = $anggaranVal - $realisasiVal;
        $volAnggaran = $item->total_volume_anggaran ?? 0;
        $volRealisasi = $item->volume_realisasi ?? 0;
        $persen = $anggaranVal > 0 ? ($realisasiVal / $anggaranVal) * 100 : 0;
        @endphp
        <tr>
            <td style="border: 1px solid #000; vertical-align: top;">{{ $item->namakomponen }}</td>
            <td style="border: 1px solid #000; vertical-align: top;">{{ $item->spek }}</td>
            <td style="border: 1px solid #000; vertical-align: top;">{{ $item->koefisien }}</td>
            <td style="border: 1px solid #000; vertical-align: top; mso-number-format:'\@';">{{ $item->korek->singkat ??
                '-' }}</td>
            <td style="border: 1px solid #000; vertical-align: top;" data-format="#,##0">{{ $item->hargasatuan }}</td>
            <td style="border: 1px solid #000; vertical-align: top; text-align: center;">{{ $volAnggaran }}</td>
            <td style="border: 1px solid #000; vertical-align: top; text-align: center;">{{ $volRealisasi }}</td>
            <td style="border: 1px solid #000; vertical-align: top; text-align: center;">{{ $item->satuan }}</td>
            <td style="border: 1px solid #000; vertical-align: top;" data-format="#,##0">{{ $anggaranVal }}</td>
            <td style="border: 1px solid #000; vertical-align: top;" data-format="#,##0">{{ $realisasiVal }}</td>
            <td style="border: 1px solid #000; vertical-align: top;" data-format="#,##0">{{ $sisaVal }}</td>
            <td style="border: 1px solid #000; vertical-align: top; text-align: center;">{{ number_format($persen, 2)
                }}%</td>
        </tr>
        @endforeach
        @endforeach
        @endforeach
    </tbody>
</table>