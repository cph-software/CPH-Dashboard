# 📋 CHANGELOG - CPH Dashboard Tyre Performance

> **Dokumen ini** mencatat seluruh perubahan yang dilakukan pada proyek CPH-Dashboard
> sejak di-clone dari branch `staging` di GitHub.
>
> **Tanggal Mulai Pengembangan:** Maret 2026
> **Terakhir Diperbarui:** 4 April 2026

---

## 🔐 Ringkasan Singkat

Total **33 file dimodifikasi** dan **3 file baru ditambahkan**, dengan fokus utama pada:
1. Sistem Validasi Import yang ketat (Anti Data Loss)
2. Perbaikan Bug Import Approval untuk data massal
3. Optimasi Dashboard agar otomatis mendeteksi rentang data
4. Penyempurnaan Master Data (Tyre & Kendaraan)
5. Peningkatan Monitoring & Examination

---

## 📦 A. PERUBAHAN DATABASE

### A1. Migration: `create_users_table.php`
- **Perubahan:** Penyesuaian minor pada struktur tabel users
- **Dampak:** Kompatibilitas kolom dengan fitur multi-company

### A2. Migration: `add_tyre_position_configuration_id_to_master_import_kendaraan_table.php`
- **Perubahan:** Memperluas kolom pada tabel `master_import_kendaraan`
- **Dampak:** Mendukung relasi konfigurasi posisi ban per kendaraan

### A3. Migration: `restructure_tyre_tables_for_refined_logic.php`
- **Perubahan:** Restrukturisasi tabel-tabel ban untuk logika yang lebih refined
- **Dampak:** Menambahkan kolom-kolom baru yang mendukung tracking lifetime KM/HM, status ban yang lebih granular, dan field `last_hm_reading`

### A4. Seeders yang Dimodifikasi
| Seeder | Perubahan |
|--------|-----------|
| `DatabaseSeeder.php` | Urutan seeding diperbaiki, penambahan seeder baru |
| `DemoDataSeeder.php` | Data demo disesuaikan dengan struktur baru |
| `OnboardingMenuSeeder.php` | Menu baru ditambahkan |
| `TyreExaminationMenuSeeder.php` | Menu Examination ditambahkan |
| `TyrePerformanceMenuRefactorSeeder.php` | Refaktor menu Tyre Performance |

### A5. Seeders Baru (Belum di-commit)
| Seeder | Fungsi |
|--------|--------|
| `FixMenuPlacementSeeder.php` | Merapikan penempatan menu di sidebar |
| `FixRolePermissionsSeeder.php` | Memperbaiki hak akses role |

---

## 🛡️ B. FITUR UTAMA: SISTEM VALIDASI IMPORT (Anti Data Loss)

### B1. `ImportController.php` — Pre-Check Validation (+76 baris)
**Ini adalah perubahan terpenting.** Sebelumnya, sistem memproses file Excel tanpa mengecek apakah data referensi (ban, kendaraan) sudah ada di Master. Akibatnya, ribuan data bisa hilang diam-diam.

**Yang ditambahkan:**
- ✅ **Validasi Serial Number Ban** — Sistem mengecek apakah semua SN Ban di file Excel sudah terdaftar di Master Tyre sebelum batch dibuat
- ✅ **Validasi Kode Kendaraan** — Sistem mengecek apakah semua kode unit sudah terdaftar di Master Vehicle
- ✅ **Validasi Layout/Konfigurasi** — Untuk import Vehicle Master, posisi roda dicek ke tabel Position Configuration
- ✅ **Auto-Reject** — Jika ada data yang tidak cocok, import langsung ditolak dengan pesan error yang informatif (menampilkan daftar data yang bermasalah)
- ✅ **Optimasi Performa** — Pengecekan menggunakan `array_chunk(1000)` agar query database tidak overload untuk data puluhan ribu baris

### B2. `ImportApprovalController.php` — Bug Fix & Optimasi (+148 baris modifikasi)
**Perubahan krusial pada mesin pemrosesan import:**

- ✅ **Fix Timeout Issue** — Menambahkan `set_time_limit(0)` dan `ini_set('memory_limit', '-1')` agar pemrosesan ribuan data tidak terputus di tengah jalan
- ✅ **Chunking Processing** — Data diproses per batch 200 baris menggunakan `chunkById()`, bukan `get()` sekaligus, untuk mencegah kehabisan RAM
- ✅ **Fix Empty String Bug** — Kolom numerik (odometer, hm, rtd, psi) yang kosong di Excel kini di-cast dengan benar ke `(float)` atau `null`, bukan string kosong yang menyebabkan error database
- ✅ **Fix Status Enum Bug** — Kolom `status` dan `target_status` yang kosong kini otomatis terisi default (`'Repaired'`), mencegah error "Data truncated for column 'status'"
- ✅ **Fix Position Lookup** — Sistem kini bisa mencocokkan posisi ban dari format angka (`1`, `2`, `3`) ke kode posisi (`FL`, `FR`, `LRI1`) menggunakan `display_order`
- ✅ **Fix Removal Validation Gap** — Menambahkan validasi posisi untuk proses Removal yang sebelumnya tidak ada (celah logika dari developer lama)

