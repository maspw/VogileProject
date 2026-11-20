<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_akun' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
            'saldo_normal' => 'required|in:Debit,Kredit',
        ];
    }
}
