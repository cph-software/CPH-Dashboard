# Investigasi: Menu User Management Tidak Tampil di Hosting

## Masalah

Menu "User Management" tampil di lingkungan **Local**, tetapi tidak muncul di lingkungan **Hosting**.

## Analisis Penyebab

Berdasarkan pengecekan kode pada `resources/views/layouts/sections/menu.blade.php` (baris 80-88), terdapat logika filter yang sangat ketat:

```php
// Baris 80 di menu.blade.php
if (!in_array($user->role->name, ['Super Admin', 'Administrator'])) {
    // Jika bukan "Super Admin" atau "Administrator", hanya tampilkan menu Tyre
    $aplikasiList = $rawAplikasiList->where('id', 20);
} else {
    // Jika Role cocok, tampilkan Tyre + CPH Dashboard + User Management
    $aplikasiList = $rawAplikasiList->filter(function ($app) {
        return $app->id == 20 || $app->name == 'CPH Dashboard' || $app->name == 'User Management';
    });
}
```

### Kemungkinan 1: Ketidaksesuaian Nama Role (Case-Sensitive)

Pengecekan `in_array` di PHP bersifat _case-sensitive_. Jika di database hosting nama role-nya adalah **"ADMINISTRATOR"** (huruf besar) atau **"Admin"**, maka pengecekan ini akan mengembalikan `false` dan menyembunyikan menu User Management.

### Kemungkinan 2: Data Aplikasi Belum Ada

Jika di tabel `aplikasi` pada database hosting tidak ada record dengan nama **tepat** `"User Management"` atau `"CPH Dashboard"`, maka filter `$app->name == ...` akan gagal.

### Kemungkinan 3: Relasi Role ke Aplikasi/Menu Belum Di-set

Logika menu bergantung pada fungsi helpel `getAplikasiPerRole($user->role_id)`. Jika role di hosting belum dikaitkan ke aplikasi User Management di tabel `aplikasi_role` atau belum ada menu yang di-assign ke role tersebut di tabel `role_menu`, maka aplikasi tersebut tidak akan muncul di daftar.

---

## Langkah Pengecekan di Hosting

Silakan cek database hosting melalui terminal (artisan tinker) atau database manager (phpMyAdmin/DBeaver):

1.  **Cek Nama Role User Anda:**

    ```bash
    php artisan tinker
    >>> auth()->user()->role->name
    ```

    _Pastikan outputnya adalah "Super Admin" atau "Administrator" (sama persis)._

2.  **Cek Daftar Aplikasi:**

    ```bash
    php artisan tinker
    >>> App\Models\Aplikasi::pluck('name', 'id')
    ```

    _Pastikan ada ID aplikasi dengan nama "User Management" atau "CPH Dashboard"._

3.  **Cek Apakah Role Terhubung ke Aplikasi:**
    ```bash
    php artisan tinker
    >>> getAplikasiPerRole(auth()->user()->role_id)->pluck('name')
    ```
    _Pastikan "User Management" ada dalam daftar output._

---

## Rekomendasi Solusi (Draft)

Jika ingin membuat kode lebih aman dari perbedaan huruf besar/kecil, kita bisa mengubah baris 80 di `menu.blade.php` menjadi:

```php
// Menggunakan strtolower untuk mengabaikan perbedaan huruf besar/kecil
if (!in_array(strtolower($user->role->name), ['super admin', 'administrator'])) {
    // ...
}
```

Dan pastikan seeder berikut telah dijalankan di hosting:

```bash
php artisan db:seed --class=UserManagementSeeder
```
