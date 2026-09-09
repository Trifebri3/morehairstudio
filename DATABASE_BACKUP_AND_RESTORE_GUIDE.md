# Panduan Operasional Backup dan Pemulihan (Restore) Database MORE Hair Studio

## 1. Pendahuluan dan Tujuan Strategis
Sistem pencadangan (*backup*) dan pemulihan (*restore*) database MORE Hair Studio dirancang untuk menjamin kelangsungan bisnis (*Business Continuity Plan*) serta perlindungan data dari ancaman kehilangan data akibat kegagalan perangkat keras, galat manusia, ataupun insiden keamanan siber.

Panduan ini mendokumentasikan strategi pencadangan, konfigurasi jadwal otomatis, operasional melalui antarmuka web dan baris perintah (CLI), serta prosedur mitigasi pemulihan bencana (*Disaster Recovery*).

---

## 2. Strategi Pencadangan Database

### 2.1. Tipe dan Cakupan Backup
- **Pencadangan Penuh (*Full Backup*)**: Mencakup seluruh struktur skema DDL (*Data Definition Language*: `CREATE TABLE`, `DROP TABLE IF EXISTS`, indeks, kunci asing) dan data DML (*Data Manipulation Language*: data transaksi, pelanggan, reservasi, kapster, log).
- **Format Berkas**:
  - File SQL mentah (`.sql`): Memudahkan inspeksi teks, audit, dan kompatibilitas universal.
  - File SQL terkompresi (`.sql.gz`): Menghemat ruang penyimpanan hingga 90% untuk efisiensi transfer data dan arsip jangka panjang.

