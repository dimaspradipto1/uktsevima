# UKTHub Multi-Kampus — SEVIMA Project M2: Integrasi Pembayaran UKT via Webhook

Aplikasi web fullstack multi-tenant terpadu untuk pengelolaan tagihan Uang Kuliah Tunggal (UKT) antar-institusi kampus dengan verifikasi notifikasi webhook payment gateway berbasis **HMAC-SHA256**, portal admin keuangan, dan portal mahasiswa.

---

## 1. Stack Teknologi & Prasyarat
- **Bahasa & Framework:** PHP 8.4+ / Laravel 13
- **Database:** PostgreSQL (Wajib sesuai brief SEVIMA)
- **Frontend:** Blade Templates + Bootstrap 5 + NiceAdmin Template + Yajra DataTables + SweetAlert2
- **Testing:** PHPUnit / Pest Test Framework (27 Feature & Unit Tests)

---

## 2. Cara Setup & Menjalankan Aplikasi

### Langkah Instalasi:
1. **Clone Repository:**
   ```bash
   git clone https://github.com/dimaspradipto1/uktsevima.git
   cd uktsevima
   ```
2. **Install Dependensi Composer:**
   ```bash
   composer install
   ```
3. **Konfigurasi Environment (`.env`):**
   Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Sesuaikan konfigurasi database PostgreSQL Anda di `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=uktsevima
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```
4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```
5. **Migrasi Database & Seeder Data Awal (Minimal 3 Institusi):**
   ```bash
   php artisan migrate --seed
   ```
6. **Jalankan Web Server:**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses pada browser di: `http://127.0.0.1:8000`

7. **Menjalankan Automated Tests:**
   ```bash
   php artisan test
   ```

---

## 3. Akun Demo untuk Setiap Role

Semua akun demo menggunakan password default: **`admin123`**

### A. Super Administrator (Global Monitoring Platform)
- **Email:** `superadmin@sevima.com` atau `admin@gmail.com`
- **Password:** `admin123`
- **Hak Akses:** Dapat memantau dan mengelola seluruh kampus, institusi, tagihan, dan log webhook di tingkat sistem.

### B. Admin Keuangan / Finance Admin (Tenant-Scoped per Kampus)
Sesuai aturan bisnis, Admin Kampus hanya dapat melihat dan mengelola data milik kampusnya sendiri:
| Institusi Kampus | Kode | Email Login Admin | Password |
| :--- | :--- | :--- | :--- |
| **Universitas Sevima Nusantara** | `UNIV01` | `admin.univ@sevima.com` | `admin123` |
| **Politeknik Negeri Teknologi Edukasi** | `POLI02` | `admin.poli@sevima.com` | `admin123` |
| **STMIK Cipta Digital Edukasi** | `STMIK03` | `admin.stmik@sevima.com` | `admin123` |

### C. Akun Mahasiswa (Portal Mahasiswa)
Mahasiswa memiliki antarmuka khusus untuk memantau status tagihan berjalan, riwayat cicilan, dan simulasi bayar cicilan:
| Nama Mahasiswa | NIM | Kampus | Email Login | Password |
| :--- | :--- | :--- | :--- | :--- |
| **Dimas Pradipto** | `2024010001` | Universitas Sevima Nusantara | `dimas@gmail.com` | `admin123` |
| **Siti Nurhaliza** | `2024010002` | Universitas Sevima Nusantara | `siti@gmail.com` | `admin123` |
| **Budi Santoso** | `2024020001` | Politeknik Negeri Teknologi | `budi@gmail.com` | `admin123` |
| **Rina Andriani** | `2024020002` | Politeknik Negeri Teknologi | `rina@gmail.com` | `admin123` |

---

## 4. Dokumentasi Webhook Payment Gateway (Mock Gateway)

### A. Endpoint Webhook
- **URL:** `POST /api/webhook-payment`
- **Content-Type:** `application/json`

### B. Header Wajib:
| Header | Keterangan |
| :--- | :--- |
| `X-Signature` | Signature HMAC-SHA256 yang dihitung dari *raw body mentah* payload menggunakan `secret_key` institusi. |
| `X-Institusi-Code` | Kode institusi kampus penerima pembayaran (misal: `UNIV01`, `POLI02`, `STMIK03`). |
| `X-Gateway-Event-Id` | ID unik event notifikasi (untuk deteksi duplikasi / idempotency). |

### C. Secret Key Institusi (Tersimpan di Database):
| Institusi Kampus | Kode Institusi | Secret HMAC Key (Demo) |
| :--- | :--- | :--- |
| Universitas Sevima Nusantara | `UNIV01` | `e4d909c290d0fb1ca068ffaddf22cbd0add0b7b12d5930219c00b58e65842813` |
| Politeknik Negeri Teknologi | `POLI02` | `9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08` |
| STMIK Cipta Digital | `STMIK03` | `4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a` |

### D. Format Payload JSON:
#### 1. Event: `payment.success` (Pembayaran Berhasil)
```json
{
  "event_id": "EVT-2024-00123",
  "event": "payment.success",
  "kode_institusi": "UNIV01",
  "gateway_transaction_id": "TRX-UNIV01-2024-001",
  "nominal_bayar": 1500000,
  "metode_pembayaran": "VA",
  "status": "berhasil",
  "timestamp": "2024-11-07T12:00:00Z"
}
```

