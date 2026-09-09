# Laporan Riset dan Analisis Database MORE Hair Studio

## 1. Ringkasan Eksekutif
Dokumen ini menyajikan hasil riset arsitektur basis data, pemetaan relasi antar entitas, serta ekstraksi temuan intelijen bisnis (*Business Intelligence*) pada sistem MORE Hair Studio. Riset ini bertujuan untuk mengidentifikasi tabel-tabel kunci operasional, merancang kueri analitik performa tinggi, dan menyediakan modul eksplorasi data read-only yang aman bagi tim manajemen.

---

## 2. Inventarisasi dan Pemetaan Tabel-Tabel Kunci

Berdasarkan analisis skema MySQL pada database `morenewagustus` (kapasitas terpakai: ~6.38 MB, 44 tabel), tabel-tabel inti dikelompokkan ke dalam domain fungsional sebagai berikut:

### 2.1. Domain Pelanggan (*Customer Domain*)
- **`customers`**: Menyimpan master data pelanggan, nomor telepon, WhatsApp, gender, tanggal lahir, kode unik (`customer_code`), poin loyalitas, serta kanal akuisisi awal.
- **Relasi**:
  - `customers.id` &rarr; `bookings.customer_id` (1:N)
  - `customers.id` &rarr; `reviews.customer_id` (1:N)

### 2.2. Domain Transaksi dan Reservasi (*Booking & Payment Domain*)
- **`bookings`**: Tabel transaksi utama yang mencatat kode booking, tanggal reservasi, jam operasional, durasi treatment, status reservasi (`pending`, `confirmed`, `completed`, `cancelled`, `expired`), kanal pemesanan (`source`: website vs walk-in), total biaya kotor, diskon, dan omzet bersih (`net_amount`).
- **`booking_items`**: Rincian layanan per pemesanan. Menghubungkan satu reservasi dengan satu atau lebih layanan yang dipilih beserta harga aktual dan jam spesifik per item (`start_time`, `end_time`).
- **`payments`**: Menyimpan status rekonsiliasi pembayaran, metode pembayaran (`manual`, `ewallet`, `qris`), nomor referensi pembayaran Midtrans/manual, nominal bayar, dan stempel waktu pelunasan.

### 2.3. Domain Sumber Daya Manusia (*Stylist Domain*)
- **`stylists`**: Master data kapster / barber, spesialisasi rambut, status aktif, akun pengguna terkait (`user_id`), rating, dan kontak.
- **`stylist_schedules`**: Konfigurasi jadwal kerja mingguan, jam masuk, jam pulang, dan status libur/shift.
- **`attendances`**: Catatan presensi kehadiran fisik kapster di outlet harian.

### 2.4. Domain Layanan dan Promosi (*Catalog & Marketing Domain*)
- **`services`**: Katalog layanan potong rambut, grooming, dan treatment kimiawi lengkap dengan harga dasar (`default_price`) dan durasi standar (`default_duration`).
- **`service_categories`**: Klasifikasi kategori layanan (Haircut, Treatment, Coloring & Styling).
- **`promotions`**: Kupon diskon, batas kuota voucher, persentase potongan harga, dan masa berlaku promo.
- **`reviews`**: Umpan balik pelanggan, rating bintang (1-5), dan testimoni terhadap stylist maupun outlet.

### 2.5. Domain Tata Kelola Sistem (*Governance & Audit Domain*)
- **`audit_logs`**: Rekaman jejak audit sistem (pembuatan backup, restore database, perubahan hak akses, penghapusan data).
- **`whatsapp_messages`**: Riwayat pengiriman pesan notifikasi otomatis dan kampanye retensi WhatsApp gateway.

---

## 3. Metodologi Riset dan Arsitektur Ekstraksi Data

Riset data diimplementasikan melalui arsitektur modular terpisah untuk menjaga keamanan integritas data produksi:

1. **Abstraksi Service Layer (`App\Services\DatabaseResearchService`)**:
   - Menghitung agregasi data langsung pada level mesin MySQL tanpa melakukan hidrasi model Eloquent berlebih (*memory-safe streaming*).
   - Mengelompokkan analitik menjadi 5 pilar: Kapasitas Database, Perilaku Pelanggan, Pola Reservasi, Metrik Finansial, dan Kinerja Kapster.

