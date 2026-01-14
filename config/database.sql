-- ============================================
-- Database: db_inspire_project
-- Portal INSPIRE - Universitas Sam Ratulangi
-- ============================================

-- Use the existing database
USE db_inspire_project;

-- Drop existing tables (order matters due to foreign keys)
DROP TABLE IF EXISTS transkrip;
DROP TABLE IF EXISTS krs;
DROP TABLE IF EXISTS perencanaan_studi;
DROP TABLE IF EXISTS pengumuman;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS mata_kuliah;

-- ============================================
-- TABEL USERS
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    foto_profil VARCHAR(255) DEFAULT 'assets/images/user_default.png',
    
    -- Status Akademik
    semester INT DEFAULT 1,
    tahun_akademik VARCHAR(20) DEFAULT '2025/2026',
    periode VARCHAR(10) DEFAULT 'Gasal',
    status VARCHAR(50) DEFAULT 'Aktif',
    status_pddikti VARCHAR(50) DEFAULT 'Aktif',
    
    -- Data Akademik Tambahan
    angkatan VARCHAR(4),
    fakultas VARCHAR(100),
    prodi VARCHAR(100),
    jenjang VARCHAR(10),
    pembimbing_akademik VARCHAR(100),
    
    -- Statistik
    masa_studi INT DEFAULT 1,
    sisa_masa_studi INT DEFAULT 14,
    ipk DECIMAL(3,2) DEFAULT 0.00,
    sks_lulus INT DEFAULT 0,
    sks_diambil INT DEFAULT 0,
    
    has_academic_warning TINYINT(1) DEFAULT 0,
    warning_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert user - Password: DAVAulus123
INSERT INTO users (nim, password, nama, angkatan, fakultas, prodi, jenjang, status, semester, sks_lulus, ipk, pembimbing_akademik, foto_profil) VALUES
('220211060323', '$2y$12$VZclEMIjVHn2PmQAbJ8oO.vlh.zv3rFq9bNoed2BQRtOSg.gcodvq', 'DAVA OKTAVITO JOSUA L. ULUS', '2022', 'Teknik', 'Teknik Informatika', 'S1', 'Aktif', 7, 120, 3.55, 'Dr. TIK, S.T., M.T.', 'assets/img/default-profile.jpg');

-- ============================================
-- TABEL PENGUMUMAN
-- ============================================
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT,
    kategori VARCHAR(50),
    oleh VARCHAR(100),
    role VARCHAR(50) DEFAULT 'DOSEN MATAKULIAH',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pengumuman (judul, isi, kategori, oleh, role) VALUES
('[PENGEMBANGAN GAME] Link Demo UAS', 'Demo UAS meet.google.com/zfy-suiy-bnn', 'Matakuliah', 'Ir. SUMENGE TANGKAWAROUW GODION KAUNANG MT, Ph.D', 'DOSEN MATAKULIAH'),
('[REALITAS TERTAMBAH DAN REALITAS VIRTUAL]', '39SBJQ85', 'Matakuliah', 'WAHYUNI FITHRATUL ZALMI S.Kom., M.Kom', 'DOSEN MATAKULIAH'),
('[KRIPTOGRAFI] Absensi UAS', '05HCHM85', 'Matakuliah', 'RENDY SYAHPUTRA S.Kom., M.Kom', 'DOSEN MATAKULIAH');

