<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi (Bahasa Indonesia)
|--------------------------------------------------------------------------
|
| Dipakai oleh validator Laravel dan seluruh form Filament (APP_LOCALE=id).
| :attribute otomatis berisi label field. Aturan yang tidak tercantum di sini
| memakai pesan bawaan bahasa Inggris.
|
*/

return [

    'accepted' => ':attribute wajib disetujui.',
    'array' => ':attribute harus berupa daftar yang valid.',
    'boolean' => ':attribute hanya boleh bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak sesuai. Silakan periksa kembali isian Anda.',
    'current_password' => 'Kata sandi yang Anda masukkan tidak sesuai.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'distinct' => ':attribute memiliki nilai yang sama dengan isian lain. Silakan gunakan nilai yang berbeda.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan. Silakan pilih kembali.',
    'file' => ':attribute harus berupa berkas.',
    'image' => ':attribute harus berupa berkas gambar (JPG, PNG, atau sejenisnya).',
    'in' => ':attribute yang dipilih tidak valid. Silakan pilih salah satu opsi yang tersedia.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
        'file' => 'Ukuran :attribute tidak boleh melebihi :max kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh melebihi :max karakter.',
    ],
    'mimes' => ':attribute harus berupa berkas dengan format: :values.',
    'mimetypes' => ':attribute harus berupa berkas dengan format: :values.',
    'min' => [
        'array' => ':attribute harus memiliki minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':attribute tidak boleh kurang dari :min.',
        'string' => ':attribute minimal terdiri dari :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi apabila :other bernilai :value.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan. Silakan gunakan nilai lain.',
    'uploaded' => ':attribute gagal diunggah. Silakan coba kembali.',
    'url' => ':attribute harus berupa tautan (URL) yang valid.',

];
