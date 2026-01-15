# INSPIRE Portal Clone

Portal akademik berbasis PHP Native yang terinspirasi dari sistem INSPIRE Universitas Sam Ratulangi.

## 🔗 Repository

**GitHub**: [https://github.com/dapzx2/project_inspire_2026](https://github.com/dapzx2/project_inspire_2026)

## ✨ Fitur

- 🔐 **Login/Logout** - Autentikasi dengan hashing password
- 📊 **Dashboard** - Ringkasan informasi akademik mahasiswa
- 📋 **Transkrip** - Daftar nilai per semester dengan cetak PDF
- 📝 **KRS** - Kartu Rencana Studi dengan cetak PDF
- 📅 **Perencanaan Studi** - Perencanaan mata kuliah untuk semester berikutnya

## 🛠️ Teknologi

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: AdminLTE 3, Bootstrap 4, Font Awesome 5

## 🚀 Instalasi

1. **Clone repository**:
   ```bash
   git clone https://github.com/dapzx2/project_inspire_2026.git
   cd project_inspire_2026
   ```

2. **Import database**:
   - Buat database `db_inspire_project` di phpMyAdmin
   - Import file `config/database.sql`

3. **Konfigurasi database** di `config/database.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_inspire_project";
   ```

4. **Akses melalui browser**:
   ```
   http://localhost/project_inspire_2026/
   ```

## 🔑 Login Demo

| Field    | Value          |
|----------|----------------|
| NIM      | 220211060323   |
| Password | DAVAulus123    |

## 📁 Struktur Folder

```
project_inspire_2026/
├── assets/
│   ├── css/          # auth.bundle.css, dashboard.bundle.css
│   ├── js/           # auth.bundle.js, dashboard.bundle.js
│   └── images/       # Logo & default images
├── config/
│   ├── database.php  # Database connection
│   └── database.sql  # Database schema & data
├── layout/
│   ├── header.php    # Header template
│   └── footer.php    # Footer template
├── index.php         # Login page
├── dashboard.php     # Main dashboard
├── perencanaan.php   # Study planning
├── transkrip.php     # Transcript view
├── krs.php           # KRS view
└── README.md
```

## 👨‍💻 Author

**Dava Oktavito Josua L. Ulus**  
Teknik Informatika - Universitas Sam Ratulangi

## 📜 License

This project is for educational purposes only.
