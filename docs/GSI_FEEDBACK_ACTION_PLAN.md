# 📋 Action Plan — Masukan GSI (Pak Agus)

> **Tanggal Masukan**: 2 Februari 2026 & 12 Februari 2026  
> **Disusun oleh**: Tim Development CPH Dashboard  
> **Tanggal Dokumen**: 13 Februari 2026  
> **Status**: Draft — Menunggu Review & Approval

---

## Daftar Isi

1. [Ringkasan Eksekutif](#ringkasan-eksekutif)
2. [Matriks Prioritas](#matriks-prioritas)
3. [Detail Per Item](#detail-per-item)
    - [ITEM-3: Kondisi Pemasangan Tyre (New / Spare / Repair)](#item-3-kondisi-pemasangan-tyre)
    - [ITEM-2: Nama Failure/Damage Adjustable per Customer](#item-2-nama-failuredamage-adjustable-per-customer)
    - [ITEM-1: Potensi HM/KM Minus (Reset Odometer)](#item-1-potensi-hmkm-minus-reset-odometer)
    - [ITEM-5: Overview Data (Ban/Habit & Customer Based)](#item-5-overview-data-banhabit--customer-based)
    - [ITEM-4: Claim/Warranty Parameter dari Pabrik](#item-4-claimwarranty-parameter-dari-pabrik)
4. [Timeline Pengerjaan](#timeline-pengerjaan)
5. [Catatan & Dependensi](#catatan--dependensi)

---

## Ringkasan Eksekutif

Terdapat **5 poin masukan** dari GSI (Pak Agus) terkait modul **Tyre Performance Dashboard**. Masukan ini mencakup perbaikan logika perhitungan, penyederhanaan input, fleksibilitas penamaan, dan permintaan fitur baru (overview/report & warranty).

Dokumen ini menyusun kelima item berdasarkan:

- **Kemudahan pengerjaan** (Mudah → Sulit)
- **Tingkat urgensi** (seberapa kritis terhadap akurasi data & kebutuhan user)

---

## Matriks Prioritas

| Urutan | Item   | Deskripsi Singkat                           |  Difficulty  |   Urgensi   | Estimasi |
| :----: | ------ | ------------------------------------------- | :----------: | :---------: | :------: |
|  🥇 1  | ITEM-3 | Kondisi tyre saat pasang (New/Spare/Repair) |   ⭐ Mudah   |  🔴 Tinggi  | 1-2 jam  |
|  🥈 2  | ITEM-2 | Nama failure/damage adjustable per customer | ⭐⭐ Sedang  |  🟡 Medium  | 3-5 jam  |
|  🥉 3  | ITEM-1 | Handle HM/KM minus (reset odometer)         | ⭐⭐ Sedang  |  🔴 Tinggi  | 4-6 jam  |
|   4    | ITEM-5 | Overview data (rute/tonase/driver/brand)    | ⭐⭐⭐ Sulit |  🟡 Medium  | 2-4 hari |
|   5    | ITEM-4 | Claim/warranty parameter                    | ⭐⭐⭐ Sulit | 🔵 Rendah\* | 3-5 hari |

> _\*ITEM-4 rendah bukan karena tidak penting, tapi karena **BLOCKED** — menunggu data parameter dari pihak principal/pabrik ban._

### Penjelasan Kriteria Urutan:

```
Prioritas = (Urgensi × 2) + (1 / Difficulty)
```

- **ITEM-3** paling prioritas karena mudah + urgen (langsung mempengaruhi akurasi pencatatan)
- **ITEM-1** sangat urgen (data lifetime bisa salah) tapi effort-nya lebih tinggi
- **ITEM-4** di-hold karena ada dependensi eksternal

---

## Detail Per Item

---

### ITEM-3: Kondisi Pemasangan Tyre

**`New / Spare / Repair`**

| Aspek          | Detail             |
| -------------- | ------------------ |
| **Prioritas**  | 🥇 #1              |
| **Difficulty** | ⭐ Mudah           |
| **Urgensi**    | 🔴 Tinggi          |
| **Estimasi**   | 1-2 jam            |
| **Tipe**       | Fitur Baru (Minor) |

#### Masukan Asli

> _"Kondisi pemasangan tyre dibuat lebih simple: new, spare, repair"_  
> _Respon internal: "bisa di-absorb, status dibedakan antara install dan replace"_

#### Konteks & Analisis

Saat ini form pemasangan ban **tidak memiliki field** untuk membedakan kondisi ban saat dipasang. Status di tabel `tyres` hanya berubah ke `Installed` tanpa informasi apakah ban tersebut baru, bekas (spare), atau hasil repair.

**Perbedaan konsep:**
| Kondisi | Penjelasan |
|---------|------------|
| **New** | Ban baru, pertama kali dipasang, belum pernah digunakan |
| **Spare** | Ban cadangan, sudah pernah dipakai di unit lain, sekarang dipasang ulang |
| **Repair** | Ban hasil perbaikan/vulkanisir/retread, dipasang kembali |

**Mengapa penting:**

- Membedakan performa ban baru vs repair vs spare
- Input untuk analisa CPK (Cost Per KM) — ban baru vs retread
- Dasar data untuk keputusan: apakah lebih cost-effective beli baru atau repair?

#### Kondisi Saat Ini di Kode

```
📁 Database: tyres table
   └── status ENUM('Installed', 'New', 'Scrap', 'Repaired')  ← hanya status tyre saat ini

📁 Database: tyre_movements table
   └── movement_type ENUM('Installation', 'Removal', 'Rotation', 'Inspection')
   └── ❌ TIDAK ADA field kondisi saat pasang

📁 View: pemasangan.blade.php
   └── ❌ TIDAK ADA dropdown kondisi ban
```

#### Yang Harus Dilakukan

**A. Database Migration**

```php
// Tambah field tyre_condition di tyre_movements
Schema::table('tyre_movements', function (Blueprint $table) {
    $table->enum('tyre_condition', ['New', 'Spare', 'Repair'])
          ->nullable()
          ->after('movement_type');
});
```

**B. Form Pemasangan (pemasangan.blade.php)**

- Tambah dropdown `<select name="tyre_condition">` dengan opsi: New, Spare, Repair
- Letakkan di bawah field "Pilih Ban (SN)"

**C. Controller (TyreMovementController.php)**

- Tambah validasi: `'tyre_condition' => 'nullable|in:New,Spare,Repair'`
- Simpan ke `TyreMovement::create([... 'tyre_condition' => $request->tyre_condition ...])`

**D. History/Report**

- Tampilkan kolom kondisi di tabel history movement

#### Crosscheck

- [x] Apakah enum `New, Spare, Repair` sudah final? → **Konfirmasi ke Pak Agus**
- [ ] Apakah kondisi ini juga berlaku untuk Pelepasan? → Biasanya tidak, hanya saat Install

---

### ITEM-2: Nama Failure/Damage Adjustable per Customer

| Aspek          | Detail      |
| -------------- | ----------- |
| **Prioritas**  | 🥈 #2       |
| **Difficulty** | ⭐⭐ Sedang |
| **Urgensi**    | 🟡 Medium   |
| **Estimasi**   | 3-5 jam     |
| **Tipe**       | Enhancement |

#### Masukan Asli

> _"Penyebutan nama tyre failure/damage disepakati dengan site/user"_  
> _Respon internal: "sifatnya adjustable/per cust dibuat/fasilitasi dengan istilahnya masing2 (membingungkan)"_

#### Konteks & Analisis

Setiap site/customer punya istilah sendiri untuk kerusakan ban. Contoh:

| Kode Standar | Site A           | Site B        | Site C          |
| ------------ | ---------------- | ------------- | --------------- |
| TS           | Tread Separation | Telapak Lepas | Tread Copot     |
| SB           | Sidewall Bulge   | Kembung       | Benjol Samping  |
| CP           | Crown Puncture   | Tertusuk      | Tusukan Mahkota |

Jika nama tidak familiar bagi user lapangan, bisa membingungkan dan menyebabkan salah input.

#### Kondisi Saat Ini di Kode

```
📁 Database: tyre_failure_codes
   ├── failure_code     (VARCHAR) → Kode standar, contoh: "TS"
   ├── failure_name     (VARCHAR) → Nama standar, contoh: "Tread Separation"
   ├── description      (TEXT)    → Deskripsi detail
   ├── recommendations  (TEXT)    → Rekomendasi tindakan
   ├── default_category (ENUM)    → Scrap / Repair / Claim
   ├── image_1, image_2 (VARCHAR) → Foto referensi
   └── ❌ TIDAK ADA alias/display_name per customer

📁 View: pemasangan.blade.php (Remarks Dropdown)
   └── ⚠️ HARDCODED: Pasang, Pindah, Lepas, Tergores, Kembung, Pecah, Sobek, Tertusuk, Telapak Lepas
```

**Masalah Utama:**

1. Nama failure code di database bersifat global, tidak bisa berbeda per site
2. Dropdown "Remarks" di form pemasangan **hardcoded** — tidak dari database

#### Yang Harus Dilakukan

**Opsi A — Simple (Recommended untuk sekarang):**
Jika sistem ini **single-tenant** (satu instance = satu customer), cukup:

1. Tambah field `display_name` di `tyre_failure_codes`:
    ```php
    $table->string('display_name')->nullable()->after('failure_name');
    // Jika diisi, tampilkan display_name. Jika kosong, fallback ke failure_name.
    ```
2. Buat tampilan di dropdown: `display_name ?? failure_name`
3. Admin bisa edit `display_name` sesuai istilah site masing-masing

**Opsi B — Multi-tenant (Jika diperlukan nanti):**

1. Buat tabel `tyre_failure_code_aliases`:
    ```
    id | failure_code_id | location_id | alias_name
    ```
2. Setiap lokasi bisa punya nama alias berbeda untuk failure code yang sama

**Perbaikan Remarks Dropdown:**

1. ~~Hardcoded~~ → Pindahkan ke tabel master atau ambil dari `tyre_failure_codes`
2. Buat tabel `tyre_remark_options` jika diperlukan terpisah dari failure codes

#### Crosscheck

- [ ] Konfirmasi ke setiap site, kumpulkan istilah yang mereka gunakan
- [ ] Tentukan apakah Opsi A (simple) sudah cukup atau perlu Opsi B (multi-tenant)
- [ ] Apakah dropdown "Remarks" harus masih terpisah dari Failure Codes?

---

### ITEM-1: Potensi HM/KM Minus (Reset Odometer)

| Aspek          | Detail                      |
| -------------- | --------------------------- |
| **Prioritas**  | 🥉 #3                       |
| **Difficulty** | ⭐⭐ Sedang                 |
| **Urgensi**    | 🔴 Tinggi                   |
| **Estimasi**   | 4-6 jam                     |
| **Tipe**       | Bug Fix / Logic Enhancement |

#### Masukan Asli

> _"Terkait plant dashboard performance tyre ada potensi perubahan HM/KM baru. (Bisa menimbulkan hasil minus)"_  
> _Respon internal: "#1 dari penggantian unit odometer/panel/electricity"_

#### Konteks & Analisis

**Skenario masalah:**

```
1. Ban dipasang di unit ABC pada KM 50.000
2. Unit ABC ganti panel/odometer → KM reset ke 0
3. Ban dilepas dari unit ABC pada KM 2.000
4. Perhitungan: 2.000 - 50.000 = -48.000 ← MINUS!
```

Saat ini, kode di controller menghandle ini dengan:

```php
if ($diff > 0) $kmDiff = $diff;  // Jika minus, dianggap 0
```

**Impact:** Total lifetime ban jadi **tidak akurat** — periode tersebut dianggap 0 KM padahal ban sudah berjalan 50.000 KM+ sebelum reset.

#### Kondisi Saat Ini di Kode

```
📁 TyreMovementController.php (Line 250-265)
   └── Removal logic:
       ├── Ambil last Installation movement
       ├── Hitung diff = odometer_lepas - odometer_pasang
       ├── if (diff > 0) → simpan sebagai lifetime
       └── if (diff <= 0) → diabaikan (0 KM) ← ⚠️ DATA HILANG

📁 Database: tyre_movements
   ├── odometer_reading (DECIMAL)
   ├── hour_meter_reading (DECIMAL)
   └── ❌ TIDAK ADA field untuk flag reset atau adjusted value
```

#### Yang Harus Dilakukan

**A. Database Migration**

```php
Schema::table('tyre_movements', function (Blueprint $table) {
    $table->boolean('is_odometer_reset')->default(false)->after('hour_meter_reading');
    $table->decimal('adjusted_km', 15, 2)->nullable()->after('is_odometer_reset');
    $table->decimal('adjusted_hm', 15, 2)->nullable()->after('adjusted_km');
});
```

| Field               | Tipe    | Penjelasan                                          |
| ------------------- | ------- | --------------------------------------------------- |
| `is_odometer_reset` | Boolean | Flag bahwa unit mengalami reset odometer/panel      |
| `adjusted_km`       | Decimal | KM manual yang diinput user sebagai estimasi aktual |
| `adjusted_hm`       | Decimal | HM manual yang diinput user sebagai estimasi aktual |

**B. Form Pelepasan (pelepasan.blade.php)**
Tambahkan UI:

```
┌─────────────────────────────────────────────────────┐
│ ⚠️ Apakah unit ini mengalami reset odometer/panel?  │
│ [ ] Ya, odometer/HM telah di-reset                  │
│                                                      │
│ (Jika dicentang, muncul:)                           │
│ Estimasi KM aktual: [________]                      │
│ Estimasi HM aktual: [________]                      │
│ Catatan: [________________________________]         │
└─────────────────────────────────────────────────────┘
```

**C. Controller Logic Update**

```php
// Di bagian Removal:
if ($lastInstallation) {
    if ($request->is_odometer_reset) {
        // Gunakan nilai adjusted yang diinput manual
        $kmDiff = $request->adjusted_km ?? 0;
        $hmDiff = $request->adjusted_hm ?? 0;
    } else {
        // Hitung normal
        $diff = $request->odometer - $lastInstallation->odometer_reading;
        $kmDiff = ($diff > 0) ? $diff : 0;
        // ... sama untuk HM
    }
}
```

**D. Deteksi Otomatis (Nice to Have)**
Tambahkan warning otomatis jika sistem mendeteksi `odometer_lepas < odometer_pasang`:

```
⚠️ "KM saat pelepasan (2.000) lebih kecil dari KM saat pemasangan (50.000).
    Apakah unit ini mengalami reset odometer?"
    [Ya, centang reset] [Tidak, perbaiki input]
```

#### Crosscheck

- [ ] Apakah ada histori unit yang sudah pernah reset di data sekarang?
- [ ] Bagaimana cara site mendapatkan estimasi KM aktual? (catatan manual, GPS, dll)
- [ ] Apakah perlu log event "Odometer Reset" sebagai movement_type terpisah?

---

### ITEM-5: Overview Data (Ban/Habit & Customer Based)

| Aspek          | Detail             |
| -------------- | ------------------ |
| **Prioritas**  | #4                 |
| **Difficulty** | ⭐⭐⭐ Sulit       |
| **Urgensi**    | 🟡 Medium          |
| **Estimasi**   | 2-4 hari           |
| **Tipe**       | Fitur Baru (Major) |

#### Masukan Asli

> _"ban or habit (rute/tonase/karakter tonase or muatan/skill driver/brand)"_  
> _"customer based (tonase/volume)"_  
> _"SDH hampir tercapture sih. Cm by driver ini yg musingkeun. Tp kalau ambil by unit number sama saja"_

#### Konteks & Analisis

Customer ingin sebuah **dashboard overview** yang bisa menampilkan data performa ban berdasarkan berbagai dimensi:

| Dimensi             | Yang Dimaksud               | Contoh Insight                                           |
| ------------------- | --------------------------- | -------------------------------------------------------- |
| **Rute**            | Jalur operasional unit      | "Ban di rute tambang lebih cepat aus vs rute jalan raya" |
| **Tonase**          | Kapasitas muatan kendaraan  | "Ban di unit 40 ton aus lebih cepat dari unit 20 ton"    |
| **Karakter Muatan** | Jenis barang yang diangkut  | "Muatan batu bara vs muatan tanah → beda impact ke ban"  |
| **Skill Driver**    | Performa driver terkait ban | "Unit driver A rusak ban 3x, driver B hanya 1x"          |
| **Brand**           | Merk ban                    | "Bridgestone tahan 80.000 KM, Merk X hanya 50.000 KM"    |
| **Tonase/Volume**   | Based per customer          | "Customer A (hauling) vs Customer B (logistik)"          |

**Catatan Pak Agus:** Driver bisa diwakili oleh Unit Number, karena driver biasanya tetap di satu unit.

#### Kondisi Saat Ini — Ketersediaan Data

| Data Point           | Status     | Lokasi di Database                                        |
| -------------------- | ---------- | --------------------------------------------------------- |
| Brand ban            | ✅ Ada     | `tyres.tyre_brand_id` → `tyre_brands`                     |
| Pattern ban          | ✅ Ada     | `tyres.tyre_pattern_id` → `tyre_patterns`                 |
| Size ban             | ✅ Ada     | `tyres.tyre_size_id` → `tyre_sizes`                       |
| Unit/Kendaraan       | ✅ Ada     | `tyre_movements.vehicle_id` → `master_import_kendaraan`   |
| Segment operasional  | ✅ Ada     | `tyre_movements.operational_segment_id` → `tyre_segments` |
| Lokasi/Site          | ✅ Ada     | `tyre_movements.work_location_id` → `tyre_locations`      |
| Lifetime KM/HM       | ✅ Ada     | `tyres.total_lifetime_km`, `total_lifetime_hm`            |
| RTD/Tread Depth      | ✅ Ada     | `tyres.current_tread_depth`, `tyre_movements.rtd_reading` |
| Harga ban            | ✅ Ada     | `tyres.price`                                             |
| Retread count        | ✅ Ada     | `tyres.retread_count`                                     |
| **Tonase/Kapasitas** | ❌ Belum   | Tidak ada di `master_import_kendaraan`                    |
| **Rute detail**      | ⚠️ Partial | `tyre_segments` ada, tapi bukan rute spesifik             |
| **Karakter muatan**  | ❌ Belum   | Tidak ada field jenis muatan                              |
| **Driver**           | ❌ Belum   | Tidak ada relasi driver, tapi bisa by unit number         |

#### Yang Harus Dilakukan

**Fase 1 — Tambah Data Master (Database)**

```php
// Migration: tambah field di master_import_kendaraan
Schema::table('master_import_kendaraan', function (Blueprint $table) {
    $table->decimal('tonnage_capacity', 10, 2)->nullable();     // Kapasitas tonase
    $table->decimal('volume_capacity', 10, 2)->nullable();      // Kapasitas volume
    $table->string('load_type')->nullable();                     // Jenis muatan (batu bara, tanah, dll)
    $table->string('primary_route')->nullable();                 // Rute utama operasional
});
```

**Fase 2 — Build Dashboard Overview Page**

Buat halaman baru: **Tyre Performance Overview / Analytics**

```
📊 Dashboard Sections:

1. Performance by Brand
   ├── Average Lifetime KM per Brand
   ├── Average CPK (Cost Per KM) per Brand
   └── Failure Rate per Brand

2. Performance by Segment/Route
   ├── Average Lifetime per Segment
   ├── Failure distribution per Segment
   └── Comparison chart

3. Performance by Vehicle/Tonnage
   ├── Scatter plot: Tonnage vs Tyre Lifetime
   ├── Group by tonnage range
   └── Top 10 unit paling boros ban

4. Performance by Load Type
   ├── Average lifetime per load type
   └── Failure pattern per load type

5. Tyre Lifecycle Summary
   ├── New vs Repair vs Spare distribution
   ├── Average retread cycles
   └── Scrap rate analysis
```

**Fase 3 — API/Query Layer**

Buat controller khusus: `TyreAnalyticsController.php`

- Endpoint untuk setiap section dashboard
- Support filter by: date range, location, brand, segment
- Export ke Excel/PDF (optional)

#### Crosscheck

- [ ] Konfirmasi ke Pak Agus: dimensi mana yang paling prioritas untuk dashboard pertama?
- [ ] Data tonase/volume — apakah sudah ada di dokumen/spreadsheet yang bisa diimport?
- [ ] Apakah perlu drill-down (klik chart → lihat detail per ban)?

---

### ITEM-4: Claim/Warranty Parameter dari Pabrik

| Aspek          | Detail                                               |
| -------------- | ---------------------------------------------------- |
| **Prioritas**  | #5                                                   |
| **Difficulty** | ⭐⭐⭐ Sulit                                         |
| **Urgensi**    | 🔵 Rendah (BLOCKED)                                  |
| **Estimasi**   | 3-5 hari                                             |
| **Tipe**       | Fitur Baru (Major)                                   |
| **Blocker**    | ⛔ Menunggu data parameter dari principal/pabrik ban |

#### Masukan Asli

> _"Claim/warranty: share parameter yang diberikan oleh pabrik"_  
> _Respon internal: "#4 harus discuss dengan pihak principal"_

#### Konteks & Analisis

Untuk bisa membuat fitur claim/warranty otomatis, sistem perlu tahu **parameter garansi** dari pabrik ban. Contoh parameter yang biasanya diberikan:

| Parameter           | Contoh             | Penjelasan                                        |
| ------------------- | ------------------ | ------------------------------------------------- |
| Minimum Lifetime KM | 60.000 KM          | Jika ban rusak sebelum 60K KM, eligible claim     |
| Minimum Lifetime HM | 3.000 HM           | Sama tapi dalam Hour Meter                        |
| Maximum Age         | 24 bulan           | Garansi berlaku maks 2 tahun dari tanggal beli    |
| Minimum Tread Depth | 4mm                | Ban harus masih di atas 4mm saat claim            |
| Claimable Failures  | TS, SB, MF         | Hanya jenis kerusakan tertentu yang bisa di-claim |
| Excluded Conditions | Overload, Accident | Kondisi yang membatalkan garansi                  |
| Claim Percentage    | Prorata by KM      | Nilai claim berdasarkan % sisa lifetime           |

#### Kondisi Saat Ini di Kode

```
📁 Database: tyre_failure_codes
   └── default_category ENUM('Scrap', 'Repair', 'Claim')  ← Sudah ada kategori "Claim"

📁 Database: tyres
   ├── price (DECIMAL)           ← Harga ban sudah ada
   ├── total_lifetime_km         ← Lifetime KM sudah ada
   └── ❌ TIDAK ADA: purchase_date, warranty_expiry

📁 Fitur:
   └── ❌ TIDAK ADA halaman/modul claim management
```

#### Yang Harus Dilakukan (Setelah Data Tersedia)

**Fase 1 — Database Structure**

```php
// Tabel baru: warranty parameters per brand/pattern
Schema::create('tyre_warranty_parameters', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tyre_brand_id');
    $table->unsignedBigInteger('tyre_pattern_id')->nullable();
    $table->decimal('min_lifetime_km', 15, 2)->nullable();
    $table->decimal('min_lifetime_hm', 15, 2)->nullable();
    $table->integer('max_age_months')->nullable();
    $table->decimal('min_tread_depth', 5, 2)->nullable();
    $table->text('claimable_failure_codes')->nullable(); // JSON array of failure_code IDs
    $table->text('excluded_conditions')->nullable();     // JSON
    $table->string('claim_calculation_method')->nullable(); // 'prorata_km', 'fixed', etc.
    $table->timestamps();
});

// Tambah field di tyres
Schema::table('tyres', function (Blueprint $table) {
    $table->date('purchase_date')->nullable();
    $table->date('warranty_expiry_date')->nullable();
    $table->string('purchase_order_number')->nullable();
});
```

**Fase 2 — Claim Eligibility Engine**

- Auto-check apakah ban eligible claim berdasarkan parameter
- Flag ban yang eligible di dashboard
- Notifikasi ke admin

**Fase 3 — Claim Management Module**

- Form pengajuan claim
- Status tracking (Submitted → Under Review → Approved/Rejected)
- Perhitungan nilai claim (prorata, dll)
- Histori claim per ban

#### Crosscheck & Action Required

- [ ] ⛔ **BLOCKER**: Minta Pak Agus koordinasi dengan principal untuk share parameter warranty
- [ ] Identifikasi brand ban apa saja yang digunakan (Bridgestone, Michelin, dll)
- [ ] Masing-masing brand kemungkinan punya parameter berbeda
- [ ] Apakah perlu integrasi dengan sistem claim pabrik atau manual saja?

---

## Timeline Pengerjaan

```
Minggu 1 (17-21 Feb 2026)
├── ✅ ITEM-3: Kondisi Pemasangan (New/Spare/Repair)     [1-2 jam]
├── ✅ ITEM-2: Failure Name Adjustable                    [3-5 jam]
└── 🔧 ITEM-1: Handle HM/KM Reset (Mulai)               [start]

Minggu 2 (24-28 Feb 2026)
├── ✅ ITEM-1: Handle HM/KM Reset (Selesai)              [4-6 jam total]
└── 🔧 ITEM-5: Overview Dashboard (Fase 1 - DB + Backend) [start]

Minggu 3-4 (3-14 Mar 2026)
├── ✅ ITEM-5: Overview Dashboard (Fase 2 - Frontend)
└── 📋 ITEM-4: Claim/Warranty (Persiapan DB jika data sudah tersedia)

Ongoing
└── ⏳ ITEM-4: Claim/Warranty (menunggu data dari principal)
```

> ⚠️ Timeline bersifat estimasi dan bisa berubah tergantung load pekerjaan lain serta ketersediaan data dari pihak eksternal.

---

## Catatan & Dependensi

### Dependensi Antar Item

```
ITEM-3 (Kondisi Ban) ──────────┐
                                ├──→ ITEM-5 (Overview Dashboard)
ITEM-1 (HM/KM Reset Fix) ─────┘         ↑
                                         │
ITEM-2 (Failure Names) ─────────────────┘

ITEM-4 (Warranty) → BLOCKED oleh data eksternal
```

- ITEM-5 (Dashboard) akan lebih bermakna jika ITEM-3 dan ITEM-1 sudah selesai (data lengkap & akurat)
- ITEM-2 tidak memblokir item lain tapi meningkatkan user experience

### Dependensi Eksternal

| Pihak                  | Kebutuhan                           | Untuk Item |
| ---------------------- | ----------------------------------- | ---------- |
| Pak Agus / Site        | Konfirmasi istilah failure per site | ITEM-2     |
| Principal / Pabrik Ban | Parameter warranty & claim          | ITEM-4     |
| Site / Operasional     | Data tonase & rute kendaraan        | ITEM-5     |

### Risiko

| Risiko                                          | Dampak                               | Mitigasi                                     |
| ----------------------------------------------- | ------------------------------------ | -------------------------------------------- |
| Data principal tidak kunjung datang             | ITEM-4 tertunda indefinitely         | Set deadline, siapkan default parameter      |
| Data KM historis sudah terlanjur 0 karena reset | Lifetime ban tidak akurat retroaktif | Buat fitur adjustment manual untuk data lama |
| Istilah failure tidak konsisten antar site      | User bingung                         | Gunakan kode standar + alias                 |

---

## Approval & Sign-off

| Role          | Nama           | Status     | Tanggal |
| ------------- | -------------- | ---------- | ------- |
| Product Owner | Pak Agus (GSI) | ⬜ Pending | -       |
| Dev Lead      | -              | ⬜ Pending | -       |
| Developer     | -              | ⬜ Pending | -       |

---

_Dokumen ini akan di-update seiring perkembangan pengerjaan._
