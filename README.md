# INSPIRE Portal Clone

Portal akademik berbasis PHP Native yang terinspirasi dari sistem INSPIRE Universitas Sam Ratulangi.

## Fitur

- 🔐 **Login/Logout** - Autentikasi dengan hashing password
- 📊 **Dashboard** - Ringkasan informasi akademik mahasiswa
- 📋 **Transkrip** - Daftar nilai per semester dengan cetak PDF
- 📝 **KRS** - Kartu Rencana Studi dengan cetak PDF
- 📅 **Perencanaan Studi** - Perencanaan mata kuliah untuk semester berikutnya

## Teknologi

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: AdminLTE 3, Bootstrap 4, Font Awesome 5

## Instalasi

1. Clone repository ini ke folder web server (htdocs/www):
   ```bash
   git clone https://github.com/username/inspire-portal.git
   ```

2. Import database:
   - Buat database `db_inspire_project` di phpMyAdmin
   - Import file `config/database.sql`

3. Konfigurasi database di `config/database.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_inspire_project";
   ```

4. Akses melalui browser:
   ```
   http://localhost/inspire-portal/
   ```

## Login Demo

- **NIM**: 220211060323
- **Password**: DAVAulus123

## Struktur Folder

```
├── assets/
│   ├── css/          # Stylesheet files
│   ├── js/           # JavaScript files
│   └── images/       # Image assets
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

## Author

**Dava Oktavito Josua L. Ulus**  
Teknik Informatika - Universitas Sam Ratulangi

## License

This project is for educational purposes only.
