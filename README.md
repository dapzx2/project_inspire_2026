<div align="center">
  <img src="assets/images/logo-unsrat.png" alt="Logo UNSRAT" width="120" draggable="false" />

  <h1>INSPIRE Portal</h1>
  <p>
    <strong>Sistem Informasi Akademik dan Perencanaan Studi</strong><br>
    Universitas Sam Ratulangi
  </p>

  <br>

  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-4.6-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/AdminLTE-3.2-343a40?style=for-the-badge&logo=adminlte&logoColor=white" alt="AdminLTE">

  <br><br>
</div>

---

## Daftar Isi

- [Gambaran Umum Sistem](#gambaran-umum-sistem)
- [Kapabilitas Sistem](#kapabilitas-sistem)
- [Arsitektur Teknologi](#arsitektur-teknologi)
- [Panduan Instalasi](#panduan-instalasi)
- [Kredensial Akses](#kredensial-akses)
- [Struktur Proyek](#struktur-proyek)
- [Penulis](#penulis)

---

## Gambaran Umum Sistem

**INSPIRE Portal** adalah sistem informasi akademik yang dibuat berdasarkan portal mahasiswa Universitas Sam Ratulangi. Proyek ini dibuat menggunakan PHP native dengan fitur autentikasi, database relasional, dan pembuatan dokumen PDF.

Fitur utama dari sistem ini adalah modul **Perencanaan Studi**. Fitur ini membantu mahasiswa untuk menyusun rencana pengambilan mata kuliah semester depan, mengecek prasyarat, dan batas SKS sebelum KRS dibuka.

---

## Kapabilitas Sistem

<details open>
<summary><b>01. Modul Perencanaan Studi</b></summary>
<br>

Fitur ini membantu mahasiswa menyusun rencana studi.

| Fitur | Deskripsi |
| :--- | :--- |
| **Validasi Mata Kuliah** | Pengecekan otomatis ketersediaan mata kuliah dan prasyaratnya. |
| **Rekomendasi Kontrak Ulang** | Menampilkan mata kuliah bernilai D atau E yang bisa diperbaiki. |
| **Kalkulasi Kredit** | Perhitungan batas SKS (Satuan Kredit Semester) secara real-time berdasarkan IPK sebelumnya. |
| **Pelacakan Mata Kuliah Wajib** | Daftar mata kuliah wajib yang belum diambil. |

</details>

<details>
<summary><b>02. Dashboard Mahasiswa</b></summary>
<br>

Halaman utama yang menampilkan informasi mahasiswa.

- **Ringkasan Akademik**: Menampilkan IPK saat ini, total kredit (SKS), dan status semester.
- **Status Keuangan**: Verifikasi status pembayaran UKT untuk semester aktif.
- **Informasi Pembimbing**: Akses langsung ke detail dosen pembimbing akademik.

</details>

<details>
<summary><b>03. Administrasi & Arsip Akademik</b></summary>
<br>

Dokumen-dokumen akademik yang bisa diakses dan dicetak.

- **Transkrip**: Lihat dan cetak transkrip lengkap dalam format PDF.
- **KRS**: Lihat dan cetak rencana studi semester ini.
- **KHS**: Lihat rincian nilai per semester dan download laporannya.

</details>

<details>
<summary><b>04. Keamanan & Arsitektur</b></summary>
<br>

Sistem ini dibuat dengan standar keamanan yang baik.

- **Secure Authentication**: Implementasi `bcrypt` untuk *hashing* password dan manajemen sesi.
- **SQL Injection Prevention**: Penggunaan menyeluruh **Prepared Statements** untuk semua interaksi database.
- **XSS Protection**: *Escaping* output yang ketat menggunakan `htmlspecialchars` untuk mencegah Cross-Site Scripting.
- **CSRF Tokens**: Validasi form disertakan untuk mencegah serangan Cross-Site Request Forgery.

</details>

---

## Arsitektur Teknologi

Sistem ini dibangun dengan arsitektur sederhana yang mudah di-deploy.

| Komponen | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Bahasa Backend** | PHP 8.1+ | Logika server-side inti (Implementasi Native). |
| **Database** | MySQL / MariaDB | Sistem manajemen basis data relasional. |
| **Frontend Framework** | Bootstrap 4.6 | Grid UI responsif dan pustaka komponen. |
| **Template Admin** | AdminLTE 3.2 | Antarmuka dashboard profesional. |
| **PDF Engine** | DomPDF | Mesin untuk pembuatan PDF di sisi server. |

---

## Panduan Instalasi

Ikuti instruksi berikut untuk menjalankan aplikasi di lingkungan pengembangan lokal.

### Prasyarat

Pastikan perangkat lunak berikut telah terinstal:
*   **Web Server**: Apache atau Nginx (Direkomendasikan menggunakan Laragon)
*   **PHP**: Versi 8.0 atau lebih tinggi
*   **Database**: MySQL 5.7 atau lebih tinggi

### Langkah Instalasi

1.  **Clone Repositori**

    ```bash
    git clone https://github.com/dapzx2/project_inspire_2026.git
    cd project_inspire_2026
    ```

2.  **Konfigurasi Database**

    Buat database baru bernama `db_inspire_project` dan impor skema database.

    ```bash
    mysql -u root -p db_inspire_project < config/database.sql
    ```

3.  **Konfigurasi Aplikasi**

    Verifikasi kredensial database pada file `config/database.php`.

    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_inspire_project";
    ```

4.  **Jalankan Aplikasi**

    Akses aplikasi melalui web server Anda.
    
    ```text
    http://localhost/project_inspire_2026/
    ```

---

## Kredensial Akses

Gunakan kredensial berikut untuk keperluan pengujian dan demonstrasi.

| Peran | Nama | Username (NIM) | Password |
| :--- | :--- | :--- | :--- |
| **Mahasiswa** | Dava Oktavito Josua L. Ulus | `220211060323` | `DAVAulus123` |
| **Mahasiswa** | Romal Putra Lengkong | `220211060242` | `Romal11#` |

---

## Struktur Proyek

```text
project_inspire_2026/
├── assets/                  # CSS, JS, Images
├── config/                  # Konfigurasi database
├── layout/                  # Header & Footer
├── auth.php                 # Proses login
├── dashboard.php            # Halaman utama
├── perencanaan.php          # Fitur perencanaan studi
├── transkrip.php            # Lihat transkrip
└── index.php                # Halaman login
```

---

## Penulis

<table style="border: none;">
  <tr>
    <td align="center" style="border: none;">
      <a href="https://github.com/dapzx2">
        <img src="https://github.com/dapzx2.png" width="100" style="border-radius: 50%"><br>
        <b>Dava Oktavito Josua L. Ulus</b>
      </a>
      <br>
      <i>Mahasiswa Teknik Informatika</i><br>
      Universitas Sam Ratulangi
    </td>
  </tr>
</table>

---

<p align="center">
  <small>&copy; 2026 Dava Oktavito Josua L. Ulus. Hak Cipta Dilindungi.</small>
</p>
