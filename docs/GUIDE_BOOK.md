# 📖 Panduan Penggunaan Sistem CPH Tyre Dashboard

Dokumen ini berisi simulasi, panduan penggunaan (Guide Book), dan checklist kesiapan sistem untuk pengguna (Internal & Eksternal).

---

## 1. Simulasi Alur Kerja (Workflows)

Berikut adalah simulasi alur kerja harian yang disarankan untuk menjaga integritas data ban:

### A. Alur Registrasi Ban Baru

1. **Gudang (Internal):** Melakukan input data Ban di menu **Master Tyre**.
2. **Input:** Serial Number (SN), Brand, Size, Pattern, Lokasi, dan OTD (Original Tread Depth).
3. **Status:** Ban akan berstatus **"New"** dan siap dipasang.

### B. Alur Pemasangan Ban (Installation)

1. **Tyre Man / Admin:** Memilih unit yang akan dipasang ban di menu **Movement > Pemasangan**.
2. **Pilih Posisi:** Memilih posisi roda pada unit (misal: LF, RF, RRO).
3. **Pilih Ban:** Memilih SN Ban yang tersedia (status New/Repaired).
4. **Input Data:** Odometer unit, Jam mulai/selesai, RTD saat ini, dan kondisi install (New/Spare/Repair).
5. **Update Otomatis:** Sistem akan mengubah status ban menjadi **"Installed"** dan meng-update posisi ban di master unit tersebut.

### C. Alur Pemeriksaan Rutin (Examination)

1. **Tyre Man:** Melakukan pemeriksaan di lapangan (Site).
2. **Input:** Memilih unit di menu **Examination > Input Baru**.
3. **Pengukuran:** Mengukur tekanan ban (PSI) dan kedalaman alur (RTD 1-4) untuk **setiap posisi** yang terpasang.
4. **Foto:** Mengambil foto kondisi ban jika diperlukan (Fitur baru).
5. **Deteksi Human Error:** Jika Odometer lebih rendah dari catatan sebelumnya atau RTD meningkat tanpa perbaikan, sistem akan memberikan **Peringatan (Warning)**.

### D. Alur Pelepasan Ban (Removal)

1. **Admin / Supervisor:** Memilih unit di menu **Movement > Pelepasan**.
2. **Input:** Odometer pelepasan, alasan pelepasan (Failure Code), dan **Target Status** (Repaired atau Scrap).
3. **Update Otomatis:** Sistem akan menghitung Lifetime KM/HM ban tersebut dan mengeluarkannya dari unit.

---

## 2. Checklist Kesiapan Penggunaan (Role Internal & Eksternal)

Gunakan checklist ini untuk memverifikasi kesiapan setiap role sebelum sistem digunakan secara live.

### Sisi Akun Eksternal (Customer/Site)

| No  | Kategori | Checklist Kesiapan                                      |   PIC    |
| :-: | :------- | :------------------------------------------------------ | :------: |
|  1  | Akun     | Penunjukan minimal 1 Supervisor (Full Access Dashboard) | Customer |
|  2  | Akun     | Penunjukan minimal 2 Admin/Tyre Man (Input Data Harian) | Customer |
|  3  | Data     | Daftar Unit Kendaraan (No. Pol, Kode Unit, Layout Roda) | Customer |
|  4  | Data     | Daftar Lokasi Kerja (Site/Gudang Lokal)                 | Customer |
|  5  | SOP      | Kesepakatan jadwal pemeriksaan ban mingguan             | Customer |

### Sisi Akun Internal (GSI/CPH Admin)

| No  | Kategori    | Checklist Kesiapan                              |    PIC    |
| :-: | :---------- | :---------------------------------------------- | :-------: |
|  1  | Konfigurasi | Input Master Brand, Size, dan Pattern           | CPH Admin |
|  2  | Konfigurasi | Input Failure Code yang disepakati dengan GSI   | CPH Admin |
|  3  | Training    | Pelatihan cara input Examination & Movement     | Developer |
|  4  | Training    | Pelatihan cara membaca Dashboard & Export Data  | Developer |
|  5  | IT          | Hak akses Role Management sudah sesuai (3-Tier) | Developer |

---

## 3. Kewajiban & SOP End User (Customer)

Agar sistem berjalan optimal, Customer berkewajiban untuk:

1. **Penanggung Jawab Data:** Menunjuk PIC yang bertanggung jawab atas validitas data input (biasanya Supervisor Tyre).
2. **Disiplin Odometer:** Memastikan setiap unit yang masuk workshop/pemeriksaan dicatat Odometer/HM-nya secara akurat (tanpa pembulatan kasar).
3. **Laporan Kerusakan:** Setiap ban yang keluar/copot (Removal) **wajib** mencatatkan alasannya di sistem untuk keperluan Analisis Kerusakan (Failure Analysis).
4. **Akses Sistem:** Tidak diperkenankan membagi akun (Share account) kepada pihak yang tidak berkepentingan guna menjaga integritas _Activity Log_.

---

## 4. Panduan Deteksi Kesalahan (Human Error Detection)

Sistem telah dilengkapi fitur deteksi anomali untuk meminimalisir kesalahan input:

- **Pesan Error / Anomali:** Muncul saat menyimpan data jika ditemukan ketidakwajaran (Odometer/HM menurun atau RTD meningkat). Sistem akan **MEMBLOKIR (Tidak Menyimpan)** data tersebut hingga input diperbaiki.
- **Log Activity:** Setiap upaya input yang terdeteksi sebagai "Human Error" akan otomatis tercatat di log aktivitas (Audit Trail) untuk dievaluasi oleh Manager/Supervisor.
- **Data Mismatch:** Muncul di tabel dashboard jika ada inkonsistensi data historis yang perlu divalidasi ulang.

---

_Disusun oleh: Developer CPH Team_  
_Terakhir Diperbarui: 4 Maret 2026_
