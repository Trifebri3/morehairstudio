# Dokumentasi Teknis: Fitur Hapus Akun Permanen (Pelanggan, Barber, Admin)
**MORE Hair Studio System Architecture**  
*Versi: 2026.1 &bull; Status: Production Ready &bull; Kepatuhan: UU PDP No. 27/2022 & GDPR Article 17*

---

## 1. Ringkasan Eksekutif

Fitur **Hapus Akun Permanen** dirancang untuk memberikan hak privasi penuh kepada pengguna (*Right to Erasure / Right to be Forgotten*) tanpa mengorbankan integritas catatan keuangan, perpajakan, dan audit operasional studio.

Fitur ini mencakup tiga role utama:
1. **Pelanggan (Customer)**
2. **Barber / Hairstylist**
3. **Administrator (Super Admin & Outlet Admin)**

---

## 2. Analisis Dampak Data & Strategi Penanganan

Dalam skema database MORE Hair Studio, tabel `bookings` memiliki relasi foreign key:
```sql
$table->foreignId('customer_id')->constrained()->cascadeOnDelete();
```
Jika baris pelanggan dihapus secara fisik (`DELETE FROM customers`), seluruh riwayat pemesanan (`bookings`), rincian layanan (`booking_items`), log status (`booking_status_histories`), dan pembayaran (`payments`) akan ikut **terhapus permanen** (*cascade on delete*). Hal ini melanggar regulasi pembukuan akuntansi dan audit perpajakan.

### Matriks Penanganan Data Terkait

| Entitas Data | Role Terdampak | Tindakan Penghapusan | Alasan & Justifikasi |
| :--- | :--- | :--- | :--- |
| **Data Pribadi (PII)**<br>*(Nama, HP, WhatsApp, Email, Alamat, Tanggal Lahir)* | Pelanggan | **Dianonimkan Penuh (Scrubbed / Null)**<br>`name` &rarr; `"Pelanggan Terhapus (Anonim)"`<br>`phone` &rarr; `"DEL-{id}-{random}"`<br>WhatsApp, Email, Alamat &rarr; `NULL` | Memenuhi UU PDP No. 27/2022 Pasal 43 & 44 (Penghapusan data pribadi yang tidak lagi relevan). |
| **Reservasi Masa Depan / Aktif**<br>*(Status: pending, confirmed, waiting_checkin)* | Pelanggan | **Dibatalkan Otomatis (*Cancelled*)**<br>`notes` dilengkapi penanda pembatalan otomatis. | Menghindari reservasi terbengkalai pada antrean studio. |
| **Riwayat Transaksi Lampau**<br>*(Bookings completed, Payments, POS Transactions)* | Pelanggan | **Dipertahankan (Tanpa Identitas Personal)** | Integritas laporan omset studio, laporan pajak, dan rekonsiliasi kasir kas. |
| **Akun Login Pengguna**<br>*(Tabel `users`)* | Pelanggan, Barber, Admin | **Dihapus Permanen (`$user->delete()`)**<br>Sesi login di-invalidate, token di-regenerate. | Memastikan kredensial dan hak akses terputus total. |
| **Profil & Jadwal Barber**<br>*(Tabel `stylists`, `stylist_schedules`)* | Barber / Stylist | **Dihapus Permanen (`$stylist->delete()`)**<br>Foto profil dibersihkan dari storage. Record stylist dan user login dihapus bersih dari database. Relasi pada tabel booking dan ulasan otomatis di-set ke `NULL` (*nullOnDelete*) sehingga histori pemesanan tetap aman tanpa menyisakan baris di tabel. | Data dihapus tuntas dari sistem sesuai permintaan penghapusan permanen. |
| **Audit Log Aksi**<br>*(Tabel `audit_logs`)* | Semua Role | **Pencatatan Audit Trail Immutable** | Bukti bahwa penghapusan dilakukan secara sah atas permintaan pengguna atau admin. |

---

## 3. Safety Guards & Mekanisme Pencegahan Kesalahan

Untuk mencegah penghapusan yang tidak disengaja (*accidental deletion*) dan *system lockout*, sistem menerapkan 3 lapis perlindungan:

1. **Proteksi Super Admin Terakhir (*Last Super Admin Guard*)**:
   - Sistem memeriksa jumlah akun dengan role `super_admin`.
   - Jika jumlah Super Admin &le; 1, permintaan penghapusan **DITOLAK KERAS** dengan notifikasi:
     > *"Aksi ditolak: Sistem harus memiliki setidaknya satu Super Admin aktif. Anda tidak dapat menghapus Super Admin terakhir."*