#### 2. Event: `payment.expired` (Pembayaran Kedaluwarsa)
```json
{
  "event_id": "EVT-2024-00124",
  "event": "payment.expired",
  "kode_institusi": "UNIV01",
  "gateway_transaction_id": "TRX-UNIV01-2024-001",
  "status": "kedaluwarsa"
}
```

### E. Format Respons HTTP:
- **HTTP 200 OK (Berhasil Diproses atau Duplikat Diabaikan):**
  ```json
  {
    "status": "diproses",
    "message": "Webhook successfully verified and processed."
  }
  ```
- **HTTP 401 Unauthorized (Signature HMAC Tidak Cocok):**
  ```json
  {
    "status": "ditolak",
    "message": "Invalid HMAC-SHA256 signature."
  }
  ```
- **HTTP 404 Not Found (Institusi Tidak Terdaftar):**
  ```json
  {
    "status": "ditolak",
    "message": "Institusi not found for code: XXX"
  }
  ```

### F. Cara Memicu Webhook:
1. **Melalui UI Web Dashboard (Simulator):**
   Login sebagai admin, buka menu **Integrasi Gateway &rarr; Log Webhook**, lalu klik tombol **"Uji Coba Webhook (HMAC)"**. Anda dapat memilih skenario *Valid Signature* atau *Invalid Signature* secara interaktif.
2. **Melalui cURL / Postman:**
   ```bash
   # Hitung HMAC di terminal/bash:
   # echo -n '{"event_id":"EVT-01","event":"payment.success","kode_institusi":"UNIV01","gateway_transaction_id":"TRX-UNIV01-2024-001","nominal_bayar":1500000}' | openssl dgst -sha256 -hmac "e4d909c290d0fb1ca068ffaddf22cbd0add0b7b12d5930219c00b58e65842813"

   curl -X POST http://127.0.0.1:8000/api/webhook-payment \
     -H "Content-Type: application/json" \
     -H "X-Institusi-Code: UNIV01" \
     -H "X-Signature: <HASIL_HASH_HMAC>" \
     -H "X-Gateway-Event-Id: EVT-01" \
     -d '{"event_id":"EVT-01","event":"payment.success","kode_institusi":"UNIV01","gateway_transaction_id":"TRX-UNIV01-2024-001","nominal_bayar":1500000}'
   ```

---

## 5. Endpoint API Mahasiswa (Requirement No. 5)

Endpoint untuk melihat status tagihan dan riwayat transaksi seorang mahasiswa secara terprogram:
- **URL:** `GET /api/mahasiswa/{nim}/tagihan`
- **Contoh Request:** `GET http://127.0.0.1:8000/api/mahasiswa/2024010001/tagihan`
- **Contoh Format Respons:**
  ```json
  {
    "status": "success",
    "data": {
      "mahasiswa": {
        "nim": "2024010001",
        "nama": "Dimas Pradipto",
        "institusi": "Universitas Sevima Nusantara",
        "kode_institusi": "UNIV01"
      },
      "tagihan_aktif": {
        "id": 1,
        "nomor_tagihan": "INV-UNIV01-2024-00001",
        "periode": "2024/2025 (Ganjil)",
        "nominal": 5000000,
        "total_dibayar": 1500000,
        "sisa_tagihan": 3500000,
        "status": "dicicil",
        "jatuh_tempo": "2024-12-31"
      },
      "riwayat_tagihan": [ ... ],
      "riwayat_transaksi": [
        {
          "gateway_transaction_id": "TRX-UNIV01-2024-001",
          "nominal": 1500000,
          "metode": "VA",
          "status": "berhasil",
          "dibayar_pada": "2024-11-02 12:44:19"
        }
      ]
    }
  }
  ```

---

## 6. Asumsi Teknis
1. **Model Cicilan Tagihan:** Sesuai kesepakatan, 1 tagihan semester dapat dibayar berkali-kali secara cicilan/bertahap per bulan hingga batas jatuh tempo semester berakhir.
2. **Idempotensi Webhook:** Setiap event webhook membawa `gateway_event_id` yang unik. Jika gateway mengirim ulang event yang sama yang sudah diproses, sistem mengembalikan HTTP 200 OK dengan status log `duplikat` tanpa mengubah data tagihan dua kali.
3. **Isolasi Multi-Tenant:** Data antar-kampus diisolasi secara ketat. Admin dari satu kampus tidak diizinkan mengakses data kampus lain (menghasilkan HTTP 403 Forbidden).

---

## 7. Hasil Pengujian Otomatis (Automated Tests)
Aplikasi dilengkapi dengan 27 skenario pengujian komprehensif:
- `MultiTenantIsolationTest`: Menguji isolasi data antar-kampus, pencegahan akses silang, dan penolakan pemalsuan signature.
- `LogWebhookTest`: Menguji verifikasi HMAC-SHA256, penolakan signature palsu, dan update status transaksi.
- `TagihanUktTest`: Menguji validasi compound uniqueness, pembuatan tagihan, dan perubahan status pembayaran.
- `TransaksiPembayaranTest`: Menguji pencatatan transaksi dan sinkronisasi saldo tagihan.
- `MahasiswaTest`: Menguji CRUD mahasiswa dan integritas relasi.

Jalankan test suite dengan:
```bash
php artisan test
```
*Hasil: 27 passed (78 assertions)*.
