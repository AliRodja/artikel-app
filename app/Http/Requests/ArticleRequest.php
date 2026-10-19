<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Form Request: aturan validasi dipisah dari controller dan dipakai ulang oleh store() & update().
// Jika validasi gagal, Laravel otomatis redirect kembali ke form dengan $errors dan old() input.
class ArticleRequest extends FormRequest
{
    // Hak akses (milik siapa artikelnya) dicek di controller lewat ArticlePolicy,
    // jadi di sini cukup true: route sudah dilindungi middleware auth.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            // nullable: gambar boleh tidak diisi. max dalam kilobyte → 2048 KB = 2 MB.
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Rule::in memastikan status hanya 0 (Draft) atau 1 (Published), bukan angka lain dari form yang dimanipulasi.
            'status'       => ['required', Rule::in([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED])],
            // Checkbox "hapus gambar" di form edit.
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    // Nama field yang tampil di pesan error, mis. "Judul wajib diisi." bukan "Title wajib diisi."
    public function attributes(): array
    {
        return [
            'title'        => 'judul',
            'content'      => 'isi artikel',
            'image'        => 'gambar',
            'status'       => 'status',
            'remove_image' => 'hapus gambar',
        ];
    }
}
