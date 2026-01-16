<p align="center">
  <img src="assets/images/logo-unsrat.png" alt="Logo UNSRAT" width="120"/>
</p>

<h1 align="center">INSPIRE Portal</h1>
<h3 align="center">Sistem Informasi Akademik dengan Fitur Perencanaan Studi</h3>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Bootstrap-4.6-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/AdminLTE-3.2-007bff?style=for-the-badge" alt="AdminLTE"/>
</p>

<p align="center">
  <strong>Tugas Akhir Mata Kuliah Pengembangan Web</strong><br/>
  Teknik Informatika - Universitas Sam Ratulangi
</p>

---

## Deskripsi

Proyek ini merupakan implementasi sistem portal akademik yang terinspirasi dari **INSPIRE Portal** Universitas Sam Ratulangi. Fokus utama pengembangan adalah penambahan fitur **Perencanaan Studi** yang belum tersedia di sistem aslinya memungkinkan mahasiswa untuk merencanakan pengambilan mata kuliah sebelum periode KRS dibuka.

## Fitur Utama

### Perencanaan Studi

Fitur yang membantu mahasiswa dalam perencanaan akademik:

| Fitur | Deskripsi |
|:------|:----------|
| **Status Matakuliah** | Melihat seluruh mata kuliah yang sudah dikontrak beserta status kelulusan |
| **Rekomendasi Kontrak Ulang** | Identifikasi mata kuliah bernilai D/E yang dapat dikontrak ulang |
| **Matakuliah Belum Dikontrak** | Daftar mata kuliah wajib yang belum pernah diambil |
| **Rencana Semester** | Susun rencana mata kuliah sebelum periode KRS |
| **Peringatan Evaluasi** | Notifikasi evaluasi bertahap sesuai standar UI: Sem 1-2 (SKS < 24 / IPK < 2.00), Sem 3-4 (SKS < 48 / IPK < 2.00), Sem 5-6 (SKS < 72 / IPK < 2.00), Sem 7+ (SKS < 96 / IPK < 2.00) |

### Fitur Akademik Lainnya

- **Autentikasi**: Login/logout dengan password hashing (bcrypt)
- **Dashboard**: Ringkasan informasi akademik mahasiswa
- **Transkrip Nilai**: Riwayat nilai per semester dengan ekspor PDF
- **KRS**: Kartu Rencana Studi dengan ekspor PDF
- **KHS**: Kartu Hasil Studi dengan ekspor PDF

### Keamanan & Realabilitas (New) 🛡️

Sistem ini telah melalui proses refactoring menyeluruh untuk memastikan keamanan dan kualitas kode standar industri:

- **Anti-SQL Injection**: Menggunakan *Prepared Statements* secara konsisten di seluruh modul autentikasi dan transaksi database.
- **XSS Prevention**: Output data user (NIM, Nama, Nilai) di-escape menggunakan `htmlspecialchars()` untuk mencegah Cross-Site Scripting.
- **CSRF Protection**: Form login dilengkapi token CSRF untuk mencegah serangan pemalsuan permintaan.
- **Secure Authentication**: Hashing password menggunakan `bcrypt` dan proteksi *Brute Force* dengan delay login.
- **Error Handling**: Penyembunyian pesan error database mentah dari pengguna akhir untuk mencegah kebocoran informasi sistem.

---

## Tech Stack

<table>
  <tr>
    <td align="center" width="120">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP"/>
      <br/><strong>PHP 8.x</strong>
      <br/><sub>Backend Native</sub>
    </td>
    <td align="center" width="120">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="48" height="48" alt="MySQL"/>
      <br/><strong>MySQL</strong>
      <br/><sub>Database</sub>
    </td>
    <td align="center" width="120">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/bootstrap/bootstrap-original.svg" width="48" height="48" alt="Bootstrap"/>
      <br/><strong>Bootstrap 4</strong>
      <br/><sub>CSS Framework</sub>
    </td>
    <td align="center" width="120">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="48" height="48" alt="JavaScript"/>
      <br/><strong>JavaScript</strong>
      <br/><sub>Frontend Logic</sub>
    </td>
  </tr>
</table>

**Tools & Libraries:**
- [AdminLTE 3.2](https://adminlte.io/) — Admin dashboard template
- [Font Awesome 5](https://fontawesome.com/) — Icon library
- [Laragon](https://laragon.org/) — Local development environment

---

## Instalasi

### Prerequisites

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+
- Web Server (Apache/Nginx) atau Laragon

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/dapzx2/project_inspire_2026.git

# 2. Masuk ke direktori proyek
cd project_inspire_2026
```

```sql
-- 3. Import database ke MySQL/phpMyAdmin
-- File: config/database.sql
```

```php
// 4. Konfigurasi koneksi database (config/database.php)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inspire_project";
```

```
# 5. Akses aplikasi
http://localhost/project_inspire_2026/
```

---

## Demo Access

| Nama | NIM | Password |
|:-----|:----|:---------|
| Dava Oktavito Josua L. Ulus | `220211060323` | `DAVAulus123` |
| Romal Putra Lengkong | `220211060242` | `Romal11#` |

---

## Struktur Proyek

```
project_inspire_2026/
│
├── assets/
│   ├── css/              # Stylesheet bundles
│   ├── js/               # JavaScript bundles
│   └── images/           # Logo & gambar
│
├── config/
│   ├── database.php      # Konfigurasi koneksi DB
│   └── database.sql      # Schema & data SQL
│
├── layout/
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
│
├── index.php             # Halaman login
├── auth.php              # Proses autentikasi
├── logout.php            # Proses logout
├── dashboard.php         # Dashboard utama
├── perencanaan.php       # Perencanaan studi (fitur utama)
├── transkrip.php         # Transkrip nilai
├── transkrip_cetak.php   # Cetak transkrip PDF
├── krs.php               # Kartu Rencana Studi
├── krs_cetak.php         # Cetak KRS PDF
├── khs.php               # Kartu Hasil Studi
└── khs_cetak.php         # Cetak KHS PDF
```

---

## Author

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/dapzx2">
        <img src="https://github.com/dapzx2.png" width="100px;" alt="Dava" style="border-radius:50%"/><br/>
        <strong>Dava Oktavito Josua L. Ulus</strong>
      </a>
      <br/>
      <sub>220211060323</sub>
      <br/>
      <sub>Teknik Informatika</sub>
      <br/>
      <sub>Universitas Sam Ratulangi</sub>
    </td>
  </tr>
</table>

---

## License

Proyek ini dibuat untuk keperluan **Tugas Akhir Mata Kuliah Pengembangan Web**.

<p align="center">
  <sub>© 2026 - Dava Oktavito Josua L. Ulus</sub>
</p>
