<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarangMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->role === 'operator') {
            $this->merge(['user_id' => $this->user()->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'operator')],
            'sub_category_id' => ['required', 'integer', 'exists:sub_categories,id'],
            'asal_barang' => ['required', 'string', 'max:200'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'lampiran' => ['nullable', 'file', 'mimes:doc,docx,zip', 'max:5120'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_barang' => ['required', 'string', 'max:200'],
            'items.*.harga' => ['required', 'integer', 'min:0'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.satuan' => ['required', 'string', 'max:40'],
            'items.*.tgl_expired' => ['nullable', 'date'],
        ];
    }
}
