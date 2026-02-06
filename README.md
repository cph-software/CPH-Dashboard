# CPH Dashboard Tyre - Laravel 8 Base Project

Projek ini adalah base project Laravel 8 yang dikonfigurasi khusus untuk hosting dengan **PHP 7.4** dan menggunakan **Repository & Service Pattern** untuk mempermudah maintenance serta pengembangan fitur berskala besar.

## Fitur Utama

- **PHP 7.4 Compatibility**: Terkunci via composer platform config.
- **Repository & Service Pattern**: Memisahkan logika bisnis dari controller.
- **Base Classes**: CRUD dasar otomatis via `BaseRepository` & `BaseService`.
- **Custom Artisan Commands**: Generator otomatis untuk mempercepat workflow.
- **Global Traits**: Reusable upload file (`FileUploadTrait`) dan JSON response (`ResponseTrait`).
- **Global Helpers**: Fungsi pembantu umum seperti `format_rupiah` dan `format_date`.

---

## Alur Pengembangan (Best Practice)

Ikuti alur ini untuk menambahkan fitur baru (Misal fitur: `TyreMaster`):

### 1. Buat Model & Migration
```bash
php artisan make:model Tyre -m
```
*Lengkapi file migration di `database/migrations` dan jalankan `php artisan migrate`.*

### 2. Generate Module (Repository & Service)
Gunakan command khusus yang sudah dibuat:
```bash
php artisan make:module Tyre
```
*Ini akan menghasilkan:*
- `app/Repositories/TyreRepository.php`
- `app/Services/TyreService.php`

### 3. Implementasi di Controller
Buat controller dan panggil Service-nya. Karena sudah menggunakan `BaseService`, fungsi CRUD dasar sudah tersedia.

```php
namespace App\Http\Controllers;

use App\Services\TyreService;
use Illuminate\Http\Request;

class TyreController extends Controller
{
    protected $tyreService;

    public function __construct(TyreService $tyreService)
    {
        $this->tyreService = $tyreService;
    }

    public function index()
    {
        $data = $this->tyreService->getAll();
        return view('tyre.index', compact('data'));
    }

    public function store(Request $request)
    {
        // Contoh upload file menggunakan Trait
        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'tyres');
        }

        $this->tyreService->store($data);
        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }
}
```

---

## Fungsi Reusable

### Upload File (`FileUploadTrait`)
Gunakan di Controller manapun:
- `$path = $this->uploadFile($file, $folder, $oldPath = null);`
- `$this->deleteFile($path);`

### JSON Response (`ResponseTrait`)
Berguna untuk API atau AJAX:
- `return $this->successResponse($data, $message);`
- `return $this->errorResponse($message, $code);`

### Global Helpers
Panggil langsung dari View atau Controller:
- `format_rupiah(10000)` -> Rp 10.000
- `format_date('2024-01-01')` -> 01 Jan 2024

---

## Catatan Hosting
- Pastikan versi PHP pada hosting minimal **7.4**.
- Jika menggunakan cPanel, arahkan domain/subdomain ke folder `public`.
- Jalankan `composer install --ignore-platform-reqs` jika composer di hosting memiliki versi PHP yang berbeda.
