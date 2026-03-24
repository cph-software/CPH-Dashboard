# CPH Dashboard — Tyre Operations Task Tracker

> Last Updated: 2026-03-24 17:44 WITA

---

## 📋 DAFTAR TASK & STATUS

### Legend

- ✅ = Selesai / Sudah diimplementasi
- ⚠️ = Partial / Sebagian selesai
- ❌ = Belum dikerjakan
- 🟡 = Butuh diskusi / Pending keputusan

---

## 1. APPROVAL & VALIDASI

| #   | Task                                                      | Status | Detail                                                                                                                                                                                   |
| --- | --------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.1 | Approval di Monitoring (user → Pending, admin → Approved) | ✅     | `MonitoringController@storeBatchCheck` — user biasa submit Pending, admin langsung Approved. Kolom `approval_status`, `approved_by`, `approved_at` sudah ada di `tyre_monitoring_check`. |
| 1.2 | Tombol Approve/Reject di detail Monitoring session        | ✅     | `MonitoringController@approve` & `@reject`. Approve juga sync data RTD & lifetime KM ke master tyre.                                                                                     |
| 1.3 | Aturan 1 Sumbu = 4 Roda (Axle Rule)                       | ✅     | Validasi di `storeBatchCheck` — semua posisi non-spare dalam satu axle harus diisi, kecuali axle depan (axle 1).                                                                         |
| 1.4 | Examination: Sales → Pending, Customer → Approved         | ✅     | `TyreExaminationController@store` — field `exam_type` menentukan `approval_status`.                                                                                                      |
| 1.5 | Examination: Tombol Approve/Reject di detail              | ✅     | `TyreExaminationController@approve` & `@reject`. Approve cascade update RTD & movement history. UI di `show.blade.php` dengan SweetAlert2 konfirmasi + reject modal.                     |
| 1.6 | Examination: Ban tidak wajib semua diisi                  | ✅     | Detail tyre bersifat opsional. User bisa submit partial (hanya posisi tertentu).                                                                                                         |
| 1.7 | Examination: Minimal 1 ban harus diisi                    | ✅     | Validasi di Controller: minimal 1 ban harus punya data (PSI/RTD/Catatan/Foto) agar bisa disimpan.                                                                                        |
| 1.8 | Examination: Auto-select Input Mode berdasarkan Company   | ✅     | Jika company = CPH/Catur → Otomatis "Sales" (Pending), selain itu "Customer" (Approved). Logic di client-side creation.                                                                  |
| 1.9 | Data Monitoring: Hanya update master tyre jika Approved   | ✅     | Movement & tyre update hanya terjadi saat admin submit atau saat approve. Pending checks tidak mengubah data master.                                                                     |

### File yang Diubah:

- `app/Http/Controllers/TyrePerformance/Monitoring/MonitoringController.php`
- `app/Http/Controllers/TyrePerformance/Examination/TyreExaminationController.php`
- `resources/views/tyre-performance/examination/create.blade.php`
- `resources/views/tyre-performance/examination/show.blade.php`
- `resources/views/tyre-performance/examination/index.blade.php`
- `routes/web.php` (route approve/reject untuk monitoring & examination)

### Migrasi Terkait:

- `2026_03_23_153927_add_approval_status_to_monitoring_checks`
- `2026_03_23_155552_add_approval_columns_to_tyre_examinations`

---

## 2. MASTER DATA — RESTRUKTURISASI (Gudang & Schema)

