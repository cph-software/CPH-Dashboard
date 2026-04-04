# 🚀 Panduan Push & Deployment — CPH Dashboard

> Panduan ini menjelaskan langkah-langkah lengkap untuk melakukan push perubahan
> dari lokal ke GitHub, yang kemudian secara otomatis ter-deploy ke Hostinger cPanel.

---

## 📋 Prasyarat (Satu Kali Setup)

### 1. Extension VS Code yang Diperlukan
Buka VS Code → Extensions (Ctrl+Shift+X), lalu install:

| Extension | Fungsi | Wajib? |
|-----------|--------|--------|
| **GitLens** | Visualisasi git blame, history, diff | ✅ Wajib |
| **Git Graph** | Visualisasi branch dan commit dalam grafik | ✅ Wajib |
| **GitHub Pull Requests** | Membuat PR langsung dari VS Code | Opsional |

> **Catatan:** Extension "GitHub Actions" yang sudah Anda download berguna untuk memantau status deployment. Itu sudah benar.

### 2. Konfigurasi Git (Satu Kali)
Buka Terminal di VS Code (Ctrl+`) lalu jalankan:

```bash
# Set identitas Anda (ganti dengan data Anda)
git config --global user.name "Nama Anda"
git config --global user.email "email@anda.com"
```

### 3. Pastikan Anda Punya Akses ke Repository
- Anda harus punya akses push ke `https://github.com/cph-software/CPH-Dashboard.git`
- Jika belum, minta owner repository untuk menambahkan Anda sebagai collaborator
- VS Code akan meminta login GitHub saat pertama kali push

---

## 🔄 Alur Kerja Deployment (Setiap Kali Push)

### Cara Kerjanya:
```
Anda Push ke GitHub (branch staging)
        ↓
GitHub Actions otomatis jalan (deployCpanel.yml)
        ↓
File di-upload via FTP ke Hostinger cPanel
        ↓
Website staging otomatis terupdate
```

---

## 📝 Langkah-Langkah Push (Step by Step)

### LANGKAH 1: Review Perubahan
Sebelum push, pastikan Anda tahu apa saja yang berubah.

```bash
# Lihat daftar file yang berubah
git status

# Lihat detail perubahan per file (opsional)
git diff --stat
```

Atau di VS Code: Klik icon **Source Control** (Ctrl+Shift+G) di sidebar kiri.
Anda akan melihat daftar file yang berubah di panel "Changes".

---

### LANGKAH 2: Stage (Pilih File yang Akan Di-push)

**Cara A — Via Terminal:**
```bash
# Stage SEMUA file yang berubah
git add .

# ATAU stage file tertentu saja
git add app/Http/Controllers/UserManagement/ImportController.php
```

**Cara B — Via VS Code UI:**
Di panel Source Control, klik tombol **+** di samping setiap file, atau klik **+** di header "Changes" untuk stage semua.

> ⚠️ **PENTING:** Pastikan file `.env` TIDAK ikut ter-stage. File ini berisi password database lokal Anda dan sudah ada di `.gitignore`.

---

### LANGKAH 3: Commit (Simpan Perubahan dengan Pesan)

**Cara A — Via Terminal:**
```bash
git commit -m "fix: perbaikan sistem validasi import dan optimasi dashboard"
```

**Cara B — Via VS Code UI:**
1. Ketik pesan commit di kotak teks di atas panel Source Control
2. Klik tombol **✓ Commit** atau tekan Ctrl+Enter

**Format Pesan Commit yang Baik:**
```
feat: fitur baru (contoh: feat: tambah validasi import)
fix: perbaikan bug (contoh: fix: perbaikan timeout saat import)
refactor: perubahan kode tanpa mengubah fungsi
style: perubahan tampilan/UI
docs: perubahan dokumentasi
```

---

### LANGKAH 4: Push ke GitHub

**Cara A — Via Terminal:**
```bash
git push origin staging
```

**Cara B — Via VS Code UI:**
Klik tombol **...** (menu) di panel Source Control → **Push**
Atau klik icon ☁️↑ di status bar bawah VS Code.

> Jika ini pertama kali, VS Code akan meminta Anda login ke GitHub.
> Ikuti instruksi pop-up yang muncul.

---

### LANGKAH 5: Pantau Deployment

Setelah push berhasil, GitHub Actions akan otomatis berjalan:

1. Buka `https://github.com/cph-software/CPH-Dashboard/actions` di browser
2. Anda akan melihat workflow "Publish Website" sedang berjalan (icon kuning berputar)
3. Tunggu sampai berubah menjadi ✅ hijau (biasanya 2-5 menit)
4. Website di Hostinger sudah terupdate!

> **Atau** gunakan extension GitHub Actions di VS Code untuk memantau langsung dari editor.

---

## ⚠️ Hal-Hal yang Harus Diperhatikan

### Tentang Database
Push ke GitHub **TIDAK otomatis menjalankan migrasi database**.
Jika ada perubahan struktur database (migration baru), Anda harus:
1. Login ke cPanel Hostinger
2. Buka Terminal atau SSH
3. Jalankan: `cd public_html && php artisan migrate`

### Tentang File .env
- File `.env` sudah di-`.gitignore`, jadi TIDAK akan ikut ter-push
- Konfigurasi `.env` di server hosting diatur terpisah langsung di cPanel
- Jangan pernah meng-commit file `.env` karena berisi kredensial sensitif

### Tentang Composer Dependencies
Jika Anda menambahkan package baru via `composer require`:
1. File `composer.json` dan `composer.lock` akan ikut ter-push
2. Di server, Anda perlu jalankan `composer install` via SSH/Terminal cPanel

### Tentang Branch
- **`staging`** → Website staging/testing (yang Anda gunakan sekarang)
- **`main`** → Website production/live
- Selalu push ke `staging` dulu untuk testing
- Setelah yakin stabil, buat Pull Request dari `staging` ke `main`

---

## 🔥 Perintah Darurat

```bash
# Batalkan semua perubahan yang belum di-stage (HATI-HATI, tidak bisa di-undo!)
git checkout -- .

# Batalkan commit terakhir (perubahan tetap ada di working directory)
git reset --soft HEAD~1

# Lihat log commit terakhir
git log --oneline -10

# Lihat siapa yang mengubah baris tertentu
git blame namafile.php
```

---

## 📞 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Push ditolak (rejected) | Jalankan `git pull origin staging` dulu, lalu push lagi |
| GitHub minta password | Gunakan Personal Access Token, bukan password biasa |
| Deployment gagal di Actions | Cek tab Actions di GitHub, baca error log-nya |
| Website tidak berubah setelah push | Cek apakah GitHub Actions sudah selesai (hijau), clear cache browser |
