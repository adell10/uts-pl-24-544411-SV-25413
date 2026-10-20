<?php

namespace App\Http\Requests;

use App\Models\Cafe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi untuk POST (tambah) dan PUT/PATCH (ubah) kafe.
 * Saat ubah, field boleh dikirim sebagian ("sometimes").
 */
class CafeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:100'],
            'area' => [$required, 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(Cafe::STATUSES)],
            'price_level' => ['sometimes', 'integer', 'between:1,3'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'work_friendly' => ['sometimes', 'boolean'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => [Rule::in(Cafe::FACILITIES)],
            'favorite_menu' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'last_visited_at' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'visit_count' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'color' => ['sometimes', Rule::in(Cafe::COLORS)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kafe wajib diisi.',
            'name.max' => 'Nama kafe maksimal 100 karakter.',
            'area.required' => 'Daerah wajib diisi.',
            'area.max' => 'Daerah maksimal 100 karakter.',
            'status.in' => 'Status harus visited atau wishlist.',
            'price_level.between' => 'Kisaran harga harus 1 sampai 3.',
            'rating.between' => 'Rating harus 1 sampai 5.',
            'work_friendly.boolean' => 'Cocok nugas harus true atau false.',
            'facilities.*.in' => 'Fasilitas tidak dikenal.',
            'last_visited_at.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'last_visited_at.before_or_equal' => 'Tanggal mampir tidak boleh di masa depan.',
            'color.in' => 'Warna kartu tidak tersedia.',
        ];
    }
}