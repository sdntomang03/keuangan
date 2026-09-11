<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catatan' => 'required|string',
            'is_tl' => 'nullable|boolean',
            // Pastikan hanya menerima gambar maksimal 2MB
            'file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx,xls,xlsx|max:5120',
        ];
    }
}