---

## 📊 C. DASHBOARD

### C1. `DashboardController.php` (+28 baris modifikasi)
- ✅ **Auto-Detect Date Range** — Dashboard kini otomatis mendeteksi tanggal paling awal dari data movement yang ada, bukan lagi hardcode "6 bulan terakhir". Ini mengatasi masalah grafik kosong ketika data historis berada di luar jangkauan filter default

---

## 🔧 D. CONTROLLERS (Logic Bisnis)

### D1. `TyreMasterController.php` (+39 baris)
- Penyempurnaan validasi pada form edit ban
- Penambahan field-field baru pada proses create/update
- Integrasi dengan sistem multi-company

### D2. `KendaraanController.php` (+40 baris)
- Penambahan fitur terkait tyre position configuration
- Perbaikan logic pada proses manajemen kendaraan

### D3. `MonitoringController.php` (+68 baris)
- Penyempurnaan alur monitoring session
- Perbaikan kalkulasi dan data pre-fill pada form monitoring

### D4. `TyreMovementController.php` (+9 baris)
- Minor adjustment pada tampilan movement dan logic bisnis

### D5. `TyreExaminationController.php` (+13 baris)
- Penyesuaian pada alur examination

---

## 📁 E. MODELS (Struktur Data)

| Model | Perubahan |
|-------|-----------|
| `Tyre.php` | Penambahan relasi, accessor, dan field baru (+16 baris) |
| `TyreMovement.php` | Penambahan relasi dan cast (+13 baris) |
| `TyreSegment.php` | Penyesuaian scope dan relasi (+5 baris) |
| `MasterImportKendaraan.php` | Penambahan relasi ke TyrePositionConfiguration (+5 baris) |

### Model Baru (Belum di-commit)
| Model | Fungsi |
|-------|--------|
| `TyreReconcileData.php` (Command) | Artisan command untuk rekonsiliasi data ban |

---

## 📐 F. TEMPLATE IMPORT EXCEL

| File | Perubahan |
|------|-----------|
| `ImportTemplateExport.php` | Penambahan sheet baru (+3 baris) |
| `MovementHistorySheet.php` | Penyesuaian header kolom |
| `TyreMasterSheet.php` | Penyesuaian header kolom |
| `VehicleMasterSheet.php` | Penyesuaian header kolom |
| `OtherTemplateSheets.php` | Penambahan sheet tambahan (+12 baris) |

---

## 🎨 G. VIEWS (Tampilan UI)

| View | Perubahan |
|------|-----------|
| `master/tyres/index.blade.php` | Peningkatan tampilan tabel, filter, dan aksi (+166 baris) |
| `master/tyres/edit.blade.php` | Penyesuaian form edit (+19 baris) |
| `master/kendaraan/index.blade.php` | Penambahan kolom dan fitur (+11 baris) |
| `monitoring/create_session.blade.php` | Perbaikan form monitoring (+153 baris) |

---

## 🔩 H. LAIN-LAIN

| File | Perubahan |
|------|-----------|
| `helpers.php` | Penambahan helper function baru (+3 baris) |
| `SyncTyreLocationStock.php` | Minor fix pada command sync stok |
| `composer.json` | Penambahan dependency baru |
| `composer.lock` | Auto-generated dari composer |

---

## 🗑️ I. FILE YANG SUDAH DIBERSIHKAN

File-file test/debugging berikut sudah dihapus karena tidak diperlukan di production:

```
test_audit.php, test_columns.php, test_company.php, test_dashboard.php,
test_dropdown.html, test_fail.php, test_fallback.php, test_menu_structure.php,
test_perms.php, test_perms2.php, test_pwd.php, test_render.php,
test_roles.php, test_users.php, test_verify.php, check_data.php, cleanup_import.php
```

---

## ⚠️ CATATAN PENTING UNTUK DEPLOYMENT

1. **Database Migration** — Setelah push, jalankan `php artisan migrate` di server hosting
2. **Database Seeder** — Jalankan seeder yang diperlukan untuk menu dan permissions
3. **Composer** — Jalankan `composer install` jika ada dependency baru
4. **Cache** — Jalankan `php artisan config:clear && php artisan cache:clear` setelah deploy
5. **File `.env`** — Pastikan konfigurasi `.env` di server sesuai (file ini TIDAK di-push ke GitHub)
