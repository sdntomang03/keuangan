<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAkbRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',
        ];
    }

    public function messages()
    {
        return [
            'json_files.required' => 'File JSON wajib diunggah.',
            'json_files.*.mimes' => 'Format file harus berupa JSON atau TXT.',
        ];
    }
}
