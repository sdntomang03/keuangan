<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BelanjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ini true
    }

    public function rules(): array
    {
        $belanjaId = $this->route('id');

        return [
            'tanggal' => 'required|date',
            'no_bukti' => [
                'required',
                'string',
                Rule::unique('belanjas', 'no_bukti')->ignore($belanjaId),
            ],
            'rekanan_id' => 'required',
            'uraian' => 'required|string', // Uraian wajib diisi
            'rincian' => 'nullable|string',

            // Kolom dari input hidden
            'idbl' => 'required',
            'kodeakun' => 'required',
            'sub_total' => 'nullable|numeric',
            'ppn' => 'nullable|numeric',
            'pph' => 'nullable|numeric',
            'transfer' => 'nullable|numeric',

            // Validasi Array Items
            'items' => 'required|array|min:1',
            'items.*.idblrinci' => 'required',
            'items.*.namakomponen' => 'required|string',
            'items.*.volume' => 'required|numeric|min:0',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.spek' => 'nullable|string',

            // Validasi Array Pajak
            'pajaks' => 'nullable|array',
            'pajaks.*.id_master' => 'nullable|integer',
            'pajaks.*.nominal' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'no_bukti.unique' => 'Nomor Bukti ini sudah digunakan pada transaksi lain. Mohon ganti.',
            'items.required' => 'Harap masukkan minimal satu rincian belanja.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'rekanan_id.required' => 'Penerima / Toko wajib dipilih.',
            'uraian.required' => 'Uraian transaksi wajib diisi.',
            'items.*.namakomponen.required' => 'Ada nama komponen/barang yang masih kosong.',
            'items.*.volume.required' => 'Volume barang harus diisi.',
            'items.*.harga_satuan.required' => 'Harga satuan barang harus diisi.',
        ];
    }
}
