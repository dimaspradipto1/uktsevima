# Keputusan Teknis Penting (DECISIONS.md)

Dokumen ini mencatat keputusan arsitektural dan teknis utama yang diambil selama pengerjaan **Project M2: Integrasi Pembayaran UKT via Webhook**.

---

## 1. Arsitektur Multi-Tenant: Shared Database Single Schema vs Database Per Tenant

**Konteks :**  
Aplikasi melayani banyak perguruan tinggi (multi-kampus). Setiap institusi memiliki mahasiswa, tagihan, dan transaksi sendiri-sendiri, namun data diakses melalui satu sistem terpusat. Diperlukan arsitektur basis data yang efisien, mudah dimigrasi, dan memiliki performa tinggi di PostgreSQL.

**Opsi :**  
1. *Database per Tenant:* Setiap kampus memiliki database PostgreSQL terpisah.
2. *Multi-Schema:* Satu database dengan schema terpisah per kampus (`schema_univ1`, `schema_univ2`).
3. *Shared Database Single Schema dengan Tenant Discriminator (`institusi_id`):* Satu schema publik di mana setiap tabel bisnis membawa kolom `institusi_id` dan dilindungi oleh *compound unique constraints* dan *authorization policies*.

**Keputusan :**  
Memilih **Opsi 3 (Shared Database Single Schema dengan `institusi_id`)**.  
**Alasan:**  
- Sesuai dengan spesifikasi ERD resmi SEVIMA yang menetapkan relasi foreign key terpusat ke tabel `institusis`.
- Memudahkan reporting global bagi Super Admin Sevima, penyederhanaan migrasi skema tunggal, dan menghemat konsumsi koneksi database pool.
- Keamanan isolasi antar-kampus dijamin melalui otorisasi level Controller (`abort(403)` jika `institusi_id` tidak cocok) dan penyaringan query pada DataTables.

---

## 2. Model Pembayaran UKT: 1 Tagihan Semester dengan Multi-Cicilan (Model 1) vs Tagihan Terpisah Tiap Bulan (Model 2)

**Konteks :**  
Di banyak institusi, mahasiswa diperbolehkan membayar UKT semesteran secara bertahap / bulanan. Namun pada ERD Sevima, terdapat aturan keunikan:  
`UQ (institusi_id, mahasiswa_id, periode_akademik_id)`.

**Opsi :**  
1. *Model 1 (Induk Tagihan Semester + Multi-Cicilan Transaksi):* Satu tagihan diterbitkan per semester per mahasiswa. Pembayaran bulanan dicatat pada tabel `transaksi_pembayarans` (relasi 1:N). Status tagihan dievaluasi dinamis (`belum_bayar` &rarr; `dicicil` &rarr; `lunas`).
2. *Model 2 (Tagihan Terpisah Tiap Bulan):* Menambahkan kolom `bulan` atau `termin` pada `tagihan_ukts` dan menghapus / memodifikasi aturan keunikan ERD.

**Keputusan :**  
Memilih **Model 1 (Induk Tagihan Semester + Multi-Cicilan Transaksi)**.  
**Alasan:**  
- Mematuhi integritas ERD Sevima 100% tanpa mengubah constraint skema database.
- Memberikan fleksibilitas nyata bagi mahasiswa untuk mencicil UKT setiap bulan (atau melunasi langsung).
- Status pembayaran tagihan (`belum_bayar`, `dicicil`, `lunas`) secara presisi mencerminkan akumulasi transaksi yang berhasil diverifikasi.

---

## 3. Keamanan Verifikasi Webhook: HMAC-SHA256 Raw Payload vs Body Decoding

**Konteks :**  
Notifikasi pembayaran dari payment gateway rentan terhadap pemalsuan (*man-in-the-middle* atau *replay attack*). Brief mewajibkan verifikasi signature HMAC-SHA256 pada header `X-Signature` menggunakan `secret_key` institusi masing-masing.

**Opsi :**  
1. *HMAC dihitung dari array hasil JSON decode (`json_encode($request->all())`):* Rentan terhadap perbedaan urutan key (*key ordering*) atau normalisasi whitespace/formatting.
2. *HMAC dihitung dari Raw Body Mentah (`$request->getContent()`):* Signature dihitung tepat dari byte array string asli yang dikirimkan oleh gateway.

**Keputusan :**  
Memilih **Opsi 2 (Raw Body Mentah `$request->getContent()`)**.  
**Alasan:**  
- Memenuhi aturan teknis ERD No. 5: *"Signature dihitung dari body mentah (payload_asli) menggunakan secret_key yang terdaftar di institusi"*.
- Memakai fungsi `hash_equals()` untuk mencegah *timing attack* pada perbandingan string hash kriptografis.
- Menyimpan teks body mentah di kolom `payload_asli` pada `log_webhooks` untuk kebutuhan audit forensik dan verifikasi ulang (*replay*).

---

## 4. Penanganan Kegagalan & Idempotensi Webhook: Duplicate Detection vs Direct Update

**Konteks :**  
Payment gateway sering kali mengirimkan *retry notification* ketika terjadi latency jaringan, atau gateway mengalami timeout saat menerima respons 200 OK. Jika tidak ditangani, satu transaksi dapat diproses ganda.

**Opsi :**  
1. *Direct Update Tanpa Pengecekan Idempotensi:* Langsung memproses dan menjumlahkan pembayaran setiap kali webhook masuk.
2. *Idempotency Check via Event ID:* Menyimpan `gateway_event_id` pada `log_webhooks`. Jika event ID yang sama diterima kembali dan statusnya sudah `diproses`, sistem langsung mencatat log sebagai `duplikat` dan mengembalikan respons HTTP 200 OK tanpa memproses ulang saldo/transaksi.

**Keputusan :**  
Memilih **Opsi 2 (Idempotency Check via Event ID)**.  
**Alasan:**  
- Mencegah data inkonsisten atau double-crediting pada tagihan mahasiswa.
- Memenuhi catatan brief: *"Setiap percobaan webhook dicatat, termasuk duplikat dan signature tidak valid"*.

