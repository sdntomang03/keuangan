<table>
    <thead>
        {{-- HEADER INFO REKANAN --}}
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold;">
                RIWAYAT TRANSAKSI - {{ strtoupper($rekanan->nama_rekanan) }}
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
                NPWP: {{ $rekanan->npwp ?? '-' }} | Bank: {{ $rekanan->nama_bank ?? '-' }} ({{ $rekanan->no_rekening ??
                '-' }})
            </td>
        </tr>
        <tr></tr>

        {{-- HEADER TABEL --}}
        <tr>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">No
            </th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">
                Tanggal</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">No
                Bukti</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">Uraian
                Belanja</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">Kode
                Rekening</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">Volume
            </th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #f0f0f0;">Total
                (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; $no = 1; @endphp

        @foreach($dataBelanja as $belanja)
        {{-- Loop Rincian Barang --}}
        @foreach($belanja->rincis as $index => $rinci)
        <tr>
            {{-- Penomoran & Info Nota (Hanya di baris pertama item) --}}
            <td style="border: 1px solid #000; text-align: center; vertical-align: top;">
                {{ $index === 0 ? $no++ : '' }}
            </td>
            <td style="border: 1px solid #000; text-align: center; vertical-align: top;">
                {{ $index === 0 ? \Carbon\Carbon::parse($belanja->tanggal)->format('d/m/Y') : '' }}
            </td>
            <td style="border: 1px solid #000; text-align: center; vertical-align: top;">
                {{ $index === 0 ? $belanja->no_bukti : '' }}
            </td>

            {{-- Detail Barang --}}
            <td style="border: 1px solid #000; vertical-align: top;">
                {{ $rinci->uraian }}
            </td>
            <td style="border: 1px solid #000; text-align: center; vertical-align: top;">
                {{ $rinci->rkas->korek->kode_rekening ?? '-' }}
            </td>
            <td style="border: 1px solid #000; text-align: center; vertical-align: top;">
                {{ $rinci->volume }} {{ $rinci->satuan }}
            </td>
            <td style="border: 1px solid #000; text-align: right; vertical-align: top;">
                {{ number_format($rinci->total_bruto, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach

        {{-- Baris PPN (Jika Ada) --}}
        @if($belanja->ppn > 0)
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000; font-style: italic;">PPN (Pajak Pertambahan Nilai)</td>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($belanja->ppn, 0, ',', '.') }}</td>
        </tr>
        @endif

        {{-- Hitung Total --}}
        @php $grandTotal += ($belanja->subtotal + $belanja->ppn); @endphp

        {{-- Pemisah Antar Nota --}}
        <tr>
            <td colspan="7" style="border-top: 2px solid #000;"></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="border: 1px solid #000; font-weight: bold; text-align: right;">TOTAL KESELURUHAN</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: right;">Rp {{ number_format($grandTotal,
                0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>