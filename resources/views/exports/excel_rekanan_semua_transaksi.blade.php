<table>
    <thead>
        <tr>
            <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: center;">
                REKAPITULASI TRANSAKSI URK: {{ strtoupper($rekanan->nama_rekanan) }}
            </th>
        </tr>
    </thead>
    <tbody>
        {{-- Looping semua transaksi URK milik rekanan ini --}}
        @foreach($belanjas as $belanja)

        {{-- Jarak pemisah antar transaksi URK --}}
        @if(!$loop->first)
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
        @endif

        {{-- Header Bukti Transaksi --}}
        <tr>
            <th colspan="5" style="background-color: #f3f4f6; border: 1px solid #000; font-weight: bold;">
                BUKTI: {{ $belanja->no_bukti }} | TANGGAL: {{ \Carbon\Carbon::parse($belanja->tanggal)->format('d/m/Y')
                }}
            </th>
        </tr>
        <tr>
            <td colspan="5" style="border: 1px solid #000; font-style: italic;">
                Pekerjaan: {{ $belanja->uraian }}
            </td>
        </tr>

        {{-- Kolom Tabel Rincian --}}
        <tr style="background-color: #5adb03; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000; width: 50px;">No</th>
            <th style="border: 1px solid #000; width: 350px;">Uraian Barang/Jasa</th>
            <th style="border: 1px solid #000; width: 100px;">Volume</th>
            <th style="border: 1px solid #000; width: 150px;">Harga Satuan</th>
            <th style="border: 1px solid #000; width: 150px;">Jumlah</th>
        </tr>

        {{-- Looping Rincian Belanja --}}
        @foreach($belanja->rincis as $index => $rinci)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $rinci->uraian }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $rinci->vol }} {{ $rinci->satuan }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $rinci->harga }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $rinci->total }}</td>
        </tr>
        @endforeach

        {{-- Looping Pajak (Jika Ada) --}}
        @foreach($belanja->pajaks as $pajak)
        <tr>
            <td colspan="4" style="border: 1px solid #000; text-align: right; font-style: italic;">
                {{ $pajak->masterPajak->nama_pajak ?? 'Pajak' }}
            </td>
            <td style="border: 1px solid #000; text-align: right; color: red;">
                {{ $pajak->nominal }}
            </td>
        </tr>
        @endforeach

        {{-- Grand Total Transaksi Ini --}}
        <tr style="font-weight: bold; background-color: #e5e7eb;">
            <td colspan="4" style="border: 1px solid #000; text-align: right;">TOTAL DIBAYARKAN</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ $belanja->subtotal + $belanja->ppn }}
            </td>
        </tr>

        @endforeach
    </tbody>
</table>