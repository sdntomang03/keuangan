<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompareAkbRequest extends FormRequest
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
            'jenis_json' => 'required|in:baru,lama',
        ];
    }

    public function messages()
    {
        return [
            'jenis_json.required' => 'Silakan pilih apakah ini JSON Baru atau Lama.',
        ];
    }
}
