# Tyre Performance - Data Synchronization Documentation

## 📊 Sinkronisasi Data Otomatis

### 1. PEMASANGAN BAN (Installation)

Ketika user mengisi form pemasangan (`/master_data/movement/pemasangan`), sistem akan melakukan update otomatis:

#### Tabel: `tyres` (Master Ban)

```php
✅ status              → 'Installed'
✅ current_vehicle_id  → ID kendaraan tujuan
✅ current_position_id → ID posisi ban di kendaraan
✅ current_tread_depth → RTD (jika diinput saat pemasangan)
```

#### Tabel: `tyre_position_details` (Posisi di Kendaraan)

```php
✅ tyre_id → ID ban yang dipasang (sinkronisasi 2 arah)
```

#### Tabel: `tyre_movements` (History Pergerakan)

```php
✅ Record baru dibuat dengan semua detail pemasangan:
   - movement_type: 'Installation'
   - tyre_id, vehicle_id, position_id
   - odometer_reading, hour_meter_reading
   - rtd_reading, psi_reading
   - tyreman_1, tyreman_2
   - start_time, end_time
   - dll.
```

---

### 2. PELEPASAN BAN (Removal)

Ketika user mengisi form pelepasan (`/master_data/movement/pelepasan`), sistem akan:

#### Tabel: `tyres` (Master Ban)

```php
✅ status              → Sesuai pilihan user (Repaired/Scrap/New)
✅ current_vehicle_id  → NULL (ban lepas dari kendaraan)
✅ current_position_id → NULL
✅ total_lifetime_km   → OTOMATIS BERTAMBAH (Odometer Lepas - Odometer Pasang)
✅ total_lifetime_hm   → OTOMATIS BERTAMBAH (HM Lepas - HM Pasang)
✅ current_tread_depth → RTD terakhir yang diinput
```

**Contoh Perhitungan Lifetime:**

```
Pemasangan: Odometer = 10,000 km
Pelepasan:  Odometer = 15,000 km
Selisih:    5,000 km → Ditambahkan ke total_lifetime_km

Jika ban sudah pernah dipasang sebelumnya:
total_lifetime_km (lama) = 8,000 km
total_lifetime_km (baru) = 8,000 + 5,000 = 13,000 km
```

#### Tabel: `tyre_position_details` (Posisi di Kendaraan)

```php
✅ tyre_id → NULL (posisi menjadi kosong/available)
```

#### Tabel: `tyre_movements` (History Pergerakan)

```php
✅ Record baru dibuat dengan semua detail pelepasan:
   - movement_type: 'Removal'
   - target_status (status ban setelah dilepas)
   - failure_code_id (jika ada kerusakan)
   - dll.
```

---

## 🔄 Integritas Data (Data Integrity)

### ✅ AMAN - Sudah Tersinkronisasi:

1. **Status Ban:**
    - Otomatis berubah saat pemasangan/pelepasan
    - Tidak perlu update manual

2. **Posisi Ban di Kendaraan:**
    - Sinkronisasi 2 arah antara `tyres.current_position_id` dan `tyre_position_details.tyre_id`
    - Ketika ban dipasang: kedua field terisi
    - Ketika ban dilepas: kedua field di-NULL-kan

3. **Lifetime Tracking:**
    - Otomatis terakumulasi setiap kali ban dilepas
    - Tidak bisa dimanipulasi manual (calculated field)

4. **History Lengkap:**
    - Semua pergerakan tercatat di `tyre_movements`
    - Bisa dilacak siapa yang memasang/melepas (created_by)
    - Timestamp otomatis

### ⚠️ BELUM TERSINKRONISASI:

1. **Stok di Lokasi:**
    - Tabel `tyre_locations` tidak punya kolom `current_stock_count`
    - Jika ingin tracking stok per lokasi, perlu:
        - Tambah kolom `current_stock` di `tyre_locations`
        - Update stok saat ban masuk/keluar lokasi

2. **Work Location Ban:**
    - Field `work_location_id` di tabel `tyres` ada, tapi:
    - Tidak otomatis update saat pemasangan/pelepasan
    - Perlu logic tambahan jika ingin auto-update lokasi

---

## 🛡️ Proteksi Data

### Database Transaction:

```php
DB::beginTransaction();
try {
    // Semua update dilakukan di sini
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Jika ada error, semua perubahan dibatalkan
}
```

**Artinya:**

- Jika salah satu update gagal, SEMUA perubahan dibatalkan
- Data tetap konsisten
- Tidak ada "setengah jadi"

---

## 📍 Route untuk Detail Ban

```php
URL:        /master_data/master_tyre/{id}
Route Name: tyre-master.show
Method:     GET
Controller: TyreMasterController@show
```

**Contoh:**

```
/master_data/master_tyre/1  → Detail ban ID 1
/master_data/master_tyre/25 → Detail ban ID 25
```

---

## 🔍 Cara Cek Sinkronisasi

### Test Case 1: Pemasangan Ban

1. Pilih ban dengan status "New" (misal: SN-STOCK-00001)
2. Pasang ke kendaraan DT-001, posisi FL
3. Cek database:

    ```sql
    SELECT status, current_vehicle_id, current_position_id
    FROM tyres WHERE serial_number = 'SN-STOCK-00001';
    -- Harusnya: status='Installed', vehicle_id dan position_id terisi

    SELECT tyre_id FROM tyre_position_details
    WHERE position_code = 'FL' AND tyre_position_configuration_id = ...;
    -- Harusnya: tyre_id terisi dengan ID ban tersebut
    ```

### Test Case 2: Pelepasan Ban

1. Lepas ban dari DT-001 FL
2. Input Odometer Lepas = 15000, Odometer Pasang = 10000
3. Cek database:
    ```sql
    SELECT status, current_vehicle_id, total_lifetime_km
    FROM tyres WHERE serial_number = 'SN-STOCK-00001';
    -- Harusnya:
    -- status='Repaired' (atau sesuai pilihan)
    -- current_vehicle_id=NULL
    -- total_lifetime_km bertambah 5000
    ```

---

## 💡 Rekomendasi

### Jika Ingin Tracking Stok Lokasi:

1. Tambah migration untuk kolom `current_stock`:

    ```php
    Schema::table('tyre_locations', function (Blueprint $table) {
        $table->integer('current_stock')->default(0);
    });
    ```

2. Update controller saat pemasangan/pelepasan:
    ```php
    // Saat pemasangan: kurangi stok lokasi asal
    // Saat pelepasan: tambah stok lokasi tujuan
    ```

### Jika Ingin Auto-Update Work Location:

Tambahkan di controller:

```php
// Saat pelepasan
$tyre->update([
    'work_location_id' => $request->work_location_id, // Lokasi tujuan
    // ... field lainnya
]);
```

---

**Kesimpulan:**
✅ Sinkronisasi data sudah AMAN untuk use case standar
✅ Semua perubahan menggunakan Database Transaction
✅ History lengkap tercatat
⚠️ Stok lokasi perlu development tambahan jika diperlukan
