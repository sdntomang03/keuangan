<table>
    <thead>
        <tr>
            <th colspan="5" style="font-weight: bold; font-size: 14pt; text-align: center;">
                REKAPITULASI BELANJA REKANAN: {{ strtoupper($rekanan->nama_rekanan) }}
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($belanjas as $belanja)
        <tr>
            <td colspan="5"></td>
        </tr>

        <tr>
            <th colspan="5" style="background-color: #f3f4f6; border: 1px solid #000; font-weight: bold;">
                NO. BUKTI: {{ $belanja->no_bukti }} | TANGGAL: {{
                \Carbon\Carbon::parse($belanja->tanggal)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <td colspan="5" style="border: 1px solid #000;">
                <strong>Uraian:</strong> {{ $belanja->uraian }}
            </td>
        </tr>

        <tr style="background-color: #5adb03; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000; width: 50px;">No</th>
            <th style="border: 1px solid #000; width: 300px;">Uraian Barang/Jasa</th>
            <th style="border: 1px solid #000; width: 100px;">Volume</th>
            <th style="border: 1px solid #000; width: 150px;">Harga Satuan</th>
            <th style="border: 1px solid #000; width: 150px;">Total</th>
        </tr>

        @foreach($belanja->rincis as $index => $rinci)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $rinci->uraian }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $rinci->vol }} {{ $rinci->satuan }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($rinci->harga, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($rinci->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        @foreach($belanja->pajaks as $pajak)
        <tr>
            <td colspan="4" style="border: 1px solid #000; text-align: right; font-style: italic;">
                {{ $pajak->masterPajak->nama_pajak }}
            </td>
            <td style="border: 1px solid #000; text-align: right; color: red; font-weight: bold;">
                {{ number_format($pajak->nominal, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach

        <tr style="background-color: #e5e7eb; font-weight: bold;">
            <td colspan="4" style="border: 1px solid #000; text-align: right;">TOTAL PEMBAYARAN</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($belanja->subtotal + $belanja->ppn, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>