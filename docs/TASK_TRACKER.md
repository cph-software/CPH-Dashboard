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
| 1.7 | Examination: Dropdown Input Mode (Sales/Customer) di form | ✅     | Ditambahkan di `create.blade.php`.                                                                                                                                                       |
| 1.8 | Data Monitoring: Hanya update master tyre jika Approved   | ✅     | Movement & tyre update hanya terjadi saat admin submit atau saat approve. Pending checks tidak mengubah data master.                                                                     |

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

## 2. MASTER DATA — RESTRUKTURISASI

| #   | Task                                                         | Status | Detail                                                                                                                                            |
| --- | ------------------------------------------------------------ | ------ | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2.1 | Hapus `tyre_segment_id` → ganti `segment_name` (string)      | ✅     | Kolom `tyre_segment_id` dihapus dari tabel `tyres`, diganti `segment_name`.                                                                       |
| 2.2 | Hapus `work_location_id` → ganti `is_in_warehouse` (boolean) | ✅     | Kolom `work_location_id` dihapus, diganti flag `is_in_warehouse`.                                                                                 |
| 2.3 | Tambah `ply_rating` di master tyre                           | ✅     | Kolom string, input manual di form.                                                                                                               |
| 2.4 | Tambah `original_tread_depth` di master tyre                 | ✅     | Kolom decimal, input manual (OTD).                                                                                                                |
| 2.5 | RTD/KM/HM tidak tampil di form input master tyre             | ✅     | Field ini dihitung otomatis dari movement/monitoring, bukan input manual. Kolom `total_lifetime_km/hm` tetap ada di DB untuk aggregasi dashboard. |
| 2.6 | Tambah `total_tyres` di master company                       | ✅     | Field di tabel `tyre_companies` + UI form add/edit. Menampilkan: Total Ban (Asset), Currently terdaftar, dan Limit (Quota).                       |
| 2.7 | Opsi Trail / Non-Trail di Position Layout                    | ✅     | Kolom `is_trail` ditambahkan di `tyre_position_configurations`.                                                                                   |

### File yang Diubah:

- `app/Http/Controllers/TyrePerformance/Master/TyreMasterController.php`
- `resources/views/tyre-performance/master/tyres/index.blade.php`
- `resources/views/tyre-performance/master/companies/index.blade.php`

### Migrasi Terkait:

- `2026_03_23_153440_restructure_tyre_tables_for_refined_logic`
- `2026_03_23_154518_add_total_tyres_to_tyre_companies`
- `2026_03_24_142829_restore_lifetime_columns_to_tyres`

---

## 3. ROLE & PERMISSION

| #   | Task                                                            | Status     | Detail                                                                                                    |
| --- | --------------------------------------------------------------- | ---------- | --------------------------------------------------------------------------------------------------------- |
| 3.1 | Admin (role_id=1) bypass semua permission check                 | ✅         | `CheckTyrePermission` middleware — admin langsung `return $next($request)`.                               |
| 3.2 | Sinkronisasi nama menu di route vs database                     | ✅         | `Tyre Monitoring` → `Monitoring`, `Position Layouts` → `Axle Layouts` di `web.php`.                       |
| 3.3 | Admin: Input Brand/Size/Pattern manual (select2-tags)           | ⚠️ Partial | Sudah ada di form Master Tyre (`index.blade.php`). **Belum diterapkan di form Monitoring & Examination.** |
| 3.4 | User: Dropdown pilih dari data existing (tidak bisa ketik baru) | ⚠️ Partial | Sudah ada di form Master Tyre. **Belum diterapkan di form Monitoring & Examination.**                     |
| 3.5 | Hierarki dropdown Brand → Size → Pattern                        | ⚠️ Partial | Sudah di Master Tyre form. **Belum di Monitoring & Examination form.**                                    |

### File yang Diubah:

- `app/Http/Middleware/CheckTyrePermission.php`
- `routes/web.php`

---

## 4. COMPANY ISOLATION (Data per Instansi)

| #   | Task                                               | Status | Detail                                                                                                                           |
| --- | -------------------------------------------------- | ------ | -------------------------------------------------------------------------------------------------------------------------------- |
| 4.1 | Filter kendaraan & ban per company user yang login | ❌     | Dropdown di form Monitoring/Examination/Movement belum filter otomatis berdasarkan `tyre_company_id` user. **Prioritas TINGGI.** |
| 4.2 | Whitelist data → ubah ke dropdown pilih            | ❌     | Konsep mapping data satu-satu per company → rencananya ubah jadi user pilih dari master global via dropdown. **Butuh diskusi.**  |
| 4.3 | Segment dropdown untuk user (bukan input bebas)    | ❌     | User seharusnya pilih dari daftar segment yang ada, bukan ketik manual.                                                          |