-- ============================================
-- TABEL TRANSKRIP
-- ============================================
CREATE TABLE transkrip (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    kode_mk VARCHAR(20) NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL DEFAULT 3,
    nilai_huruf VARCHAR(2) NOT NULL,
    bobot DECIMAL(3,2) NOT NULL,
    semester INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEMESTER 1
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK1011', 'Pendidikan Agama', 2, 'B', 3.00, 1),
('220211060323', 'TIK1021', 'Pancasila', 2, 'B+', 3.50, 1),
('220211060323', 'TIK1031', 'Bahasa Indonesia', 2, 'A', 4.00, 1),
('220211060323', 'TIK1041', 'Kewarganegaraan', 2, 'B+', 3.50, 1),
('220211060323', 'TIK1051', 'Matematika Diskrit', 3, 'E', 0.00, 1),
('220211060323', 'TIK1061', 'Kalkulus', 3, 'C+', 2.50, 1),
('220211060323', 'TIK1071', 'Probabilitas Dan Statistika', 2, 'D', 1.00, 1),
('220211060323', 'TIK1081', 'Algoritma Dan Pemrograman Komputer', 4, 'C+', 2.50, 1),
('220211060323', 'TIK1091', 'Prak. Algoritma Dan Pemrograman Komputer', 1, 'C+', 2.50, 1);

-- SEMESTER 2
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK1012', 'Bahasa Inggris', 2, 'C', 2.00, 2),
('220211060323', 'TIK1022', 'Pengetahuan Kepasifikan', 2, 'A', 4.00, 2),
('220211060323', 'TIK1032', 'Pengantar Teknik Informatika', 2, 'C', 2.00, 2),
('220211060323', 'TIK1042', 'Aljabar Linear', 2, 'E', 0.00, 2),
('220211060323', 'TIK1052', 'Metode Numerik', 3, 'E', 0.00, 2),
('220211060323', 'TIK1072', 'Komputer Dan Masyarakat', 2, 'B+', 3.50, 2),
('220211060323', 'TIK1082', 'Basis Data', 3, 'B', 3.00, 2),
('220211060323', 'TIK1092', 'Praktikum Basis Data', 1, 'E', 0.00, 2);

-- SEMESTER 3
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK2011', 'Sistem Informasi', 3, 'B', 3.00, 3),
('220211060323', 'TIK2021', 'Arsitektur Dan Organisasi Komputer', 3, 'A', 4.00, 3),
('220211060323', 'TIK2031', 'Sistem Operasi', 3, 'C+', 2.50, 3),
('220211060323', 'TIK2041', 'Struktur Data', 3, 'A', 4.00, 3),
('220211060323', 'TIK2051', 'Rekayasa Perangkat Lunak', 3, 'A', 4.00, 3),
('220211060323', 'TIK2061', 'Teknologi Basis Data', 3, 'A', 4.00, 3);

-- SEMESTER 4
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK2012', 'Interaksi Manusia Dan Komputer', 4, 'A', 4.00, 4),
('220211060323', 'TIK2022', 'Kecerdasan Buatan', 2, 'A', 4.00, 4),
('220211060323', 'TIK2032', 'Pemrograman Web', 3, 'D', 1.00, 4),
('220211060323', 'TIK2042', 'Pemodelan Dan Simulasi Komputer', 3, 'D', 1.00, 4),
('220211060323', 'TIK2052', 'Pengolahan Citra Digital', 3, 'B+', 3.50, 4),
('220211060323', 'TIK2062', 'Jaringan Dan Komunikasi Data', 3, 'A', 4.00, 4),
('220211060323', 'TIK2072', 'Praktikum Jaringan Dan Komunikasi Data', 1, 'E', 0.00, 4);

-- SEMESTER 5
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK3011', 'Pembelajaran Mesin', 3, 'D', 1.00, 5),
('220211060323', 'TIK3021', 'Pengembangan Game', 3, 'E', 0.00, 5),
('220211060323', 'TIK3031', 'Realitas Tertambah Dan Realitas Maya', 3, 'E', 0.00, 5),
('220211060323', 'TIK3041', 'Komputasi Awan', 2, 'B', 3.00, 5),
('220211060323', 'TIK3051', 'Sistem Multimedia', 3, 'B+', 3.50, 5),
('220211060323', 'TIK3061', 'Praktikum Sistem Multimedia', 1, 'B+', 3.50, 5);

-- SEMESTER 6
INSERT INTO transkrip (nim, kode_mk, nama_mk, sks, nilai_huruf, bobot, semester) VALUES
('220211060323', 'TIK3022', 'Riset Informatika', 3, 'C', 2.00, 6),
('220211060323', 'TIK3032', 'Kewirausahaan', 2, 'A', 4.00, 6),
('220211060323', 'TIK3042', 'Topik Khusus Teknik Informatika', 2, 'B+', 3.50, 6),
('220211060323', 'TIK3052', 'Kecakapan Antar Personal', 2, 'A', 4.00, 6),
('220211060323', 'TIK3062', 'Keamanan Siber', 3, 'A', 4.00, 6),
('220211060323', 'TIK3072', 'Praktikum Keamanan Siber', 1, 'B+', 3.50, 6),
('220211060323', 'TIK3132', 'Data Mining', 2, 'A', 4.00, 6),
('220211060323', 'TIK3182', 'Pengembangan Aplikasi Desktop', 2, 'A', 4.00, 6),
('220211060323', 'TIK3192', 'Pengembangan Aplikasi Web Berbasis Framework', 2, 'C', 2.00, 6);

-- ============================================
-- TABEL MATA KULIAH (Kurikulum Teknik Informatika)
-- ============================================
CREATE TABLE mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) NOT NULL UNIQUE,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL DEFAULT 3,
    semester INT DEFAULT 1,
    jenis ENUM('wajib', 'pilihan') DEFAULT 'wajib',
    kategori VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEMESTER 1 (21 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK1011', 'Pendidikan Agama', 2, 1, 'wajib'),
('TIK1021', 'Pancasila', 2, 1, 'wajib'),
('TIK1031', 'Bahasa Indonesia', 2, 1, 'wajib'),
('TIK1041', 'Kewarganegaraan', 2, 1, 'wajib'),
('TIK1051', 'Matematika Diskrit', 3, 1, 'wajib'),
('TIK1061', 'Kalkulus', 3, 1, 'wajib'),
('TIK1071', 'Probabilitas dan Statistika', 2, 1, 'wajib'),
('TIK1081', 'Algoritma dan Pemrograman Komputer', 4, 1, 'wajib'),
('TIK1091', 'Prakt. Algoritma & Pemrograman Komputer', 1, 1, 'wajib');

-- SEMESTER 2 (20 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK1012', 'Bahasa Inggris', 2, 2, 'wajib'),
('TIK1022', 'Pengetahuan Kepasifikan', 2, 2, 'wajib'),
('TIK1032', 'Pengantar Teknik Informatika', 2, 2, 'wajib'),
('TIK1042', 'Aljabar Linier', 2, 2, 'wajib'),
('TIK1052', 'Metode Numerik', 3, 2, 'wajib'),
('TIK1062', 'Teori Bahasa dan Automata', 3, 2, 'wajib'),
('TIK1072', 'Komputer dan Masyarakat', 2, 2, 'wajib'),
('TIK1082', 'Basis Data', 3, 2, 'wajib'),
('TIK1092', 'Praktikum Basis Data', 1, 2, 'wajib');

-- SEMESTER 3 (19 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK2011', 'Sistem Informasi', 3, 3, 'wajib'),
('TIK2021', 'Arsitektur dan Organisasi Komputer', 3, 3, 'wajib'),
('TIK2031', 'Sistem Operasi', 3, 3, 'wajib'),
('TIK2041', 'Struktur Data', 3, 3, 'wajib'),
('TIK2051', 'Rekayasa Perangkat Lunak', 3, 3, 'wajib'),
('TIK2061', 'Teknologi Basis Data', 3, 3, 'wajib'),
('TIK2071', 'Praktikum Teknologi Basis Data', 1, 3, 'wajib');

-- SEMESTER 4 (19 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK2012', 'Interaksi Manusia dan Komputer', 4, 4, 'wajib'),
('TIK2022', 'Kecerdasan Buatan', 2, 4, 'wajib'),
('TIK2032', 'Pemrograman Web', 3, 4, 'wajib'),
('TIK2042', 'Pemodelan dan Simulasi Komputer', 3, 4, 'wajib'),
('TIK2052', 'Pengolahan Citra Digital', 3, 4, 'wajib'),
('TIK2062', 'Jaringan dan Komunikasi Data', 3, 4, 'wajib'),
('TIK2072', 'Praktikum Jaringan dan Komunikasi Data', 1, 4, 'wajib');

-- SEMESTER 5 (19 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK3011', 'Pembelajaran Mesin', 3, 5, 'wajib'),
('TIK3021', 'Pengembangan Game', 3, 5, 'wajib'),
('TIK3031', 'Realitas Tertambah dan Realitas Maya', 3, 5, 'wajib'),
('TIK3041', 'Komputasi Awan', 2, 5, 'wajib'),
('TIK3051', 'Sistem Multimedia', 3, 5, 'wajib'),
('TIK3061', 'Praktikum Sistem Multimedia', 1, 5, 'wajib');

-- SEMESTER 6 (20 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK3012', 'Bioinformatika', 3, 6, 'wajib'),
('TIK3022', 'Riset Informatika', 3, 6, 'wajib'),
('TIK3032', 'Kewirausahaan', 2, 6, 'wajib'),
('TIK3042', 'Topik Khusus Teknik Informatika', 2, 6, 'wajib'),
('TIK3052', 'Kecakapan Antar Personal', 2, 6, 'wajib'),
('TIK3062', 'Keamanan Siber', 3, 6, 'wajib'),
('TIK3072', 'Praktikum Keamanan Siber', 1, 6, 'wajib');

-- SEMESTER 7 (16 SKS)
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK4010', 'KKT', 4, 7, 'wajib'),
('TIK4020', 'Magang', 3, 7, 'wajib'),
('TIK4030', 'Seminar dan Praktek Profesional', 3, 7, 'wajib'),
('TIK4041', 'Grafika Komputer', 2, 7, 'wajib'),
('TIK4051', 'Etika Profesi', 2, 7, 'wajib'),
('TIK4061', 'Kriptografi', 2, 7, 'wajib');

-- SEMESTER 8
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis) VALUES
('TIK4040', 'Skripsi', 6, 8, 'wajib');

-- ============================================
-- MATA KULIAH PILIHAN - ARTIFICIAL INTELLIGENCE
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3071', 'Sistem Pendukung Pengambilan Keputusan', 2, 5, 'pilihan', 'Artificial Intelligence'),
('TIK3081', 'Sistem Pakar', 2, 5, 'pilihan', 'Artificial Intelligence'),
('TIK3082', 'Visi Komputer', 2, 6, 'pilihan', 'Artificial Intelligence'),
('TIK3092', 'Jaringan Saraf Tiruan', 2, 6, 'pilihan', 'Artificial Intelligence'),
('TIK4042', 'Algoritma Genetik', 2, 7, 'pilihan', 'Artificial Intelligence'),
('TIK4052', 'Robotika Cerdas', 2, 7, 'pilihan', 'Artificial Intelligence');

-- ============================================
-- MATA KULIAH PILIHAN - HUMAN INFORMATICS
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3091', 'Desain Eksperimen', 2, 5, 'pilihan', 'Human Informatics'),
('TIK3101', 'Informatika Biomedis', 2, 5, 'pilihan', 'Human Informatics'),
('TIK3102', 'Teknologi Pembelajaran Daring', 2, 6, 'pilihan', 'Human Informatics'),
('TIK3112', 'Sistem Informasi Kesehatan', 2, 6, 'pilihan', 'Human Informatics'),
('TIK4062', 'E-Sport', 2, 7, 'pilihan', 'Human Informatics'),
('TIK4071', 'User Experience', 2, 7, 'pilihan', 'Human Informatics');

-- ============================================
-- MATA KULIAH PILIHAN - BIG DATA
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3111', 'Manajemen Sistem Basis Data', 2, 5, 'pilihan', 'Big Data'),
('TIK3121', 'Representasi Pengetahuan', 2, 5, 'pilihan', 'Big Data'),
('TIK3122', 'Big Data', 2, 6, 'pilihan', 'Big Data'),
('TIK3132', 'Data Mining', 2, 6, 'pilihan', 'Big Data'),
('TIK4081', 'Information Retrieval', 2, 7, 'pilihan', 'Big Data'),
('TIK4091', 'Semantic Web', 2, 7, 'pilihan', 'Big Data');

-- ============================================
-- MATA KULIAH PILIHAN - INFORMATION SYSTEM
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3131', 'Teori Informasi', 2, 5, 'pilihan', 'Information System'),
('TIK3141', 'Manajemen Sistem Informasi Korporat', 2, 5, 'pilihan', 'Information System'),
('TIK3142', 'E-Bisnis', 2, 6, 'pilihan', 'Information System'),
('TIK3152', 'Sistem Informasi Geografis', 2, 6, 'pilihan', 'Information System'),
('TIK4101', 'E-Government', 2, 7, 'pilihan', 'Information System'),
('TIK4111', 'Audit Sistem Informasi', 2, 7, 'pilihan', 'Information System');

-- ============================================
-- MATA KULIAH PILIHAN - DISTRIBUTED SYSTEM
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3151', 'Teknik Perutean Jaringan', 2, 5, 'pilihan', 'Distributed System'),
('TIK3161', 'Teknik Administrasi Server', 2, 5, 'pilihan', 'Distributed System'),
('TIK3162', 'Pemrograman Jaringan', 2, 6, 'pilihan', 'Distributed System'),
('TIK3172', 'Komunikasi Data Nirkabel', 2, 6, 'pilihan', 'Distributed System'),
('TIK4121', 'Sistem Komunikasi Optik', 2, 7, 'pilihan', 'Distributed System'),
('TIK4131', 'Teknik Simulasi Jaringan', 2, 7, 'pilihan', 'Distributed System');

-- ============================================
-- MATA KULIAH PILIHAN - SOFTWARE DEVELOPMENT
-- ============================================
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, kategori) VALUES
('TIK3171', 'Pengembangan Aplikasi Mobile', 2, 5, 'pilihan', 'Software Development'),
('TIK3181', 'Aplikasi Berorientasi Service', 2, 5, 'pilihan', 'Software Development'),
('TIK3182', 'Pengembangan Aplikasi Desktop', 2, 6, 'pilihan', 'Software Development'),
('TIK3192', 'Pengembangan Aplikasi Web Berbasis Framework', 2, 6, 'pilihan', 'Software Development'),
('TIK4141', 'Manajemen Proyek Perangkat Lunak', 2, 7, 'pilihan', 'Software Development'),
('TIK4151', 'Kualitas Perangkat Lunak', 2, 7, 'pilihan', 'Software Development');

-- ============================================
-- TABEL PERENCANAAN STUDI
-- ============================================
CREATE TABLE perencanaan_studi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    kode_mk VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_perencanaan (nim, kode_mk)
);

-- ============================================
-- TABEL KRS (Kartu Rencana Studi)
-- ============================================
CREATE TABLE krs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    kode_mk VARCHAR(20) NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL DEFAULT 3,
    jenis VARCHAR(20) DEFAULT 'Wajib',
    kelas VARCHAR(5) DEFAULT 'A',
    dosen1 VARCHAR(150),
    dosen2 VARCHAR(150),
    hari VARCHAR(20) NOT NULL,
    jam_mulai VARCHAR(10) NOT NULL,
    jam_selesai VARCHAR(10) NOT NULL,
    semester_krs VARCHAR(20) DEFAULT '20251',
    status_krs VARCHAR(20) DEFAULT 'Disetujui',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert KRS data for semester 7 (Gasal 2025/2026)
INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, dosen2, hari, jam_mulai, jam_selesai) VALUES
('220211060323', 'TIK1051', 'MATEMATIKA DISKRIT', 3, 'Wajib', 'A', 'Dr.Eng. SARY DIANE EKAWATI PATURUSI ST, M.Eng', 'PUJO HARI SAPUTRO S.Kom., M.T', 'Jumat', '08:00', '10:30'),
('220211060323', 'TIK1071', 'PROBABILITAS DAN STATISTIKA', 2, 'Wajib', 'A', 'KENNETH YOSUA R PALILINGAN ST, MT', NULL, 'Jumat', '10:30', '12:10'),
('220211060323', 'TIK2071', 'PRAKTIKUM TEKNOLOGI BASIS DATA', 1, 'Wajib', 'A', 'DIRKO GUSTAAFIANO SETYADHARMAPUTRA RUINDUNGAN ST, M.Eng', NULL, 'Jumat', '13:00', '15:30'),
('220211060323', 'TIK3011', 'PEMBELAJARAN MESIN', 3, 'Wajib', 'C', 'OKTAVIAN ABRAHAM LANTANG ST, MTI, Ph.D', NULL, 'Senin', '08:00', '10:30'),
('220211060323', 'TIK3021', 'PENGEMBANGAN GAME', 3, 'Wajib', 'C', 'Ir. SUMENGE TANGKAWAROUW GODION KAUNANG MT, Ph.D', NULL, 'Selasa', '08:00', '10:30'),
('220211060323', 'TIK3031', 'REALITAS TERTAMBAH DAN REALITAS MAYA', 3, 'Wajib', 'C', 'BRAVE ANGKASA SUGIARSO ST', 'WAHYUNI FITHRATUL ZALMI S.Kom., M.Kom', 'Kamis', '08:00', '10:30'),
('220211060323', 'TIK4030', 'SEMINAR DAN PRAKTEK PROFESIONAL', 3, 'Wajib', 'B', 'ALWIN MELKIE SAMBUL ST, M.Eng, Ph.D.', 'VICTOR TARIGAN M.Kom', 'Selasa', '13:00', '15:30'),
('220211060323', 'TIK4041', 'GRAFIKA KOMPUTER', 2, 'Wajib', 'B', 'RIZAL SENGKEY ST, MT', NULL, 'Rabu', '14:40', '16:20'),
('220211060323', 'TIK4051', 'ETIKA PROFESI', 2, 'Wajib', 'B', 'DRINGHUZEN JEKKE MAMAHIT ST, MT', NULL, 'Selasa', '15:30', '17:10'),
('220211060323', 'TIK4061', 'KRIPTOGRAFI', 2, 'Wajib', 'B', 'RENDY SYAHPUTRA S.Kom., M.Kom', NULL, 'Rabu', '13:00', '14:40');
