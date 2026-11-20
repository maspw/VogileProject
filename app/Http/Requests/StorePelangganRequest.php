<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pelanggan' => 'nullable|string|max:20|unique:pelanggan,id_pelanggan',
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string|max:20',
            'limit_piutang' => 'nullable|numeric|min:0',
        ];
    }
}