### Diskusi Terbuka:

> **Pertanyaan:** Apakah konsep whitelist/mapping di Master Company dihapus total, dan diganti user langsung pilih dari dropdown master global? Atau tetap ada pembatasan per company?

---

## 5. LOCATION & WAREHOUSE

| #   | Task                                            | Status | Detail                                                                                                     |
| --- | ----------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------------- |
| 5.1 | Pisahkan konsep Warehouse vs Operasional (Site) | ❌     | Saat ini `is_in_warehouse` hanya boolean per ban. Belum ada tracking lokasi warehouse spesifik + capacity. |
| 5.2 | Tambah capacity di warehouse location           | ❌     | Warehouse perlu kolom `capacity` untuk tracking berapa ban bisa ditampung.                                 |
| 5.3 | Perjelas UI ban di warehouse vs ban di pakai    | ❌     | Tampilan master tyre belum visual membedakan ban yang di-stock vs yang terpasang di unit.                  |

### Diskusi Terbuka:

> **Pertanyaan:** Capacity ini per lokasi warehouse, atau per company? Dan apakah lokasi warehouse bisa lebih dari satu per company?

---

## 6. UI/FORM IMPROVEMENTS

| #   | Task                                                                   | Status | Detail                                                                                          |
| --- | ---------------------------------------------------------------------- | ------ | ----------------------------------------------------------------------------------------------- |
| 6.1 | Field Pattern di form Monitoring — perbaiki responsif                  | ❌     | Layout belum optimal di mobile/tablet.                                                          |
| 6.2 | Head Unit vs Trailer selector di form Monitoring                       | ❌     | `is_trail` sudah ada di DB tapi belum ada UI untuk memilih.                                     |
| 6.3 | Konsistensi dropdown di semua form (Monitoring, Examination, Movement) | ❌     | Brand/Size/Pattern/Segment harus konsisten cascading di semua form, bukan hanya di master tyre. |

---

## 7. IMPORT & DATA

| #   | Task                                         | Status | Detail                                                                                                   |
| --- | -------------------------------------------- | ------ | -------------------------------------------------------------------------------------------------------- |
| 7.1 | Import mendukung Excel (.xlsx/.xls) + CSV    | ✅     | `ImportController@storeCSV` — menggunakan `Maatwebsite/Excel`.                                           |
| 7.2 | Import processor align dengan schema baru    | ✅     | `processTyreMaster` sudah pakai `segment_name`, `is_in_warehouse`, `ply_rating`, `original_tread_depth`. |
| 7.3 | Buat template import Excel standar per modul | ❌     | Template file `.xlsx` belum dibuat untuk: Tyre Master, Vehicle Master, Movement History, Examination.    |
| 7.4 | Buat company samaran & akun dummy            | ❌     | Seeder untuk data demo belum dibuat.                                                                     |
| 7.5 | Perbaikan data existing untuk import         | ❌     | Belum ada script/tool untuk cleaning & preparing data real.                                              |

---

## 8. HUMAN ERROR DETECTION

| #   | Task                             | Status | Detail                                                                                                                  |
| --- | -------------------------------- | ------ | ----------------------------------------------------------------------------------------------------------------------- |
| 8.1 | Deteksi odometer reset/anomali   | ✅     | Di `TyreExaminationController@store` — ada opsi "Meter Reset" centang. Jika tidak dicentang tapi angka turun → warning. |
| 8.2 | Deteksi HM reset/anomali         | ✅     | Sama seperti odometer, ada validasi untuk hour meter.                                                                   |
| 8.3 | RTD tidak boleh melebihi initial | ✅     | Validasi di monitoring check.                                                                                           |

---

## 📊 RINGKASAN PROGRESS

| Kategori                    | Selesai | Partial | Belum  | Total  |
| --------------------------- | ------- | ------- | ------ | ------ |
| Approval & Validasi         | 8       | 0       | 0      | 8      |
| Master Data Restrukturisasi | 7       | 0       | 0      | 7      |
| Role & Permission           | 2       | 3       | 0      | 5      |
| Company Isolation           | 0       | 0       | 3      | 3      |
| Location & Warehouse        | 0       | 0       | 3      | 3      |
| UI/Form Improvements        | 0       | 0       | 3      | 3      |
| Import & Data               | 2       | 0       | 3      | 5      |
| Human Error Detection       | 3       | 0       | 0      | 3      |
| **TOTAL**                   | **22**  | **3**   | **12** | **37** |