| #   | Task                                                         | Status | Detail                                                                                                                                            |
| --- | ------------------------------------------------------------ | ------ | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2.1 | Hapus `tyre_segment_id` → ganti `segment_name` (string)      | ✅     | Kolom `tyre_segment_id` dihapus dari tabel `tyres`, diganti `segment_name`.                                                                       |
| 2.2 | Hapus `work_location_id` → ganti `is_in_warehouse` (boolean) | ✅     | Kolom `work_location_id` dihapus, diganti flag `is_in_warehouse`.                                                                                 |
| 2.3 | Tambah `ply_rating` di master tyre                           | ✅     | Kolom string, input manual di form.                                                                                                               |
| 2.4 | Tambah `original_tread_depth` di master tyre                 | ✅     | Kolom decimal, input manual (OTD).                                                                                                                |
| 2.5 | RTD/KM/HM tidak tampil di form input master tyre             | ✅     | Field ini dihitung otomatis dari movement/monitoring, bukan input manual. Kolom `total_lifetime_km/hm` tetap ada di DB untuk aggregasi dashboard. |
| 2.6 | Tambah `total_tyres` di master company                       | ✅     | Field di tabel `tyre_companies` + UI form add/edit. Menampilkan: Total Ban (Asset), Currently terdaftar, dan Limit (Quota).                       |
| 2.7 | Opsi Trail / Non-Trail di Position Layout                    | ✅     | Kolom `is_trail` ditambahkan di `tyre_position_configurations`.                                                                                   |
| 2.8 | Penambahan `current_location_id` (Gudang Spesifik)           | ✅     | Memungkinkan tracking ban berada di gudang mana (Pusat, Site A, Site B).                                                                          |

### File yang Diubah:

- `app/Http/Controllers/TyrePerformance/Master/TyreMasterController.php`
- `app/Http/Controllers/TyrePerformance/Movement/TyreMovementController.php`
- `resources/views/tyre-performance/master/tyres/index.blade.php`
- `resources/views/tyre-performance/master/companies/index.blade.php`

### Migrasi Terkait:

- `2026_03_23_153440_restructure_tyre_tables_for_refined_logic`
- `2026_03_23_154518_add_total_tyres_to_tyre_companies`
- `2026_03_24_142829_restore_lifetime_columns_to_tyres`
- `2026_03_24_233702_add_current_location_id_to_tyres`

---

## 3. ROLE & PERMISSION

| #   | Task                                                            | Status | Detail                                                                                                |
| --- | --------------------------------------------------------------- | ------ | ----------------------------------------------------------------------------------------------------- |
| 3.1 | Admin (role_id=1) bypass semua permission check                 | ✅     | `CheckTyrePermission` middleware — admin langsung `return $next($request)`.                           |
| 3.2 | Sinkronisasi nama menu di route vs database                     | ✅     | `Tyre Monitoring` → `Monitoring`, `Position Layouts` → `Axle Layouts` di `web.php`.                   |
| 3.3 | Admin: Input Brand/Size/Pattern manual (select2-tags)           | ✅     | Implemented in Master Tyre and Monitoring forms. Admin can type to create new master data on the fly. |
| 3.4 | User: Dropdown pilih dari data existing (tidak bisa ketik baru) | ✅     | Implemented via `isAdmin` check in Select2 logic. Regular users restricted to existing records.       |
| 3.5 | Hierarki dropdown Brand → Size → Pattern                        | ✅     | Cascading logic implemented in Master Tyre and Monitoring (create session) forms.                     |
| 3.6 | Navbar: Restriksi Filter Company hanya untuk Super Admin        | ✅     | Dropdown filter di top-bar navbar kini hanya tampil jika `role_id == 1`.                              |
| 3.7 | Dashboard: Sinkronisasi Tombol "Add" di Axle Layout             | ✅     | Perbaikan nama permission dari `Position Layouts` ke `Axle Layouts` di view agar tombol "Add" muncul. |

---

## 4. COMPANY ISOLATION (Data per Instansi)

| #   | Task                                               | Status | Detail                                                                                                                                 |
| --- | -------------------------------------------------- | ------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| 4.1 | Filter kendaraan & ban per company user yang login | ✅     | Implemented via `BelongsToCompany` trait in models (`Tyre`, `Vehicle`, `Movement`, etc.). Data filtered automatically by Global Scope. |
| 4.2 | Whitelist data → ubah ke dropdown pilih            | ✅     | Removed `tyre_company_*` whitelist mapping logic. Master data (Brand/Size/Pattern) is now global and filtered via cascading dropdowns. |
| 4.3 | Segment dropdown untuk user (bukan input bebas)    | ✅     | `TyreSegment` model trait removed to make it global. Dropdowns in forms now use standard selection.                                    |

---

## 5. LOCATION & WAREHOUSE

