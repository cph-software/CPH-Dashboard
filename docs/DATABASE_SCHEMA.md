# Database Schema — Tabel Inti Modul Tyre

> **Terakhir diverifikasi:** 31 Maret 2026 (langsung dari MySQL)

---

## Tabel `tyres` — Data Utama Ban

| Kolom | Tipe | Keterangan |
|:---|:---|:---|
| `id` | bigint (PK) | Primary key |
| `tyre_company_id` | bigint (FK) | → `tyre_companies.id` |
| `serial_number` | varchar | Nomor seri ban (unik, uppercase) |
| `custom_serial_number` | varchar | Nomor seri kustom (opsional) |
| `tyre_brand_id` | bigint (FK) | → `tyre_brands.id` |
| `tyre_size_id` | bigint (FK) | → `tyre_sizes.id` |
| `tyre_pattern_id` | bigint (FK) | → `tyre_patterns.id` |
| `ply_rating` | varchar | Rating ply ban |
| `original_tread_depth` | decimal | Kedalaman alur awal dari pabrik (OTD) |
| `is_in_warehouse` | boolean | `true` = di gudang, `false` = terpasang |
| `current_location_id` | bigint (FK) | → `tyre_locations.id` (gudang spesifik) |
| `segment_name` | varchar | Nama segment operasional (string, bukan FK) |
| `status` | varchar | New / Installed / Repaired / Retread / Scrap |
| `retread_count` | integer | Jumlah vulkanisir (R0, R1, R2, R3) |
| `price` | decimal | Harga beli (IDR) |
| `initial_tread_depth` | decimal | Ketebalan awal saat pertama kali didaftarkan |
| `current_tread_depth` | decimal | Ketebalan saat ini (diupdate oleh inspection) |
| `total_lifetime_km` | decimal | Total KM kumulatif selama hidup ban |
| `total_lifetime_hm` | decimal | Total HM kumulatif selama hidup ban |
| `current_km` | decimal | KM berjalan saat periode pemasangan saat ini |
| `current_hm` | decimal | HM berjalan saat periode pemasangan saat ini |
| `last_inspection_date` | date | Tanggal pemeriksaan terakhir |
| `current_vehicle_id` | bigint (FK) | → `master_import_kendaraan.id` (null = di gudang) |
| `current_position_id` | bigint (FK) | → `tyre_position_details.id` (null = di gudang) |
| `last_hm_reading` | decimal | Pembacaan hour meter terakhir |
| `created_by` | bigint | ID user yang membuat |
| `updated_by` | bigint | ID user yang terakhir mengubah |

> **PENTING:** Kolom `work_location_id` dan `tyre_segment_id` sudah **DIHAPUS** pada migrasi 23 Maret 2026.

---

## Tabel `tyre_movements` — Riwayat Pergerakan Ban

| Kolom | Tipe | Keterangan |
|:---|:---|:---|
| `id` | bigint (PK) | Primary key |
| `tyre_company_id` | bigint (FK) | → `tyre_companies.id` |
| `tyre_id` | bigint (FK) | → `tyres.id` |
| `vehicle_id` | bigint (FK) | → `master_import_kendaraan.id` |
| `position_id` | bigint (FK) | → `tyre_position_details.id` |
| `operational_segment_id` | bigint (FK) | → `tyre_segments.id` |
| `work_location_id` | bigint (FK) | → `tyre_locations.id` (**MASIH ADA di tabel ini**) |
| `work_location` | varchar | Nama lokasi (redundan untuk logging) |
| `movement_type` | varchar | Installation / Removal / Rotation / Inspection |
| `install_condition` | varchar | New / Spare / Repair |
| `is_replacement` | boolean | True jika menggantikan ban lain |
| `target_status` | varchar | Status target setelah pelepasan |
| `movement_date` | date | Tanggal pergerakan |
| `odometer_reading` | decimal | Pembacaan odometer saat transaksi |
| `running_km` | decimal | Delta KM sejak event sebelumnya |
| `hour_meter_reading` | decimal | Pembacaan hour meter |
| `running_hm` | decimal | Delta HM sejak event sebelumnya |
| `failure_code_id` | bigint (FK) | → `tyre_failure_codes.id` (hanya untuk Removal) |
| `rtd_reading` | decimal | Rata-rata RTD saat transaksi |
| `psi_reading` | decimal | Tekanan angin (PSI) |
| `notes` | text | Catatan transaksi |
| `created_by` | bigint | ID user yang membuat |

---

## Tabel `tyre_examination_details` — Detail Pemeriksaan Per Ban

| Kolom | Tipe | Keterangan |
|:---|:---|:---|
| `id` | bigint (PK) | Primary key |
| `tyre_company_id` | bigint (FK) | → `tyre_companies.id` |
| `examination_id` | bigint (FK) | → `tyre_examinations.id` |
| `position_id` | bigint (FK) | → `tyre_position_details.id` |
| `tyre_id` | bigint (FK) | → `tyres.id` |
| `psi_reading` | decimal | Tekanan angin |
| `rtd_1` ~ `rtd_4` | decimal | 4 titik pengukuran ketebalan alur |
| `remarks` | text | Catatan |

> **PENTING:** Kolom `serial_number` sudah **TIDAK ADA** di tabel ini.

---

## Tabel `tyre_monitoring_installation` — Ban Terpasang di Sesi Monitoring

| Kolom | Tipe | Keterangan |
|:---|:---|:---|
| `install_id` | bigint (PK) | Primary key |
| `session_id` | bigint (FK) | → `tyre_monitoring_session.session_id` |
| `tyre_company_id` | bigint (FK) | → `tyre_companies.id` |
| `position` | integer | Nomor urut posisi |
| `position_id` | bigint (FK) | → `tyre_position_details.id` |
| `serial_number` | varchar | Nomor seri ban (**MASIH ADA di tabel ini**) |
| `tyre_id` | bigint (FK) | → `tyres.id` |
| `brand` / `pattern` / `size` | varchar | Nama redundan untuk logging |
| `avg_rtd` | decimal | Rata-rata RTD saat install |
| `original_rtd` | decimal | RTD original/pabrik |
| `odometer_reading` | decimal | Odometer saat install |
| `hm_reading` | decimal | Hour meter saat install |

---

_Dokumen ini dibuat berdasarkan hasil scanning langsung kolom-kolom dari MySQL menggunakan `Schema::getColumnListing()`._
