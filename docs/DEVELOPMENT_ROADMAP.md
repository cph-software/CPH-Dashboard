# 🚀 Project Development Roadmap: CPH Tyre Dashboard

Dokumen ini berfungsi sebagai acuan utama status pengerjaan fitur berdasarkan masukan user dan kebutuhan teknis GSI.

## 📌 Update Terakhir: 20 Februari 2026

**Status Utama:** Phase 1 (Core Tyre) 🟢 Selesai | Phase 2 (Financial & Workflow) 🟡 Planning

---

## 🛠️ Modul 1: Export/Import & Data Verification

**Tujuan:** Memberikan fleksibilitas bagi semua user untuk mengelola data via Excel/CSV.

- [ ] **Global Access:** Mengaktifkan izin Export/Import untuk seluruh user aplikasi CPH.
- [ ] **Raw Data Export:** Fitur download data mentah dari Dashboard, Failure Analysis, dan Movement untuk verifikasi manual.
- [ ] **Import Engine:** Implementasi Laravel Excel untuk upload data masal.
- [ ] **Approval Workflow:**
    - Admin melakukan upload data ke tabel "Temporary".
    - Data tidak langsung masuk ke Master.
    - Level Manajerial/Supervisor memberikan "Approval" baru data dipindahkan ke Master.

---

## 💰 Modul 2: Business Workflow (BA, PO, & Invoicing)

**Tujuan:** Tracking proses penagihan ban consignment dan pemantauan piutang.

### A. Alur Kerja (Workflow)

1. **Alur Internal (GSI/Vendor):** `BA (Berita Acara)` ➔ `PO` ➔ `INVOICE`
2. **Alur Customer:** `PO` ➔ `BA` ➔ `INVOICE`

### B. Fitur Utama

- [ ] **BA Management:** Form upload dokumen BA, nomor BA, dan link ke SN Ban yang digunakan.
- [ ] **Invoice Tracking:**
    - Monitor Invoice terbit.
    - Perhitungan otomatis **Overdue** (Usia invoice dari jatuh tempo).
- [ ] **Invoicing Milestone:**
    - [ ] `Items Delivery Date` (Ban Sampai)
    - [ ] `BA Date` (Berita Acara)
    - [ ] `Invoicing Date` (Invoice Terbit)
    - [ ] `Document Received Date` (Dokumen diterima customer)

---

## ⏱️ Modul 3: Lead Time & Performance Analytics

**Tujuan:** Menganalisa kecepatan setiap proses dalam workflow.

- [ ] **Milestone Gap Analysis:** Menghitung durasi antara setiap tanggal milestone (Delivery ➔ BA, BA ➔ Inv, dst).
- [ ] **Manajerial Dashboard:**
    - [ ] Log Import/Export summary.
    - [ ] All Dashboard Data view.
    - [ ] Split views: `Tyre History`, `Lead Time`, `AR History`, `All Log History`.

---

## 🔍 Modul 4: Activity Tracking (Audit Trail)

**Tujuan:** Mencatat setiap aktivitas krusial untuk keamanan data.

- [ ] **Detailed Edit Log:** Mencatat nilai lama (Old Value) dan nilai baru (New Value) saat terjadi perubahan data.
- [ ] **User Action Log:** Tracking siapa yang melakukan Export, Import, dan pengajuan Approval.

---

## 📊 Progress Checklist

| Fitur                               | Status | Keterangan         |
| :---------------------------------- | :----: | :----------------- |
| Core Tyre Movement (Install/Remove) |   🟢   | Done               |
| Tyre Dashboard KPI                  |   🟢   | Done               |
| Account Manajerial, Spv, Admin      |   🟢   | Done (Placeholder) |
| Struktur Database BA & Invoice      |   🔴   | Next Task          |
| Lead Time Tracking Logic            |   🔴   | Waiting DB         |
| Approval System for Import          |   🔴   | Waiting Logic      |
| Export/Import Excel                 |   🔴   | Planned            |

---

## 📝 Catatan Tambahan (Requirement Khusus)

1. **Penting:** Semua user CPH Dashboard harus bisa Export/Import data.
2. **Penting:** Fitur approval diperlukan khusus untuk aksi "Import Request Data" oleh Admin.
3. **Penting:** Perhitungan Lead Time harus mencakup 4 titik tanggal utama (Delivery, BA, Invoicing, Doc Received).
