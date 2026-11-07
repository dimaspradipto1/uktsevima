# AI Notes — Refleksi Pairing dengan AI

## Bagian yang dikerjakan bersama AI
- **Perancangan Skema Database & Migrasi:** Menerjemahkan diagram ERD Sevima (*uktsevima*) ke dalam migrasi Laravel dengan PostgreSQL, termasuk aturan *compound unique constraint* seperti `(institusi_id, nomor_induk_mahasiswa)`, `(institusi_id, mahasiswa_id, periode_akademik_id)`, dan `(institusi_id, gateway_transaction_id)`.
- **Integrasi Yajra DataTables & UI NiceAdmin:** Membangun antarmuka tabel dinamis dengan sequential index column (`DT_RowIndex`), modal popup create/edit, serta pemisahan portal admin dan portal mahasiswa.
- **Implementasi Verifikasi HMAC-SHA256:** Membangun algoritma validasi webhook signature berbasis raw body payload (`payload_asli`) dan institusi `secret_key` dengan `hash_equals()`.
- **Pembuatan Automated Tests (27 Test Cases):** Menyusun unit & feature test suite lengkap untuk validasi CRUD, composite unique constraints, isolasi data multi-tenant, dan webhook endpoint.

## Satu contoh saran AI yang saya tolak/koreksi, dan alasannya
- **Saran Awal AI:** Pada pembuatan antarmuka tagihan UKT, AI sempat mengasumsikan pembayaran UKT hanya bersifat *one-time payment* (sekali bayar langsung lunas), atau membuat tagihan baru yang terpisah setiap bulan (Model 2).
- **Koreksi & Keputusan Saya:** Saya mengoreksi model tersebut dan mengarahkan ke **Model 1**: Satu tagihan semester dapat dibayar secara bertahap / cicilan bulanan melalui relasi 1:N ke `transaksi_pembayarans`. Alasan saya adalah agar tetap mematuhi aturan integritas ERD `UQ(institusi_id, mahasiswa_id, periode_akademik_id)` tanpa mengubah skema tabel, sekaligus memberikan fleksibilitas pembayaran bulanan yang nyata bagi mahasiswa.
- **Koreksi UI:** AI sempat menambahkan badge icon gedung (`bi-building`) pada nama universitas di DataTables, yang saya tolak dan instruksikan agar diganti menjadi teks biasa tanpa background kartu agar tampilan tabel lebih bersih dan profesional.

## Bagian yang saya kerjakan tanpa AI, dan alasannya
- **Penetapan Hierarki & Alur Bisnis SEVIMA:** Menentukan batasan isolasi data kampus (multi-tenant shared schema) dan aturan bahwa admin institusi tidak boleh melihat institusi lain.
- **Pengambilan Keputusan Model Pembayaran UKT:** Memilih Model 1 (Tagihan Induk Semester + Pembayaran Cicilan) untuk menyeimbangkan kebutuhan operasional kampus dan kepatuhan terhadap diagram ERD.
- **Review Kemanan & Kepatuhan Brief:** Memastikan tidak ada secret HMAC yang bocor di repositori git serta memverifikasi bahwa Livewire tidak digunakan sesuai larangan dalam spesifikasi tes teknis SEVIMA.

## Kalau mengulang dari awal, apa yang akan saya ubah
- **Mendefinisikan Format API & Header Kontrak di Awal:** Saya akan mendefinisikan kontrak header webhook (`X-Signature` dan `X-Institusi-Code`) serta format penamaan kolom (`nominal_rupiah` vs `nominal_bayar`) sejak sprint pertama sebelum menjalankan migrasi tabel awal.
- **Penerapan Global Query Scope Otomatis:** Menggunakan Laravel Eloquent `GlobalScope` (misal `InstitusiScope`) pada model untuk mengotomatisasi penyaringan `where('institusi_id', auth()->user()->institusi_id)` sehingga tidak perlu difilter manual di setiap method controller.