2. **Proteksi Reservasi Aktif Barber (*Barber Active Bookings Guard*)**:
   - Sistem memeriksa apakah Barber memiliki reservasi dengan status `pending`, `confirmed`, `waiting_checkin`, atau `in_progress` pada hari ini atau masa mendatang.
   - Jika ditemukan reservasi aktif, penghapusan **DIBLOKIR** dengan notifikasi:
     > *"Stylist '{name}' masih memiliki {count} reservasi aktif/mendatang. Harap batalkan atau alihkan reservasi tersebut ke stylist lain sebelum menghapus akun."*

3. **Autentikasi Ulang & Konfirmasi Frasa Ganda**:
   - Pengguna wajib memasukkan **Kata Sandi Aktif** (`current_password`).
   - Pengguna wajib mengetik frasa konfirmasi: `"HAPUS AKUN"`. Tombol submit terkunci (*disabled*) sampai frasa tersebut diketik secara tepat.

---

## 4. Arsitektur Backend & Kode Inti

### Service: `App\Services\AccountDeletionService`
Lokasi: `app/Services/AccountDeletionService.php`

Metode publik:
- `AccountDeletionService::deleteCustomerAccount(Customer|User $entity, ?string $reason = null): array`
- `AccountDeletionService::deleteStylistAccount(User|Stylist $entity, ?string $reason = null): array`
- `AccountDeletionService::deleteAdminAccount(User $user, ?string $reason = null): array`

Seluruh operasi dijalankan di dalam `DB::transaction()` untuk menjamin sifat *Atomic, Consistent, Isolated, Durable* (ACID).

---

## 5. Rincian Endpoint & Route

| HTTP Method | URI | Controller / Action | Keterangan |
| :--- | :--- | :--- | :--- |
| `DELETE` | `/profile` | `ProfileController@destroy` | Hapus akun mandiri oleh pengguna yang sedang login (Pelanggan, Barber, Admin). |
| `DELETE` | `/admin/customers/{id}` | `Admin\AdminPanelController@deleteCustomer` | Hapus & anonimkan akun pelanggan via CRM oleh Admin. |
| `DELETE` | `/admin/stylists/{id}` | `Admin\AdminPanelController@deleteStylist` | Hapus akun login & nonaktifkan profil stylist oleh Super Admin. |
| `DELETE` | `/admin/users/{user}` | `Admin\UserController@destroy` | Hapus akun user sistem oleh Super Admin. |

---

## 6. Antarmuka Pengguna (UI / UX)

1. **Dasbor Barber / Hairstylist (`resources/views/hairstylis/dashboard.blade.php`)**:
   - Ditambahkan kartu **Zona Bahaya: Hapus Akun Stylist** beraksen rose/merah.
   - Modal konfirmasi interaktif dengan verifikasi kata sandi aktif dan input teks `"HAPUS AKUN"`.
2. **Admin Panel - Manajemen Stylist (`resources/views/admin/stylists.blade.php`)**:
   - Ditambahkan tombol **Hapus** pada kolom **AKSI** di samping tombol *Edit* dan *Masuk Akun*.
   - Dialog peringatan konfirmasi terstruktur.
3. **Pengaturan Profil Akun (`resources/views/profile/partials/delete-user-form.blade.php`)**:
   - Desain ulang sesuai estetika MORE Hair Studio.
   - Teks penjelasan dinamis sesuai role pengguna.
   - Double-check password & input frasa konfirmasi.
4. **Admin Panel - CRM Pelanggan (`resources/views/admin/customers.blade.php`)**:
   - Dialog konfirmasi yang menegaskan kepatuhan UU PDP dan perlindungan retensi finansial.

---

## 7. Hasil Pengujian (Automated Verification)

Uji coba telah dijalankan menggunakan script integrasi `scratch/test_account_deletion.php` dengan hasil 100% lulus:
- [x] Proteksi Super Admin terakhir sukses menolak penghapusan.
- [x] Penghapusan Super Admin sekunder berhasil dan tercatat di `audit_logs`.
- [x] Proteksi Barber dengan reservasi aktif sukses memblokir penghapusan.
- [x] Penghapusan Barber tanpa booking aktif berhasil: user terhapus, profil diarsipkan, absensi payroll & ulasan tetap aman.
- [x] Penghapusan Pelanggan berhasil: data PII dianonimkan, booking masa depan dibatalkan, riwayat booking & omset keuangan tetap utuh.
