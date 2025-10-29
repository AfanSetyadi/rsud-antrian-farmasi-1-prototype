# Sistem Antrian Farmasi 11 RSUD R.T. Notopuro Sidoarjo

Sistem antrian digital untuk Farmasi 11 dengan integrasi database PostgreSQL dan printer thermal.

## Fitur

- ✅ Antrian otomatis dengan nomor urut harian
- ✅ Penyimpanan data antrian ke database PostgreSQL
- ✅ Integrasi printer thermal (cetak 2x otomatis)
- ✅ Interface web responsif dengan Tailwind CSS
- ✅ Real-time update tanggal dan waktu
- ✅ Error handling dan feedback user

## Persyaratan Sistem

- Web server dengan PHP 7.4+ dan PDO PostgreSQL extension
- PostgreSQL database
- Printer thermal dengan endpoint print di `http://localhost/direct-print/pakai_usb.php`

## Setup Database

1. Buat database PostgreSQL dengan nama `farmasi_antrian`
2. Jalankan script SQL dari file `setup_database.sql`
3. Sesuaikan konfigurasi database di file `config.php`

## Konfigurasi

Edit file `config.php` untuk menyesuaikan:

```php
$host = 'localhost';           // Host database
$dbname = 'farmasi_antrian';   // Nama database
$username = 'postgres';        // Username database
$password = '';                // Password database
$print_endpoint = 'http://localhost/direct-print/pakai_usb.php'; // Endpoint printer
$id_loket = 11;               // ID loket (Farmasi 11)
$id_jenis_antrian = 1;        // ID jenis antrian
```

## Struktur File

```
farmasi-1/
├── index.php              # Halaman utama sistem antrian
├── config.php             # Konfigurasi database dan printer
├── setup_database.sql     # Script setup database
├── api/
│   ├── take_number.php    # API ambil nomor antrian baru
│   └── get_current_number.php # API ambil nomor antrian terakhir
└── README.md              # Dokumentasi ini
```

## API Endpoints

### GET `/api/get_current_number.php`
Mengambil nomor antrian terakhir hari ini.

**Response:**
```json
{
  "success": true,
  "currentNumber": "F048",
  "timestamp": "2024-01-15 14:30:00"
}
```

### POST `/api/take_number.php`
Membuat nomor antrian baru, simpan ke database, dan cetak tiket.

**Response:**
```json
{
  "success": true,
  "queueNumber": "F049",
  "timestamp": "2024-01-15T14:30:15+07:00",
  "printSuccess": true,
  "printErrors": {
    "first": "",
    "second": ""
  }
}
```

## Cara Penggunaan

1. Buka `index.php` di web browser
2. Klik tombol "Ambil Nomor Antrian" atau tekan Space/Enter
3. Sistem akan:
   - Generate nomor antrian baru
   - Simpan ke database
   - Cetak tiket 2x secara otomatis
   - Tampilkan konfirmasi ke user

## Troubleshooting

### Database Connection Error
- Pastikan PostgreSQL service berjalan
- Cek konfigurasi di `config.php`
- Pastikan database dan tabel sudah dibuat

### Print Error
- Pastikan endpoint printer dapat diakses
- Cek koneksi printer thermal
- Verifikasi URL print endpoint di `config.php`

### Nomor Antrian Tidak Update
- Cek console browser untuk error JavaScript
- Pastikan API endpoints dapat diakses
- Verifikasi permission file dan folder

## Pengembangan Lebih Lanjut

- [ ] Dashboard admin untuk monitoring antrian
- [ ] Sistem panggilan antrian dengan speaker
- [ ] Integrasi dengan sistem informasi rumah sakit
- [ ] Laporan harian/bulanan antrian
- [ ] Notifikasi WhatsApp untuk pasien