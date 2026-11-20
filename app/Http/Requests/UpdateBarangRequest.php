<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string|max:150',
            'harga_barang' => 'required|numeric|min:0',
            'hpp' => 'required|numeric|min:0',
            'satuan' => 'nullable|string|max:20',
            'min_stok' => 'nullable|integer|min:0',
        ];
    }
}