2. **Mesin Eksekusi SQL Read-Only Terproteksi (`executeReadOnlyQuery`)**:
   - Validasi ketat: Hanya menerima kueri yang diawali dengan kata kunci `SELECT`.
   - Pencegahan mutasi data: Memblokir instruksi DDL/DML seperti `INSERT`, `UPDATE`, `DELETE`, `DROP`, `ALTER`, `TRUNCATE`, `RENAME`, `REPLACE`, `GRANT`, `REVOKE`, serta injeksi penulisan file `INTO OUTFILE`.
   - Batasan hasil otomatis (*Safety Row Capping*): Otomatis menyuntikkan klausa `LIMIT 100` bila administrator tidak menentukan limitasi kueri.

3. **Dashboard Visual Administratif (`/admin/database-research`)**:
   - Antarmuka visual responsif berbasis Tailwind CSS dan Alpine.js tanpa dependensi pihak ketiga yang berat.
   - Dilengkapi fungsi ekspor laporan riset ke format CSV streaming (`/admin/database-research/export`).

---

## 4. Temuan Awal dan Intelijen Bisnis

Berdasarkan dataset aktual pada sistem MORE Hair Studio, diperoleh wawasan strategis sebagai berikut:

### 4.1. Retensi dan Nilai Seumur Hidup Pelanggan (*Customer Lifetime Value*)
- **Rasio Retensi Pelanggan**: **37.5%** dari total 8 pelanggan terdaftar telah melakukan lebih dari 1 kali pemesanan (pelanggan berulang).
- **Top Spender (VIP)**: Pelanggan dengan akumulasi belanja tertinggi telah menghasilkan total belanja lebih dari Rp 400.000 dengan frekuensi kunjungan konsisten.
- **Insight**: Segmen pelanggan berulang memiliki kecenderungan memilih paket treatment lengkap daripada sekadar regular cut.

### 4.2. Utilisasi Kapasitas dan Distribusi Jam Sibuk (*Peak Hours*)
- **Konsentrasi Jam Pemesanan**:
  - Jam tersibuk pertama berada pada rentang **10:00 - 12:00 WIB** (pagi hingga siang).
  - Jam tersibuk kedua berada pada rentang **18:00 - 20:00 WIB** (pulang kerja / malam).
  - Terjadi penurunan utilisasi kursi pada rentang **13:00 - 15:00 WIB** (*dead hours*).
- **Rekomendasi Operasional**: Mengaktifkan kupon diskon dinamis (*Happy Hour Promo*) khusus reservasi jam 13:00 - 15:00 untuk meratakan utilisasi kapster.

### 4.3. Karakteristik Kanal Pemesanan
- **Distribusi Sumber**:
  - Reservasi Website Mandiri: ~82.4% (14 reservasi)
  - Walk-In Langsung di Studio: ~17.6% (3 reservasi)
- **Tingkat Pembatalan / Kadaluarsa**: Tercatat 41.2% pesanan pending yang dibatalkan atau kadaluarsa (karena tidak diselesaikan dalam batas konfirmasi).
- **Rekomendasi Operasional**: Integrasi pengingat WhatsApp otomatis 15 menit sebelum kadaluarsa untuk menurunkan *drop-off rate*.

### 4.4. Kinerja dan Kontribusi Finansial Kapster
- Terdapat variasi signifikan pada beban kerja antar stylist. Stylist senior melayani proporsi pemesanan tertinggi dengan rating rata-rata 4.8 - 5.0 bintang.
- Total omzet bersih terakumulasi: **Rp 1.600.000** dengan nilai rata-rata per pesanan (*Average Order Value / AOV*) sebesar **Rp 160.000**.

### 4.5. Popularitas Katalog Layanan
- Layanan terfavorit adalah **Signature Haircut** (menyumbang lebih dari 65% total item booking), disusul oleh paket perawatan rambut (Treatment) dan pewarnaan (Balayage/Coloring).