**Progress: ~67% selesai (25/37 item)**

---

## 🎯 PRIORITAS SELANJUTNYA (Rekomendasi)

### 🔴 Prioritas Tinggi

1. **Company Isolation** — Filter data per company di semua form (Poin 4.1)
2. **Konsistensi Dropdown** — Brand > Size > Pattern di semua form (Poin 3.3–3.5, 6.3)
3. **Whitelist → Dropdown** — Simplifikasi konsep mapping (Poin 4.2)

### 🟠 Prioritas Sedang

4. **Location & Warehouse** — Pisahkan konsep + capacity (Poin 5.1–5.3)
5. **Head Unit vs Trailer UI** — Selector di form monitoring (Poin 6.2)
6. **Form Responsif** — Pattern field di monitoring (Poin 6.1)

### 🟢 Prioritas Rendah

7. **Import Template** — File template standar (Poin 7.3)
8. **Data Demo** — Company samaran + akun dummy (Poin 7.4)
9. **Data Cleaning** — Persiapan import data real (Poin 7.5)

---

## 📝 CATATAN DISKUSI TERBUKA

### A. Pattern & Size di Master Config

- **Opsi A:** Gabung jadi 1 tabel `tyre_configurations` (brand+size+pattern = 1 record)
- **Opsi B (Rekomendasi):** Tetap 3 tabel terpisah, UI cascading (Brand → Size → Pattern)
- **Status:** Menunggu keputusan

### B. Company Data Strategy

- Saat ini ada mapping/whitelist per company
- Rencana ubah: User pilih dari master global via dropdown
- Data operasional (ban, kendaraan) tetap terisolasi per company
- **Status:** Menunggu keputusan

### C. Location Architecture

- Warehouse: punya capacity, untuk stok ban
- Site/Operational: tempat kendaraan beroperasi
- Ban di-remove → masuk warehouse, di-install → keluar warehouse
- Capacity per warehouse atau per company?
- **Status:** Menunggu keputusan

---

## 🔧 MIGRASI DATABASE YANG SUDAH DIJALANKAN

| Migrasi                                                       | Deskripsi                                                                                                                                                                  | Batch |
| ------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----- |
| `2026_03_23_153440_restructure_tyre_tables_for_refined_logic` | Tambah ply_rating, original_tread_depth, is_in_warehouse, segment_name. Hapus tyre_segment_id, work_location_id, total_lifetime_km/hm. Tambah is_trail di position config. | 106   |
| `2026_03_23_153927_add_approval_status_to_monitoring_checks`  | Tambah approval_status, approved_by, approved_at, reject_reason di monitoring check.                                                                                       | 107   |
| `2026_03_23_154518_add_total_tyres_to_tyre_companies`         | Tambah total_tyres di tyre_companies.                                                                                                                                      | 108   |
| `2026_03_23_155552_add_approval_columns_to_tyre_examinations` | Tambah exam_type, approval_status, approved_by, reject_reason di tyre_examinations.                                                                                        | 109   |
| `2026_03_24_142829_restore_lifetime_columns_to_tyres`         | Restore total_lifetime_km, total_lifetime_hm, current_km, current_hm (dibutuhkan dashboard).                                                                               | 110   |

---

## 📁 FILE KUNCI YANG DIMODIFIKASI

### Controllers

- `app/Http/Controllers/TyrePerformance/Monitoring/MonitoringController.php`
- `app/Http/Controllers/TyrePerformance/Examination/TyreExaminationController.php`
- `app/Http/Controllers/TyrePerformance/Master/TyreMasterController.php`
- `app/Http/Controllers/UserManagement/ImportController.php`
- `app/Http/Controllers/UserManagement/ImportApprovalController.php`

### Middleware

- `app/Http/Middleware/CheckTyrePermission.php`

### Views

- `resources/views/tyre-performance/examination/create.blade.php`
- `resources/views/tyre-performance/examination/show.blade.php`
- `resources/views/tyre-performance/examination/index.blade.php`
- `resources/views/tyre-performance/master/tyres/index.blade.php`
- `resources/views/tyre-performance/master/companies/index.blade.php`

### Routes

- `routes/web.php`

### Models

- `app/Models/TyreMonitoringCheck.php`
- `app/Models/TyreExamination.php`
