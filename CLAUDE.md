# Project: uktsevima (SEVIMA M2 Project)

## Deskripsi singkat
uktsevima adalah sistem manajemen tagihan dan pembayaran Uang Kuliah Tunggal (UKT) multi-institusi / multi-tenant berbasis web fullstack dengan integrasi webhook payment gateway (HMAC-SHA256 signature verification), portal admin keuangan, dan portal mahasiswa.

## Stack dan cara menjalankan
- **Framework:** Laravel 13 (PHP 8.4)
- **Database:** PostgreSQL (Multi-tenant shared database, single schema)
- **Frontend UI:** Blade Templates + Bootstrap 5 + NiceAdmin Dashboard + Yajra DataTables + SweetAlert2
- **Testing:** PHPUnit / Pest Feature & Unit Tests

### Cara Menjalankan:
1. `composer install`
2. Konfigurasi `.env` dengan koneksi PostgreSQL:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=uktsevima
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan serve` (Aplikasi berjalan pada `http://127.0.0.1:8000`)
6. Menjalankan test: `php artisan test`

## Arsitektur singkat
- **Multi-Tenant Shared Database:** Seluruh tabel transaksi dan master membawa `institusi_id` (foreign key ke `institusis`).
- **Role-Based View & Access Control:**
  - `super_admin`: Akses cross-institution untuk monitoring platform Sevima pusat.
  - `admin` (Finance Admin Kampus): Dibatasi secara ketat hanya pada `institusi_id` kampusnya (tenant isolation).
  - `mahasiswa`: Terisolasi pada profil mahasiswa dan tagihan/riwayat transaksi dirinya sendiri.
- **Webhook & HMAC-SHA256 Security:**
  - Gateway mengirim payload ke endpoint `POST /api/webhook-payment`.
  - Signature dihitung dari raw body mentah (`payload_asli`) dicocokkan dengan `secret_key` institusi penerima menggunakan `hash_equals()`.
  - Proteksi Idempotency: Deteksi duplikasi event ID untuk mencegah double-processing.

## Aturan bisnis penting (termasuk multi-institusi)
1. **Isolasi Data Antar-Institusi:**
   Pengguna institusi A tidak boleh melihat, mengubah, atau menghapus data tagihan, mahasiswa, maupun transaksi milik institusi B (HTTP 403 Forbidden).
2. **Keunikan Tagihan UKT (Compound Unique Constraint):**
   `UQ(institusi_id, mahasiswa_id, periode_akademik_id)`: Satu mahasiswa hanya memiliki tepat 1 tagihan UKT per periode semester di institusi yang sama.
3. **Keunikan NIM Per Kampus:**
   `UQ(institusi_id, nomor_induk_mahasiswa)`: NIM unik di dalam kampus terkait, namun mahasiswa di kampus lain boleh memiliki NIM yang sama.
4. **Keunikan Nomor Tagihan:**
   `UQ(institusi_id, nomor_tagihan)`: Nomor tagihan unik per institusi.
5. **Model Pembayaran Bertahap (Cicilan Bulanan):**
   Tagihan semester dapat dicicil per bulan melalui tabel `transaksi_pembayarans`. Status tagihan mencerminkan akumulasi riil:
   - `belum_bayar`: Belum ada pembayaran masuk.
   - `dicicil`: Pembayaran cicilan masuk namun belum melunasi total UKT.
   - `lunas`: Akumulasi cicilan mencapai/melebihi total tagihan.
   - `kadaluarsa`: Melewati batas jatuh tempo tanpa pelunasan.
6. **Audit Trail Log Webhook:**
   Semua percobaan webhook (termasuk signature tidak valid dan duplikat) dicatat lengkap ke tabel `log_webhooks` beserta alamat IP, HTTP status, dan response error.

## Konvensi kode
- Menggunakan PSR-12 coding standard dan penamaan Laravel Eloquent konvensional.
- Strict Type Hints & Form Requests/Controller validation.
- Database Transaction (`DB::transaction`) untuk operasi keuangan multi-tabel (transaksi pembayaran + tagihan status).
- Penggunaan `hash_equals()` untuk perbandingan signature kriptografis agar terhindar dari *timing attack*.
- Clickable file pathing dan dokumentasi yang konsisten.

## Hal yang tidak boleh dilakukan AI
1. Jangan membocorkan atau menyimpan plaintext secret key atau credentials sensitif di dalam source code repository.
2. Jangan mengizinkan bypass isolasi multi-tenant (`institusi_id`) pada level query maupun controller.
3. Jangan menghapus atau mengubah tabel transaksi tanpa memastikan mekanisme sinkronisasi status induk tagihan.
4. Jangan menggunakan integrasi Livewire karena dilarang dalam ketentuan teknis SEVIMA.
5. Jangan mengubah skema database di luar spesifikasi diagram ERD SEVIMA.