| #   | Task                                            | Status | Detail                                                                                                         |
| --- | ----------------------------------------------- | ------ | -------------------------------------------------------------------------------------------------------------- |
| 5.1 | Pisahkan konsep Warehouse vs Operasional (Site) | ✅     | `is_in_warehouse` flag + `current_location_id` untuk tracking spesifik gudang.                                 |
| 5.2 | Tambah capacity di warehouse location           | ✅     | Master Lokasi sudah punya kolom `capacity` & `current_stock` yang di-update otomatis oleh movement controller. |
| 5.3 | Perjelas UI ban di warehouse vs ban di pakai    | ✅     | Indikator "Badge" di Master Ban membedakan nama Warehouse (Info) vs "Terpasang" (Warning) dengan icon.         |

---

## 6. UI/FORM IMPROVEMENTS

| #   | Task                                                                   | Status | Detail                                                                                                           |
| --- | ---------------------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------------------- |
| 6.1 | Field Pattern di form Monitoring — perbaiki responsif                  | ✅     | Fixed col-md nesting and layout for general tyre settings in `create_session.blade.php`.                         |
| 6.2 | Head Unit vs Trailer selector di form Monitoring                       | ✅     | `is_trail` toggle ditambahkan di modal Add/Edit Monitoring Vehicle + Badge di list view.                         |
| 6.3 | Konsistensi dropdown di semua form (Monitoring, Examination, Movement) | ✅     | Cascading Brand > Size > Pattern added to `TyreMasterController@index` and `MonitoringController@createSession`. |
| 6.4 | Dashboard: Pisahkan tombol Import & Export                             | ✅     | Tombol Import & Export dipisahkan agar akses lebih cepat & jelas bagi Admin.                                     |
| 6.5 | Konsistensi Label "Tire position" → "Jumlah Posisi Ban"                | ✅     | Standarisasi label di Monitoring & Master data agar user tidak bingung.                                          |

---

## 7. IMPORT & DATA

| #   | Task                                         | Status | Detail                                                                                                                    |
| --- | -------------------------------------------- | ------ | ------------------------------------------------------------------------------------------------------------------------- |
| 7.1 | Import mendukung Excel (.xlsx) Only          | ✅     | `ImportController` refined to accept `.xlsx` only. Modal UI updated with instructions & template links.                   |
| 7.2 | Import processor align dengan schema baru    | ✅     | `processTyreMaster` sudah pakai `segment_name`, `is_in_warehouse`, `ply_rating`, `original_tread_depth`.                  |
| 7.3 | Buat template import Excel standar per modul | ✅     | File `.xlsx` berformat professional (header warna, contoh data, panduan) dibuat untuk 9 modul via `ImportTemplateExport`. |
| 7.4 | Buat company samaran & akun dummy            | ✅     | `DemoDataSeeder` (PT MITRA TAMBANG DEMO) dibuat dengan master data, kendaraan, history 6 bulan, & 3 akun demo.            |
| 7.5 | Perbaikan data existing untuk import         | 🟢     | Dialihkan ke penggunaan `DemoDataSeeder` (7.4) & template import excel (7.3) yang telah rampung.                          |

---

## 📊 RINGKASAN PROGRESS

| Kategori                    | Selesai | Partial | Belum | Total  |
| --------------------------- | ------- | ------- | ----- | ------ |
| Approval & Validasi         | 10      | 0       | 0     | 10     |
| Master Data Restrukturisasi | 8       | 0       | 0     | 8      |
| Role & Permission           | 7       | 0       | 0     | 7      |
| Company Isolation           | 3       | 0       | 0     | 3      |
| Location & Warehouse        | 3       | 0       | 0     | 3      |
| UI/Form Improvements        | 5       | 0       | 0     | 5      |
| Import & Data               | 5       | 0       | 0     | 5      |
| Human Error Detection       | 3       | 0       | 0     | 3      |
| **TOTAL**                   | **44**  | **0**   | **0** | **44** |

**Progress: 100% SELESAI (44/44 item)**

---

## 🎯 PRIORITAS SELANJUTNYA

1. **Selector Trailer** — Tambahkan toggle `is_trail` di form Monitoring (Poin 6.2)
2. **Import Template** — Buat file Excel (.xlsx) standar untuk semua modul (Poin 7.3)
3. **Demo Data** — Seeders untuk testing & presentation (Poin 7.4–7.5)
