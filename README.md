# CPH Dashboard — Tyre Performance Management System

> **Internal Enterprise Dashboard** untuk manajemen performa ban kendaraan armada.  
> Dibangun di atas **Laravel 8** dengan **PHP 7.4** compatibility.

---

## 📑 Daftar Isi

1.  [Gambaran Umum](#-gambaran-umum)
2.  [Tech Stack](#-tech-stack)
3.  [Setup Lokal (Onboarding)](#-setup-lokal-onboarding)
4.  [Arsitektur Proyek](#-arsitektur-proyek)
5.  [Struktur Folder Penting](#-struktur-folder-penting)
6.  [Modul & Fitur Sistem](#-modul--fitur-sistem)
7.  [Skema Database (Key Tables)](#-skema-database-key-tables)
8.  [Role & Permission System](#-role--permission-system)
9.  [Alur Kerja Utama (Workflows)](#-alur-kerja-utama-workflows)
10. [Panduan Pengembangan](#-panduan-pengembangan)
11. [Deployment & Hosting](#-deployment--hosting)
12. [Dokumentasi Lanjutan](#-dokumentasi-lanjutan)

---

## 🏢 Gambaran Umum

CPH Dashboard adalah sistem enterprise untuk **monitoring, pemeriksaan, dan analisis performa ban** pada armada kendaraan. Sistem ini digunakan oleh beberapa instansi/perusahaan secara multi-tenant, dengan mekanisme isolasi data per company.

### Stakeholder Utama

- **Administrator (CPH)** — Full access, konfigurasi master data, approval
- **Supervisor** — Monitoring dashboard, review data, export report
- **Tyre Man / Admin Lapangan** — Input harian: pemasangan, pelepasan, pemeriksaan
- **Customer/Sales** — Input data menunggu approval

---

## 🛠 Tech Stack

| Layer               | Teknologi                            | Versi                        |
| ------------------- | ------------------------------------ | ---------------------------- |
| **Backend**         | Laravel (PHP)                        | 8.x (PHP 7.4 prod / 8.x dev) |
| **Database**        | MySQL                                | 5.7+ / 8.x                   |
| **Frontend**        | Blade + jQuery + Bootstrap 5         | —                            |
| **UI Theme**        | Vuexy Admin Template (Remix Icon)    | —                            |
| **DataTables**      | DataTables Bootstrap 5 (Server-side) | —                            |
| **Excel I/O**       | Maatwebsite/Excel (Laravel Excel)    | 3.x                          |
| **PDF**             | Barryvdh/DomPDF                      | —                            |
| **Alerts**          | SweetAlert2                          | —                            |
| **Dropdowns**       | Select2                              | —                            |
| **Version Control** | Git + GitHub Actions (CI/CD)         | —                            |

---

## 🚀 Setup Lokal (Onboarding)

### Prasyarat

- PHP 7.4+ (atau 8.x untuk development)
- Composer 2.x
- MySQL 5.7+ / MariaDB 10.3+
- Node.js (opsional, hanya jika perlu compile assets)
- Git

### Langkah-langkah

```bash
# 1. Clone repository
git clone <repo-url> CPH-Dashboard
cd CPH-Dashboard

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate
```

**Edit file `.env`** sesuai database lokal:

```env
APP_NAME="CPH Dashboard"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cph_dashboard
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
# 4. Buat database
mysql -u root -p -e "CREATE DATABASE cph_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Jalankan migration
php artisan migrate

# 6. Jalankan demo seeder (PENTING untuk presentasi/testing)
php artisan db:seed --class=DemoDataSeeder

# 7. Buat symbolic link untuk storage
php artisan storage:link

# 8. Jalankan server
php artisan serve
```

Akses di: `http://localhost:8000`

### Troubleshooting Umum

| Masalah                                | Solusi                                                                                                                |
| -------------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| `PHP Fatal error: match expression`    | Gunakan `switch` statement. Lihat [MASTER_GUIDELINES.md](docs/MASTER_GUIDELINES.md#1-kompatibilitas-php-wajib-php-74) |
| `SQLSTATE[42S22]: Column not found`    | Jalankan `php artisan migrate`. Cek docs/TASK_TRACKER.md untuk migrasi terbaru.                                       |
| View not found: `layouts.layoutMaster` | Layout yang benar: `layouts.admin`.                                                                                   |
| 403 Forbidden                          | Cek role_id user di DB. Admin = role_id 1 (bypass semua permission).                                                  |
| Gambar/foto tidak muncul               | Jalankan `php artisan storage:link`.                                                                                  |

---

## 🏗 Arsitektur Proyek

```
┌──────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                       │
│   Blade Views + jQuery + DataTables + Select2 + SweetAlert2  │
└──────────────┬──────────────────────────────────┬─────────────┘
               │ HTTP Request                     │ AJAX/JSON
┌──────────────▼──────────────────────────────────▼─────────────┐
│                     ROUTES (web.php)                          │
│              Middleware: auth, tyre.permission                │
└──────────────┬────────────────────────────────────────────────┘
               │
┌──────────────▼────────────────────────────────────────────────┐
│                      CONTROLLERS                              │
│  ┌─────────────┐ ┌──────────────┐ ┌────────────────────────┐ │
│  │  Master Data │ │  Operations  │ │   User Management      │ │
│  │  Controllers │ │  Controllers │ │   Controllers          │ │
│  └──────┬──────┘ └──────┬───────┘ └──────────┬─────────────┘ │
└─────────┼───────────────┼────────────────────┼───────────────┘
          │               │                    │
┌─────────▼───────────────▼────────────────────▼───────────────┐
│                    ELOQUENT MODELS                            │
│       Traits: BelongsToCompany, UserTracking                 │
│       Relationships, Scopes, Boot Events                     │
└──────────────────────────┬───────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│                     MySQL DATABASE                            │
│    70+ tables: tyres, tyre_movements, tyre_monitoring_*,     │
│    tyre_examinations, master_import_kendaraan, etc.          │
└──────────────────────────────────────────────────────────────┘
```

### Pattern yang Digunakan

| Pattern                  | Lokasi                               | Keterangan                                                                                                    |
| ------------------------ | ------------------------------------ | ------------------------------------------------------------------------------------------------------------- |
| **Repository & Service** | `app/Repositories/`, `app/Services/` | Digunakan untuk modul User Management. Modul Tyre langsung di Controller.                                     |
| **Traits**               | `app/Traits/`                        | `BelongsToCompany` (isolasi data), `UserTracking` (created_by/updated_by), `FileUploadTrait`, `ResponseTrait` |
| **Middleware**           | `app/Http/Middleware/`               | `CheckTyrePermission` (permission per menu + action), `CheckPermission` (legacy)                              |
| **Global Helpers**       | `app/Helpers/helpers.php`            | `format_rupiah()`, `format_date()`, `hasPermission()`, `setLogActivity()`                                     |

---

## 📂 Struktur Folder Penting

```
app/
├── Console/Commands/          # Artisan commands (BulkUpdateCompanyData, etc.)
├── Helpers/helpers.php        # Global helper functions
├── Http/
│   ├── Controllers/
│   │   ├── TyrePerformance/   # ★ Semua controller modul ban
│   │   │   ├── DashboardController.php
│   │   │   ├── Examination/TyreExaminationController.php
│   │   │   ├── Master/        # Brand, Size, Pattern, Company, Position, dll
│   │   │   ├── Monitoring/MonitoringController.php
│   │   │   └── Movement/TyreMovementController.php
│   │   └── UserManagement/    # Role, User, Menu, Permission, Import
├── Exports/                   # ★ Excel Export & Template Logic
│   └── ImportTemplateSheets/  # Lembar kerja .xlsx per modul
│   └── ImportTemplateExport.php
│   └── SimpleArrayExport.php
│   └── Monitoring/SessionExport.php
│   └── Middleware/
│       ├── CheckTyrePermission.php  # ★ Permission checker utama
│       └── CheckPermission.php      # Legacy permission
├── Models/                    # Eloquent models (23+ models tyre-related)
├── Repositories/              # Repository pattern (User Management)
├── Services/                  # Service pattern (User Management)
└── Traits/                    # BelongsToCompany, UserTracking, etc.

database/
└── migrations/                # 100+ migration files

resources/views/
├── layouts/admin.blade.php    # ★ Layout utama (Vuexy theme)
├── tyre-performance/          # ★ Semua view modul ban
│   ├── dashboard/             # KPI Dashboard
│   ├── examination/           # Form pemeriksaan & detail
│   ├── master/                # CRUD master data (tyres, brands, etc.)
│   ├── monitoring/            # Periodic monitoring sessions
│   └── movement/              # Install, Remove, Rotate views
└── user-management/           # Role, user, menu management

docs/                          # ★ Dokumentasi proyek (BACA INI)
├── MASTER_GUIDELINES.md       # Aturan coding wajib
├── TASK_TRACKER.md            # Status task & progress
├── GUIDE_BOOK.md              # Panduan penggunaan untuk end-user
├── DEVELOPMENT_ROADMAP.md     # Roadmap fitur jangka panjang
├── PROJECT_STATUS.md          # Status proyek detail
└── GSI_FEEDBACK_ACTION_PLAN.md # Action plan dari feedback client

routes/
└── web.php                    # ★ Semua route definisi (lihat komentar per section)
```

---

## 📦 Modul & Fitur Sistem

### A. Dashboard Analytics (`/tyre-dashboard`)

- KPI overview: total ban, status distribusi, rata-rata performa
- Drill-down per brand, size, pattern
- CPK (Cost Per Kilometer) analysis
- Scrap analysis per posisi
- Export Excel/PDF

### B. Master Data (`/master_*`)

| Menu               | URL                    | Controller                  | Keterangan                                      |
| ------------------ | ---------------------- | --------------------------- | ----------------------------------------------- |
| Master Tyre        | `/master_tyre`         | `TyreMasterController`      | Data ban: SN, brand, size, pattern, OTD, status |
| Vehicle Master     | `/master_kendaraan`    | `KendaraanController`       | Data kendaraan: kode, no polisi, layout roda    |
| Brands             | `/master_brand`        | `TyreBrandController`       | Merek ban                                       |
| Sizes              | `/master_size`         | `TyreSizeController`        | Ukuran ban (per brand)                          |
| Patterns           | `/master_pattern`      | `TyrePatternController`     | Pola ban (per brand)                            |
| Locations          | `/master_location`     | `TyreLocationController`    | Lokasi kerja & gudang                           |
| Segments           | `/master_segment`      | `TyreSegmentController`     | Segment operasional                             |
| Failure Codes      | `/master_failure_code` | `TyreFailureCodeController` | Kode kerusakan ban                              |
| Axle Layouts       | `/master_position`     | `TyrePositionController`    | Konfigurasi posisi roda                         |
| Company (Instansi) | `/master_company`      | `TyreCompanyController`     | Multi-tenant company management                 |

### C. Tyre Movement / Operations (`/movement`, `/pemasangan`, `/pelepasan`, `/rotasi`)

- **Pemasangan (Install):** Pasang ban ke posisi kendaraan
- **Pelepasan (Remove):** Lepas ban + catat failure code
- **Rotasi (Rotate):** Pindah posisi ban antar roda
- **Movement History:** Riwayat lengkap pergerakan ban

### D. Monitoring (`/monitoring`)

- **Vehicle Registration:** Daftarkan kendaraan ke sistem monitoring
- **Session Management:** Buat sesi monitoring per periodik
- **Installation Tracking:** Catat ban yang terpasang saat sesi dimulai
- **Periodic Checks:** Input RTD, PSI, dengan foto dokumentasi
- **Approval Workflow:** User biasa → Pending, Admin → langsung Approved
- **Axle Rule:** Semua ban per sumbu (kecuali depan) harus dicek bersamaan

### E. Examination (`/examination`)

- **Form Pemeriksaan:** Input RTD 1-4, PSI, per posisi ban
- **Input Mode:** Customer (langsung approved) vs Sales (perlu approval)
- **Human Error Detection:** Deteksi anomali odometer, HM, RTD
- **PDF Export:** Cetak form pemeriksaan (A5 landscape)

### F. Warehouse & Inventory Tracking (`/master_tyre`, `/master_location`)

- **Stock Syncing:** Otomatis update `current_stock` di lokasi saat ban dipasang/dilepas.
- **Warehouse Status:** Flag `is_in_warehouse` untuk membedakan ban stok vs ban terpasang.
- **Location Tracking:** Kolom `current_location_id` di tabel tyres untuk melacak posisi fisik ban di gudang.

### G. Import/Export & Template System (`/import-approval`)

- **Professional Templates:** 9 modul template `.xlsx` dengan header berwarna, data contoh, dan panduan pengisian terintegrasi.
- **Import Approval:** Upload XLSX → Antrean Approval → Sync ke Database.
- **Export Data:** Download data ban, kendaraan, dan transaksi ke Excel.

---

## 🗄 Skema Database (Key Tables)

### Master Data

| Tabel                          | Fungsi                    | Relasi Utama                    |
| ------------------------------ | ------------------------- | ------------------------------- |
| `tyres`                        | Data ban utama            | → brand, size, pattern, company |
| `master_import_kendaraan`      | Data kendaraan            | → position config, company      |
| `tyre_brands`                  | Merek ban                 | ← sizes, patterns               |
| `tyre_sizes`                   | Ukuran ban                | → brand                         |
| `tyre_patterns`                | Pola ban                  | → brand                         |
| `tyre_companies`               | Instansi/perusahaan       | ← users, tyres, vehicles        |
| `tyre_position_configurations` | Layout roda (axle config) | ← position_details              |
| `tyre_position_details`        | Detail per posisi roda    | → configuration                 |
| `tyre_locations`               | Lokasi kerja/gudang       |                                 |
| `tyre_segments`                | Segment operasional       |                                 |
| `tyre_failure_codes`           | Kode kerusakan            |                                 |

### Operational

| Tabel                          | Fungsi                                            |
| ------------------------------ | ------------------------------------------------- |
| `tyre_movements`               | Riwayat pergerakan ban (install, remove, inspect) |
| `tyre_monitoring_vehicle`      | Kendaraan monitoring (`is_trail` flag)            |
| `tyre_monitoring_session`      | Sesi monitoring periodik                          |
| `tyre_monitoring_installation` | Ban terpasang saat sesi                           |
| `tyre_monitoring_check`        | Data pengecekan (RTD, PSI) + approval_status      |
| `tyre_monitoring_removal`      | Data pelepasan dari monitoring                    |
| `tyre_monitoring_images`       | Foto dokumentasi monitoring                       |
| `tyre_examinations`            | Header pemeriksaan + approval_status              |
| `tyre_examination_details`     | Detail per ban yang diperiksa                     |
| `tyre_examination_images`      | Foto dokumentasi examination                      |

### System

| Tabel            | Fungsi                                  |
| ---------------- | --------------------------------------- |
| `users`          | Akun pengguna (→ role, → company)       |
| `role`           | Definisi role                           |
| `menu`           | Definisi menu sidebar                   |
| `role_menu`      | Pivot: role ↔ menu + permissions (JSON) |
| `import_batches` | Batch import data                       |
| `import_items`   | Item per baris import                   |
| `activity_log`   | Audit trail                             |

### Key Columns di `tyres`

```
id, tyre_company_id, serial_number, custom_serial_number,
tyre_brand_id, tyre_size_id, tyre_pattern_id,
ply_rating, original_tread_depth, is_in_warehouse, segment_name,
status, retread_count, price,
initial_tread_depth, current_tread_depth,
total_lifetime_km, total_lifetime_hm, current_km, current_hm,
last_inspection_date, current_vehicle_id, current_position_id,
created_at, updated_at, created_by, updated_by
```

---

## 🔐 Role & Permission System

### Mekanisme

1. User punya `role_id` → Role punya banyak Menu (via `role_menu` pivot)
2. Pivot `role_menu` menyimpan `permissions` sebagai JSON array: `["view","create","update","delete","export","import"]`
3. Middleware `tyre.permission:{MenuName}` otomatis cek akses berdasarkan route action

### Admin Bypass

Role dengan `role_id = 1` (Administrator) **bypass semua permission check** dan memiliki full access ke seluruh sistem.

### Cara Kerja di Route

```php
// Format: middleware('tyre.permission:{Nama Menu di DB}')
Route::resource('master_tyre', TyreMasterController::class)
    ->middleware('tyre.permission:Master Tyre');

// Dengan force permission
Route::post('tyre-store', ...)->middleware('tyre.permission:Tyre Operations,create');
```

### Auto-Detect Permission dari Route

| Route Action            | Permission |
| ----------------------- | ---------- |
| `index`, `show`, `data` | `view`     |
| `create`, `store`       | `create`   |
| `edit`, `update`        | `update`   |
| `destroy`               | `delete`   |

### Helper Global

```php
// Di Blade views
@if (hasPermission('Master Tyre', 'create'))
    <button>Tambah Ban</button>
@endif
```

---

## 🔄 Alur Kerja Utama (Workflows)

### Alur 1: Registrasi Ban Baru

```
Input di Master Tyre → Status: "New" → Siap dipasang
```

### Alur 2: Pemasangan Ban

```
Pilih Kendaraan → Pilih Posisi Roda → Pilih SN Ban (New/Repaired)
→ Input Odometer & RTD → Status ban: "Installed" → Movement tercatat
```

### Alur 3: Monitoring Periodik

```
Buat Session → Install ban ke session → Input Check (RTD, PSI, Foto)
→ [User biasa: Pending] → [Admin approve] → Data sync ke master
→ [Admin langsung: Approved & sync]
```

### Alur 4: Examination (Pemeriksaan)

```
Pilih Kendaraan → Input Odometer/HM → Isi RTD & PSI per posisi (opsional)
→ [Input Mode: Customer → langsung Approved]
→ [Input Mode: Sales → Pending → Admin approve]
```

### Alur 5: Pelepasan Ban

```
Pilih Kendaraan → Pilih Ban → Input alasan (Failure Code) + Target Status
→ Ban keluar dari unit → Lifetime KM/HM dihitung → Status: "Repaired"/"Scrap"
```

### Alur 6: Import Data

```
Upload CSV/XLSX → Masuk sebagai Batch (Pending) → Admin review → Approve/Reject
→ Data masuk ke master (jika approved)
```

Lihat detail lengkap di: [`docs/GUIDE_BOOK.md`](docs/GUIDE_BOOK.md)

---

## 👨‍💻 Panduan Pengembangan

### ⚠️ WAJIB BACA SEBELUM CODING

| Dokumen                 | Isi                                                   | Link                                                         |
| ----------------------- | ----------------------------------------------------- | ------------------------------------------------------------ |
| **Master Guidelines**   | Aturan PHP 7.4, coding standards, UI/UX rules         | [`docs/MASTER_GUIDELINES.md`](docs/MASTER_GUIDELINES.md)     |
| **Task Tracker**        | Status semua task, apa yang sudah/belum selesai       | [`docs/TASK_TRACKER.md`](docs/TASK_TRACKER.md)               |
| **Development Roadmap** | Roadmap fitur jangka panjang (BA, Invoice, Lead Time) | [`docs/DEVELOPMENT_ROADMAP.md`](docs/DEVELOPMENT_ROADMAP.md) |

### Aturan Kritis

1. **PHP 7.4 Only (Produksi):** Jangan gunakan `match()`, arrow functions `fn()`, null-safe `?->`, union types. Detail lengkap di [MASTER_GUIDELINES.md](docs/MASTER_GUIDELINES.md).

2. **Activity Logging:** Setiap aksi CRUD **wajib** dicatat:

    ```php
    setLogActivity(auth()->id(), "Menambahkan ban baru SN: {$tyre->serial_number}", [
        'module' => 'Master Tyre',
        'action' => 'create'
    ]);
    ```

3. **Company Isolation:** Data operasional harus terisolasi per company. Gunakan trait `BelongsToCompany` di model.

4. **Layout:** Semua view harus extend `layouts.admin`:
    ```blade
    @extends('layouts.admin')
    @section('title', 'Judul Halaman')
    @section('content') ... @endsection
    ```

### Menambahkan Fitur Baru

```bash
# 1. Buat Model + Migration
php artisan make:model NamaModel -m

# 2. (Opsional) Generate Repository & Service
php artisan make:module NamaModule

# 3. Buat Controller
php artisan make:controller TyrePerformance/NamaController

# 4. Daftarkan route di web.php (dalam group 'auth')

# 5. Daftarkan menu di database (tabel 'menu')

# 6. Assign menu ke role di tabel 'role_menu'
```

### Command Berguna

```bash
# Jalankan migration
php artisan migrate

# Rollback migration terakhir
php artisan migrate:rollback

# Cek status migration
php artisan migrate:status

# Clear semua cache
php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan cache:clear

# Tinker (debug database)
php artisan tinker

# Cek route list
php artisan route:list --columns=method,uri,name,middleware
```

---

## 🌐 Deployment & Hosting

### cPanel / Shared Hosting

1. Pastikan PHP versi **7.4** aktif di hosting
2. Arahkan domain/subdomain ke folder `public/`
3. Upload semua file kecuali `.env`, `vendor/`, `node_modules/`
4. Jalankan di hosting:
    ```bash
    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan storage:link
    ```

### CI/CD (GitHub Actions)

- Workflow deploy otomatis via FTP menggunakan `SamKirkland/FTP-Deploy-Action`
- Lihat `.github/workflows/` untuk konfigurasi

### Environment Variables Penting

| Variable            | Keterangan                       |
| ------------------- | -------------------------------- |
| `APP_ENV`           | `production` untuk live          |
| `APP_DEBUG`         | `false` untuk live               |
| `APP_URL`           | URL lengkap domain               |
| `DB_*`              | Kredensial database              |
| `FILESYSTEM_DRIVER` | `public` (untuk foto/attachment) |

---

## 📚 Dokumentasi Lanjutan

| File                                                                   | Deskripsi                                                              |
| ---------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| [`docs/MASTER_GUIDELINES.md`](docs/MASTER_GUIDELINES.md)               | **Aturan coding wajib** — PHP 7.4 rules, UI standards, data integrity  |
| [`docs/TASK_TRACKER.md`](docs/TASK_TRACKER.md)                         | **Task tracker** — Semua task beserta status (selesai/pending/diskusi) |
| [`docs/GUIDE_BOOK.md`](docs/GUIDE_BOOK.md)                             | **Panduan pengguna** — Alur kerja, checklist kesiapan, SOP end user    |
| [`docs/DEVELOPMENT_ROADMAP.md`](docs/DEVELOPMENT_ROADMAP.md)           | **Roadmap** — Fitur jangka panjang (BA, Invoice, Lead Time)            |
| [`docs/PROJECT_STATUS.md`](docs/PROJECT_STATUS.md)                     | **Status proyek** — Detail teknis progress keseluruhan                 |
| [`docs/GSI_FEEDBACK_ACTION_PLAN.md`](docs/GSI_FEEDBACK_ACTION_PLAN.md) | **Action plan** — Respon terhadap feedback dari client GSI             |

---

## 🆘 Kontak & Support

Jika mengalami kesulitan saat development, periksa:

1. `storage/logs/laravel.log` — Error log Laravel
2. Browser DevTools Console — JavaScript errors
3. `docs/TASK_TRACKER.md` — Status fitur terkini
4. `docs/MASTER_GUIDELINES.md` — Aturan yang mungkin dilanggar

---

_Terakhir diperbarui: 25 Maret 2026_
