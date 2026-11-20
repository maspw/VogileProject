<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_akun' => 'required|integer|unique:coa,kode_akun',
            'nama_akun' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
            'saldo_normal' => 'required|in:Debit,Kredit',
        ];
    }
}
