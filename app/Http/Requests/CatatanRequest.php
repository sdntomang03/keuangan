<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->sekolah_id;

        return [
            'catatan' => 'required|string',
            'is_tl' => 'nullable|boolean',
            'anggaran_id' => [
                'required',
                'integer',
                Rule::exists('anggarans', 'id')->where(fn ($query) => $query->where('sekolah_id', $schoolId)),
            ],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => ['file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ];
    }
}
