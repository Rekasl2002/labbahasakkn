# CI4 Lab Bahasa (MVP lengkap, polling event)

Ini **bukan** full CodeIgniter 4 source. Ini adalah **file tambahan/override** yang kamu taruh ke project CI4 yang sudah dibuat via Composer.

## Prasyarat
- CodeIgniter 4 (disarankan 4.4+)
- PHP (sesuai CI4 yang kamu pakai)
- MySQL/MariaDB (Laragon)
- Apache/Nginx (Laragon)
- Ext: `intl` aktif (umumnya CI4 butuh)

## Cara pakai (Laragon)
1) Buat project CI4:
   ```bash
   composer create-project codeigniter4/appstarter labbahasa
   ```
2) Copy isi folder zip ini ke root project `labbahasa/` (merge/overwrite file yang sama).
3) Atur database di `.env`:
   - `database.default.hostname = localhost`
   - `database.default.database = labbahasa`
   - `database.default.username = root`
   - `database.default.password =`
   - `database.default.DBDriver = MySQLi`
4) Jalankan migration:
   ```bash
   php spark migrate
   ```
5) Jalankan seeder admin:
   ```bash
   php spark db:seed AdminSeeder
   ```
6) Jalankan server (opsional, bisa pakai Apache Laragon):
   ```bash
   php spark serve
   ```
7) Buka:
   - `/` untuk login
   - `/admin` setelah login admin
   - `/student` setelah login siswa

## Kredensial admin default
- username: `admin`
- password: `admin123`
> Ganti segera setelah login. Ini default untuk dev.

## Catatan LAN
Pastikan PC siswa akses ke IP host, contoh:
`http://192.168.1.10/labbahasa/public`

## Struktur fitur
- Sesi (start/end)
- Join siswa + daftar peserta
- Status mic/speaker (soft control)
- Chat public + private (admin <-> siswa)
- Materi (text/file upload) + broadcast
- Quick broadcast text (kata/kalimat tampil besar)
- Attendance & recap sesi (saat end session)
- Polling event (tanpa WebSocket)