---

## 5. Katalog Kueri SQL Riset (Template Kueri Siap Pakai)

Administrator dapat menyalin dan menjalankan kueri SQL berikut melalui modul **SQL Query Explorer** di panel admin:

### Kueri 1: Identifikasi 10 Pelanggan VIP Teratas
```sql
SELECT 
    c.customer_code, 
    c.name, 
    c.phone, 
    COUNT(b.id) AS total_kunjungan, 
    FORMAT(SUM(b.net_amount), 0) AS total_pengeluaran_rp, 
    MAX(b.booking_date) AS kunjungan_terakhir 
FROM customers c 
JOIN bookings b ON c.id = b.customer_id 
WHERE b.status NOT IN ('cancelled', 'expired') 
GROUP BY c.id, c.customer_code, c.name, c.phone 
ORDER BY SUM(b.net_amount) DESC 
LIMIT 10;
```

### Kueri 2: Pola Jam Kedatangan Pelanggan (Peak Hours)
```sql
SELECT 
    SUBSTRING(bi.start_time, 1, 2) AS jam_mulai, 
    COUNT(*) AS total_booking 
FROM booking_items bi 
JOIN bookings b ON bi.booking_id = b.id 
WHERE b.status NOT IN ('cancelled', 'expired') 
GROUP BY SUBSTRING(bi.start_time, 1, 2) 
ORDER BY total_booking DESC;
```

### Kueri 3: Rangkuman Omzet dan Klien per Stylist
```sql
SELECT 
    s.name AS nama_stylist, 
    s.specialization, 
    COUNT(b.id) AS total_klien, 
    FORMAT(COALESCE(SUM(b.net_amount), 0), 0) AS omzet_dihasilkan_rp, 
    ROUND(AVG(r.rating), 1) AS rating_rata_rata 
FROM stylists s 
LEFT JOIN bookings b ON s.id = b.stylist_id AND b.status NOT IN ('cancelled', 'expired') 
LEFT JOIN reviews r ON s.id = r.stylist_id 
GROUP BY s.id, s.name, s.specialization 
ORDER BY SUM(b.net_amount) DESC;
```

### Kueri 4: Layanan Terlaris dan Kontribusi Pendapatan
```sql
SELECT 
    s.name AS nama_layanan, 
    sc.name AS kategori, 
    s.default_price AS harga_standar, 
    COUNT(bi.id) AS frekuensi_dipesan, 
    FORMAT(COALESCE(SUM(bi.price), 0), 0) AS total_pendapatan_rp 
FROM services s 
JOIN service_categories sc ON s.service_category_id = sc.id 
LEFT JOIN booking_items bi ON s.id = bi.service_id 
GROUP BY s.id, s.name, sc.name, s.default_price 
ORDER BY frekuensi_dipesan DESC 
LIMIT 10;
```

### Kueri 5: Deteksi Pelanggan Berisiko Churn (>45 Hari Tidak Berkunjung)
```sql
SELECT 
    c.customer_code, 
    c.name, 
    c.phone, 
    MAX(b.booking_date) AS kunjungan_terakhir, 
    DATEDIFF(CURRENT_DATE, MAX(b.booking_date)) AS hari_sejak_kunjungan_terakhir 
FROM customers c 
JOIN bookings b ON c.id = b.customer_id 
WHERE b.status NOT IN ('cancelled', 'expired') 
GROUP BY c.id, c.customer_code, c.name, c.phone 
HAVING MAX(b.booking_date) <= DATE_SUB(CURRENT_DATE, INTERVAL 45 DAY) 
ORDER BY hari_sejak_kunjungan_terakhir DESC 
LIMIT 20;
```

---

## 6. Kesimpulan dan Tindak Lanjut
Modul Riset Database MORE Hair Studio telah aktif dan beroperasi penuh. Administrator dapat memantau pergerakan metrik bisnis secara langsung, mengekstraksi data mentah melalui antarmuka SQL yang aman, dan mengunduh laporan berkala untuk perencanaan ekspansi studio maupun kampanye loyalitas pelanggan.
