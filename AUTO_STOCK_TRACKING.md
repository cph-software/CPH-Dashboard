# Auto Stock Tracking - Tyre Locations

## 🎯 Fitur Baru: Tracking Stok Otomatis

Sistem sekarang **otomatis menghitung dan mengupdate stok ban** di setiap lokasi tanpa perlu input manual!

---

## 📊 Cara Kerja

### 1. **PEMASANGAN BAN (Installation)**

Ketika ban dipasang ke kendaraan:

```
Lokasi Asal (Gudang) → Stok BERKURANG (-1)
```

**Contoh:**

```
Sebelum: Gudang Pusat = 20 ban
User pasang ban SN-STOCK-00001 ke kendaraan DT-001
Sesudah: Gudang Pusat = 19 ban ✅
```

---

### 2. **PELEPASAN BAN (Removal)**

Ketika ban dilepas dari kendaraan:

```
Lokasi Tujuan (Gudang) → Stok BERTAMBAH (+1)
```

**Contoh:**

```
Sebelum: Workshop Area A = 5 ban
User lepas ban dari DT-001, simpan ke Workshop Area A
Sesudah: Workshop Area A = 6 ban ✅
```

---

## 🔄 Sinkronisasi Otomatis

### Database Changes:

```sql
-- Kolom baru di tabel tyre_locations
current_stock INT DEFAULT 0
```

### Controller Logic:

**Installation:**

```php
// Kurangi stok di lokasi asal
DB::table('tyre_locations')
    ->where('id', $oldLocationId)
    ->decrement('current_stock');
```

**Removal:**

```php
// Tambah stok di lokasi tujuan
DB::table('tyre_locations')
    ->where('id', $request->work_location_id)
    ->increment('current_stock');
```

---

## 🎨 Tampilan di UI

### Master Locations Table:

```
┌─────────────────┬──────────┬──────────┬───────────────┬─────────┐
│ Location Name   │ Type     │ Capacity │ Current Stock │ Actions │
├─────────────────┼──────────┼──────────┼───────────────┼─────────┤
│ Gudang Pusat    │ Warehouse│ 1000     │ 🟢 20 tyres   │ [Edit]  │
│ Workshop Area A │ Service  │ 50       │ 🟢 0 tyres    │ [Edit]  │
│ Disposal Yard   │ Disposal │ 500      │ 🟢 0 tyres    │ [Edit]  │
└─────────────────┴──────────┴──────────┴───────────────┴─────────┘
```

**Color Indicators:**

- 🟢 **Green** (0-50% capacity): Stock normal
- 🟡 **Yellow** (50-80% capacity): Stock medium
- 🔴 **Red** (>80% capacity): Stock hampir penuh

---

## 🛠️ Command untuk Sync Stok Awal

Jika Anda sudah punya data ban sebelumnya, jalankan command ini untuk sync stok awal:

```bash
php artisan tyre:sync-location-stock
```

**Output:**

```
Syncing tyre location stock...
Location: Gudang Pusat - Stock: 20
Location: Workshop Area A - Stock: 0
Location: Disposal Yard - Stock: 0
Stock sync completed!
```

---

## ✅ Keuntungan

1. **Tidak Perlu Input Manual**
    - Stok otomatis update saat pemasangan/pelepasan
    - Mengurangi human error

2. **Real-Time Accuracy**
    - Stok selalu akurat dengan kondisi aktual
    - Langsung terlihat di Master Locations

3. **Inventory Control**
    - Mudah monitoring kapasitas gudang
    - Warning otomatis jika hampir penuh

4. **Audit Trail**
    - Semua perubahan stok tercatat via movement history
    - Bisa dilacak siapa yang melakukan transaksi

---

## 📝 Aturan Perhitungan Stok

**Stok dihitung dari ban yang:**

- ✅ `work_location_id` = ID lokasi tersebut
- ✅ `status` ≠ 'Installed' (ban yang tidak sedang terpasang)

**Artinya:**

- Ban dengan status `New`, `Repaired`, `Scrap` → Dihitung sebagai stok
- Ban dengan status `Installed` → TIDAK dihitung (sedang di kendaraan)

---

## 🔍 Test Case

### Scenario 1: Pemasangan Ban Baru

```
1. Cek stok awal: Gudang Pusat = 20 ban
2. Pasang ban SN-STOCK-00001 ke DT-001
3. Cek stok akhir: Gudang Pusat = 19 ban ✅
```

### Scenario 2: Pelepasan Ban

```
1. Cek stok awal: Workshop Area A = 0 ban
2. Lepas ban dari DT-001, simpan ke Workshop Area A
3. Cek stok akhir: Workshop Area A = 1 ban ✅
```

### Scenario 3: Rotasi Ban (Pindah Kendaraan)

```
1. Lepas ban dari DT-001 → Stok Workshop +1
2. Pasang ban yang sama ke DT-002 → Stok Workshop -1
3. Net result: Stok tetap sama ✅
```

---

## 🚀 Update di Seeder

Seeder sudah diupdate untuk set stok awal:

```php
// Setelah seeding, jalankan:
php artisan tyre:sync-location-stock
```

---

## ⚠️ Catatan Penting

1. **Jangan Edit Stok Manual**
    - Stok dikelola otomatis oleh sistem
    - Edit manual bisa menyebabkan data tidak konsisten

2. **Sync Command**
    - Hanya perlu dijalankan sekali saat setup awal
    - Atau jika ada data corruption

3. **Migration**
    - Kolom `current_stock` sudah ditambahkan
    - Default value = 0 untuk lokasi baru

---

**Kesimpulan:**
✅ Stok lokasi sekarang **OTOMATIS** dan **REAL-TIME**!
✅ User tidak perlu isi manual lagi!
✅ Sistem menjaga akurasi data secara otomatis!