### 2.2. Arsitektur Dual-Engine Cerdas (*Dual-Engine Architecture*)
Layanan `App\Services\DatabaseBackupService` mengimplementasikan arsitektur *dual-engine* dengan kemampuan deteksi otomatis:
1. **Engine Utama (Native Binary Engine)**:
   - Mendeteksi ketersediaan utilitas bawaan MySQL (`mysqldump.exe` dan `mysql.exe`) pada sistem Windows (termasuk deteksi otomatis direktori `C:\xampp\mysql\bin\`) maupun lingkungan Linux production (`/usr/bin/mysqldump`).
   - Memberikan kecepatan eksekusi tertinggi (< 1 detik untuk database puluhan megabyte) dengan penggunaan memori minimal.
2. **Engine Cadangan (PHP PDO Stream Fallback)**:
   - Jika utilitas biner MySQL tidak ditemukan pada server (misal lingkungan shared hosting tertentu), sistem secara otomatis beralih ke engine *PHP PDO Stream*.
   - Engine ini membaca skema dan data tabel secara bertahap (*chunked queries*) dan menulis langsung ke berkas SQL tanpa menyebabkan *memory exhaustion*.

---

## 3. Lokasi Penyimpanan dan Kebijakan Retensi

### 3.1. Lokasi Berkas
Seluruh berkas pencadangan disimpan di direktori internal yang terisolasi dari akses publik web:
```text
storage/app/backups/
```
Format penamaan berkas standar:
```text
backup_more_YYYY-MM-DD_HHMMSS.sql
backup_more_YYYY-MM-DD_HHMMSS.sql.gz
```

### 3.2. Kebijakan Retensi dan Pembersihan Otomatis (*Auto-Pruning*)
Untuk menjaga ketersediaan ruang penyimpanan server, sistem menerapkan kebijakan retensi berkas:
- **Periode Retensi Default**: **7 hari**.
- Setiap kali proses pencadangan (baik otomatis maupun manual) dijalankan, sistem secara otomatis mengecek berkas yang berusia lebih dari batas retensi dan menghapusnya dari disk secara aman.
- Parameter retensi dapat dikonfigurasi melalui opsi `--retention=N` pada perintah Artisan.

---

## 4. Penjadwalan Otomatis (Automated Scheduler)

### 4.1. Konfigurasi Jadwal di Sistem
Pencadangan database telah didaftarkan pada scheduler Laravel (`routes/console.php`):
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('db:backup --retention=7')
    ->dailyAt('02:00')
    ->runInBackground();
```
Jadwal ini dijalankan setiap hari pada pukul **02:00 WIB** (saat studio tutup dan aktivitas transaksi berada pada titik terendah).

### 4.2. Setup Scheduler pada Server Produksi
Agar scheduler Laravel berjalan secara berkesinambungan di server:

**Pada Server Linux (Crontab):**
Jalankan `crontab -e` dan tambahkan baris berikut:
```bash
* * * * * cd /path/to/morehairstudio && php artisan schedule:run >> /dev/null 2>&1
```

**Pada Server Windows (Windows Task Scheduler):**
1. Buka **Task Scheduler** di Windows.
2. Buat tugas baru dengan pemicu (*Trigger*): Setiap menit (*Repeat task every 1 minute*).
3. Atur aksi (*Action*): Jalankan program `php.exe` dengan argumen:
   ```text
   "c:\web project\SEPTEMBER2026\morehairstudio\artisan" schedule:run
   ```

---

## 5. Operasional Melalui Baris Perintah (Artisan CLI)

Administrator sistem dapat menjalankan operasi backup secara fleksibel melalui terminal:

### 5.1. Membuat Backup Manual (Format Standar)
```bash
php artisan db:backup
```
*Hasil:* Berkas `.sql` dibuat di `storage/app/backups/` dan berkas lama > 7 hari otomatis dibersihkan.

### 5.2. Membuat Backup Terkompresi (Gzip)
```bash
php artisan db:backup --compress
```
*Hasil:* Berkas `.sql.gz` dibuat dengan rasio kompresi optimal.

### 5.3. Mengatur Batas Retensi Kustom (Misal 14 Hari)
```bash
php artisan db:backup --retention=14
```

### 5.4. Menjalankan Pemulihan Database (Restore) via CLI
Jalankan perintah interaktif untuk memilih berkas backup yang tersedia:
```bash
php artisan db:restore
```
Sistem akan menampilkan daftar berkas backup terbaru dan meminta konfirmasi sebelum mengeksekusi restorasi data.

Untuk menjalankan restore langsung pada berkas spesifik secara non-interaktif:
```bash
php artisan db:restore backup_more_2026-09-09_221631.sql --force
```

---

## 6. Operasional Melalui Antarmuka Admin Web

Modul backup dapat diakses secara visual oleh Administrator melalui tautan:
```text
http://127.0.0.1:8000/admin/database-backup
```

### 6.1. Fitur Utama Panel Backup
1. **Ringkasan KPI**:
   - Total Berkas Backup yang tersimpan.
   - Total Penggunaan Ruang Disk (*Storage Usage*).
   - Waktu dan Nama Berkas Backup Terakhir.
   - Status dan Jadwal Otomatis (Harian 02:00 WIB).
2. **Tombol "Backup Sekarang"**:
   - Memicu pembuatan backup instan dengan opsi kompresi Gzip.
   - Memberikan notifikasi banner hijau seketika berisi metode engine dan waktu eksekusi.
3. **Unduh Berkas (*Download*)**:
   - Tombol unduh aman untuk menyimpan salinan cadangan ke penyimpanan lokal/off-site administrator.
4. **Hapus Berkas (*Delete*)**:
   - Menghapus arsip cadangan tertentu yang sudah tidak diperlukan.
5. **Restore Terproteksi (*Safety Modal Confirmation*)**:
   - Untuk mencegah ketidaksengajaan yang berpotensi fatal, administrator **wajib mengetikkan kata konfirmasi "RESTORE"** pada kotak dialog sebelum tombol eksekusi pemulihan data aktif.

---

## 7. Sistem Audit dan Penanganan Kegagalan

### 7.1. Pencatatan Jejak Audit (*Audit Trail*)
Setiap aksi pencadangan, penghapusan, dan pemulihan database dicatat ke dalam tabel `audit_logs` dengan rincian:
- `user_id`: ID administrator yang memicu aksi.
- `action`: `DATABASE_BACKUP_CREATED`, `DATABASE_RESTORED`, `DATABASE_BACKUP_DELETED`.
- `details`: Nama berkas, ukuran data, durasi pengerjaan, dan status keberhasilan.

### 7.2. Mekanisme Notifikasi Kegagalan
- Jika utilitas mysqldump atau PHP PDO mengalami kegagalan, pesan pengecualian (*exception*) ditangkap secara anggun (*graceful error handling*).
- Galat dicatat ke `storage/logs/laravel.log` dengan level `CRITICAL` atau `ERROR`.
- Panel web menampilkan pesan peringatan merah yang jelas kepada pengguna tanpa memicu *server error 500*.

---

## 8. Prosedur Pemulihan Bencana (*Disaster Recovery Runbook*)

Bila terjadi kerusakan data mendadak pada server produksi:
1. Hentikan sementara traffic publik atau aktifkan mode pemeliharaan:
   ```bash
   php artisan down --secret="morehair-recovery-bypass"
   ```
2. Tentukan berkas backup terbaik (terakhir yang valid) di folder `storage/app/backups/`.
3. Jalankan perintah pemulihan:
   ```bash
   php artisan db:restore <nama_file_backup>.sql --force
   ```
4. Verifikasi integritas tabel dan login admin:
   ```bash
   php artisan tinker --execute="dump(['users' => \App\Models\User::count(), 'bookings' => \App\Domains\Booking\Models\Booking::count()]);"
   ```
5. Nonaktifkan mode pemeliharaan untuk mengembalikan sistem ke kondisi online:
   ```bash
   php artisan up
   ```
