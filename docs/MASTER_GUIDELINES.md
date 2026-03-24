# Master Development Guidelines & Rules

Dokumen ini berisi aturan wajib yang harus diikuti oleh semua pengembang (termasuk AI Assistant) untuk menjaga konsistensi, stabilitas, dan keamanan proyek **CPH Dashboard Tyre**.

---

## 1. Kompatibilitas PHP (Wajib PHP 7.4)

Server produksi saat ini menggunakan **PHP 7.4**. Penggunaan fitur PHP 8.0+ akan menyebabkan **Syntax Error (T_DOUBLE_ARROW)**.

| Fitur Terlarang (PHP 8+)             | Solusi (PHP 7.4)                                            |
| :----------------------------------- | :---------------------------------------------------------- |
| `match ($var) { ... }`               | Gunakan `switch ($var)` atau array lookup dengan `isset()`. |
| `fn($v) => $v * 2` (Arrow Functions) | Gunakan `function($v) { return $v * 2; }`.                  |
| `$obj?->property` (Null-safe)        | Gunakan helper Laravel `optional($obj)->property`.          |
| Constructor Property Promotion       | Deklarasikan properti secara manual di dalam class.         |
| Union Types (`string\|int`)          | Gunakan satu tipe data atau hilangkan type hint.            |

> **Audit Rule**: Sebelum menyimpan file Blade atau PHP, pastikan tidak ada simbol `=>` di luar deklarasi array atau `foreach`.

---

## 2. Integritasi & Sinkronisasi Data

Data operasional harus tetap akurat dan sinkron dengan Master Data.

### A. Counter Fields (Total Ban, Stok, dll)

- Jangan biarkan field "Total" menjadi input manual jika data tersebut merupakan hasil perhitungan dari tabel lain.
- Gunakan **Eloquent Model Events** di file Model untuk sinkronisasi otomatis:
    ```php
    protected static function boot() {
        parent::boot();
        static::saved(function ($model) { $model->syncTotals(); });
        static::deleted(function ($model) { $model->syncTotals(); });
    }
    ```
- Utamakan penggunaan `withCount()` pada Controller untuk data yang ditampilkan di tabel (Index).

### B. Company Isolation

- Setiap model data operasional (Ban, Kendaraan, Monitoring) **WAJIB** menggunakan trait `App\Traits\BelongsToCompany`.
- Trait ini memastikan data tidak bocor antar instansi/perusahaan.

---

## 3. UI/UX & Standar Visual

Dashboard ini menggunakan tema premium berbasis **Vuexy/Remix Design**.

- **Icon**: Gunakan Remix Icon (`icon-base ri ri-line-name`). Contoh: `<i class="icon-base ri ri-settings-line"></i>`.
- **Alert/Konfirmasi**: Gunakan **SweetAlert2 (Swal)**. Jangan gunakan `window.confirm`.
- **DataTables**: Semua tabel di Index harus menggunakan DataTables dengan styling Bootstrap 5 yang sudah dikonfigurasi.
- **Warna Standar**:
    - Primary: `#7367f0`
    - Success: `#28c76f`
    - Warning: `#ff9f43`
    - Danger: `#ea5455`

---

## 4. Struktur Kode & Logging

- **Service & Repository Pattern**: Logika bisnis harus berada di `App\Services`, bukan langsung di Controller (khusus fitur baru).
- **Activity Log**: Setiap aksi Create, Update, dan Delete **WAJIB** mencatat log menggunakan fungsi `setLogActivity`.
- **View Partials**: Konten modal yang kompleks harus dipisah ke dalam folder `partials/` dengan prefix underscore (contoh: `_movement_detail.blade.php`).

---

## 5. Pengembangan End-to-End (Tuntas)

Setiap penambahan fitur atau perbaikan bug **WAJIB** diselesaikan secara tuntas untuk menghindari fitur "setengah jadi". Ceklis pengerjaan:

1.  **Database**: Pastikan Migration/Schema sudah mencakup field yang diperlukan.
2.  **Model**: Tambahkan relasi, boot events (jika perlu sinkronisasi), dan trait akses.
3.  **Controller**: Pastikan data tersedia baik untuk tampilan tabel (DataTables) maupun respon JSON untuk Ajax/Modal.
4.  **View**: Update tampilan utama, partials/modal, dan styling agar konsisten.
5.  **Logic & Logging**: Implementasikan logika bisnis dan catat setiap perubahan via `setLogActivity`.
6.  **Pembersihan Cache**: Jalankan `php artisan view:clear` atau `config:clear` jika ada perubahan pada View atau konfigurasi.
7.  **Final Test**: Verifikasi hasil di UI, cek log laravel, dan pastikan alert sukses/gagal muncul dengan benar.

---

## 6. Maintenance & Quality Control

- Selalu periksa `storage/logs/laravel.log` setelah melakukan revisi untuk memastikan tidak ada error yang tidak terlihat.
- Pastikan tidak ada karakter aneh atau format yang berantakan di dalam file Blade (gunakan `cat -v` untuk audit).
