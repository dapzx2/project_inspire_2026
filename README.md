# INSPIRE Portal Clone

Portal akademik berbasis PHP Native yang terinspirasi dari sistem INSPIRE Universitas Sam Ratulangi. Project ini dibuat sebagai **proposal penambahan fitur baru** untuk portal INSPIRE yang sesungguhnya.

## Repository

GitHub: [https://github.com/dapzx2/project_inspire_2026](https://github.com/dapzx2/project_inspire_2026)

---

## Proposal Fitur: Perencanaan Studi

Fitur utama yang diusulkan dalam project ini adalah **Perencanaan Studi** - sebuah sistem yang membantu mahasiswa merencanakan mata kuliah untuk semester berikutnya.

### Latar Belakang

Portal INSPIRE saat ini belum memiliki fitur yang membantu mahasiswa dalam:
- Melihat status kelulusan mata kuliah semester ganjil/genap
- Mengidentifikasi mata kuliah yang perlu diulang (nilai D/E)
- Merencanakan pengambilan mata kuliah sebelum periode KRS dibuka

### Fitur yang Diusulkan

| Fitur | Deskripsi |
|-------|-----------|
| Status Matakuliah | Menampilkan daftar mata kuliah yang sudah dikontrak beserta status lulus/tidak lulus |
| Rekomendasi Kontrak Ulang | Menampilkan mata kuliah dengan nilai D/E yang dapat dikontrak ulang di semester berikutnya (berdasarkan semester ganjil/genap) |
| Matakuliah Belum Dikontrak | Menampilkan mata kuliah wajib yang belum pernah dikontrak |
| Rencana Studi | Mahasiswa dapat menambahkan mata kuliah ke dalam daftar rencana sebelum periode KRS |
| Peringatan Evaluasi | Menampilkan peringatan jika SKS lulus < 96 atau IPK < 2.00 pada semester 7+ |

### Manfaat

1. **Bagi Mahasiswa**: Dapat merencanakan studi dengan lebih baik, menghindari keterlambatan kelulusan
2. **Bagi Dosen PA**: Memudahkan konsultasi perencanaan studi dengan mahasiswa bimbingan
3. **Bagi Akademik**: Mengurangi kasus mahasiswa yang terlambat menyadari kekurangan SKS

---

## Fitur Lainnya

- Login/Logout dengan hashing password
- Dashboard ringkasan informasi akademik
- Transkrip nilai per semester dengan cetak PDF
- KRS (Kartu Rencana Studi) dengan cetak PDF

## Teknologi

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: AdminLTE 3, Bootstrap 4, Font Awesome 5

## Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/dapzx2/project_inspire_2026.git
   ```

2. Import database `config/database.sql` ke phpMyAdmin

3. Konfigurasi `config/database.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_inspire_project";
   ```

4. Akses: `http://localhost/project_inspire_2026/`

## Login Demo

- **NIM**: 220211060323
- **Password**: DAVAulus123

## Struktur Folder

```
├── assets/css/       # Stylesheet (auth.bundle.css, dashboard.bundle.css)
├── assets/js/        # JavaScript (auth.bundle.js, dashboard.bundle.js)
├── assets/images/    # Logo & gambar
├── config/           # Konfigurasi database
├── layout/           # Header & footer template
├── index.php         # Halaman login
├── dashboard.php     # Dashboard utama
├── perencanaan.php   # Perencanaan studi (fitur utama)
├── transkrip.php     # Transkrip nilai
└── krs.php           # KRS
```

## Author

**Dava Oktavito Josua L. Ulus**  
Teknik Informatika - Universitas Sam Ratulangi

## License

Project ini dibuat untuk keperluan edukasi dan sebagai proposal pengembangan sistem INSPIRE.
