<?php
session_start();
include 'config/database.php';

// cek login
if (!isset($_SESSION['nim'])) {
    header('Location: index.php');
    exit;
}

$nim = $_SESSION['nim'];

// ambil data user dengan prepared statement
$user_data = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

// ambil data KRS
$krs_list = [];
$total_sks = 0;

$stmt = $conn->prepare("SELECT * FROM krs WHERE nim = ? AND semester_krs = '20251' ORDER BY nama_mk ASC");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $krs_list[] = $row;
    $total_sks += (int) $row['sks'];
}
$stmt->close();

// hitung IP semester sebelumnya (dinamis)
$semester_mahasiswa = (int) ($user_data['semester'] ?? 8);
$semester_sebelum = max(1, $semester_mahasiswa - 1);
$ip_sebelum = 0;
$stmt = $conn->prepare("SELECT 
    SUM(
        CASE 
            WHEN nilai_huruf = 'A' THEN 4 * sks
            WHEN nilai_huruf = 'B+' THEN 3.5 * sks
            WHEN nilai_huruf = 'B' THEN 3 * sks
            WHEN nilai_huruf = 'C+' THEN 2.5 * sks
            WHEN nilai_huruf = 'C' THEN 2 * sks
            WHEN nilai_huruf = 'D' THEN 1 * sks
            ELSE 0
        END
    ) / NULLIF(SUM(sks), 0) as ip
    FROM transkrip 
    WHERE nim = ? AND semester = ?");
$stmt->bind_param("si", $nim, $semester_sebelum);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $ip_sebelum = $row['ip'] ? number_format($row['ip'], 2) : 3.39;
}
$stmt->close();

// Format tanggal dalam Bahasa Indonesia
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tanggal_cetak = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');

// Extract angkatan from NIM (format: 22XXXXXXXXXX -> 2022)
$angkatan = '20' . substr($nim, 0, 2);

// Get dynamic data with fallbacks
$prodi = $user_data['prodi'] ?? 'TEKNIK INFORMATIKA';
$jenjang = $user_data['jenjang'] ?? 'S1';
$dosen_pa = $user_data['pembimbing_akademik'] ?? '-';
$tahun_akademik = $user_data['tahun_akademik'] ?? '2025/2026';
$periode = $user_data['periode'] ?? 'Gasal';

// Determine max SKS based on IP
$max_sks = 24;
if ($ip_sebelum >= 3.00) {
    $max_sks = 24;
} elseif ($ip_sebelum >= 2.50) {
    $max_sks = 22;
} elseif ($ip_sebelum >= 2.00) {
    $max_sks = 20;
} else {
    $max_sks = 18;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak KRS <?php echo htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" href="https://inspire.unsrat.ac.id/resources/img/logo-unsrat.png">
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            background: #fff;
            color: #000;
        }
        .container {
            max-width: 29.7cm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        /* Header */
        .header {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #2c1654 0%, #4a2c7a 100%);
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .header-logo {
            width: 70px;
            margin-right: 15px;
        }
        .header-logo img {
            width: 60px;
            height: auto;
        }
        .header-text {
            color: #fff;
        }
        .header-text h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        .header-text h3 {
            font-size: 12pt;
            font-weight: normal;
            margin: 0;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-section h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }
        .title-section .semester {
            color: #e74c3c;
            font-size: 10pt;
        }
        
        /* Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .info-left {
            flex: 1;
        }
        .info-table {
            font-size: 9pt;
        }
        .info-table tr td {
            padding: 2px 0;
            vertical-align: top;
        }
        .info-table tr td:first-child {
            width: 150px;
            color: #666;
        }
        .info-table tr td:nth-child(2) {
            width: 10px;
            text-align: center;
        }
        .info-table tr td:last-child {
            color: #000;
            font-weight: 500;
        }
        .info-right {
            width: 100px;
            text-align: right;
        }
        .photo-box {
            width: 90px;
            height: 120px;
            border: 1px solid #ccc;
            overflow: hidden;
            margin-left: auto;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* KRS Table */
        .krs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 20px;
        }
        .krs-table thead th {
            background: linear-gradient(135deg, #2c1654 0%, #4a2c7a 100%);
            color: #fff;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #2c1654;
        }
        .krs-table tbody td {
            border: 1px solid #ccc;
            padding: 5px 4px;
            vertical-align: top;
        }
        .krs-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .krs-table .no {
            width: 25px;
            text-align: center;
        }
        .krs-table .kode {
            width: 65px;
        }
        .krs-table .matakuliah {
            width: 180px;
        }
        .krs-table .sks {
            width: 30px;
            text-align: center;
        }
        .krs-table .kelas {
            width: 35px;
            text-align: center;
        }
        .krs-table .dosen {
            /* auto width */
        }
        .krs-table .hari {
            width: 50px;
            text-align: center;
        }
        .krs-table .waktu {
            width: 75px;
            text-align: center;
        }
        .krs-table tfoot td {
            border: 1px solid #ccc;
            padding: 5px 4px;
            font-weight: bold;
            background: #f0f0f0;
        }
        
        /* Footer Signature */
        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 9pt;
        }
        .signature {
            text-align: center;
            width: 30%;
        }
        .signature .title {
            margin-bottom: 50px;
        }
        .signature .name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 2px;
        }
        .signature .nip {
            font-size: 8pt;
            color: #666;
        }
        
        /* Footer Info */
        .footer-info {
            margin-top: 20px;
            font-size: 8pt;
            color: #666;
        }
        .footer-info a {
            color: #2980b9;
        }
        
        /* Print */
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .container { padding: 0; }
            .header { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .krs-table thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 16px;
            background: #2c1654;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
        }
        .print-btn:hover {
            background: #4a2c7a;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak KRS</button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="https://inspire.unsrat.ac.id/resources/img/logo-unsrat.png" alt="Logo UNSRAT">
            </div>
            <div class="header-text">
                <h2>UNIVERSITAS SAM RATULANGI</h2>
                <h3>FAKULTAS TEKNIK</h3>
            </div>
        </div>

        <!-- Title -->
        <div class="title-section">
            <h1>KARTU RENCANA STUDI</h1>
            <div class="semester">Semester: <span style="color: #e74c3c;"><?php echo htmlspecialchars($periode . ' ' . $tahun_akademik); ?></span></div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <table class="info-table">
                    <tr>
                        <td>Nama Mahasiswa</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($user_data['nama']); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Induk Mahasiswa</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($user_data['nim']); ?></td>
                    </tr>
                    <tr>
                        <td>Angkatan</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($angkatan, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <td>Program Studi</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($jenjang . ' - ' . strtoupper($prodi)); ?></td>
                    </tr>
                    <tr>
                        <td>Pembimbing Akademik</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($dosen_pa); ?></td>
                    </tr>
                    <tr>
                        <td>IP Semester Lalu</td>
                        <td>:</td>
                        <td><?php echo $ip_sebelum; ?></td>
                    </tr>
                    <tr>
                        <td>Beban SKS</td>
                        <td>:</td>
                        <td><?php echo $max_sks; ?></td>
                    </tr>
                </table>
            </div>
            <div class="info-right">
                <div class="photo-box">
                    <img src="assets/images/user_default.png" alt="Foto Mahasiswa">
                </div>
            </div>
        </div>

        <!-- KRS Table -->
        <table class="krs-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="kode">Kode MK</th>
                    <th class="matakuliah">Matakuliah</th>
                    <th class="sks">SKS</th>
                    <th class="kelas">Kelas</th>
                    <th class="dosen">Nama Dosen</th>
                    <th class="hari">Hari</th>
                    <th class="waktu">Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($krs_list) > 0): ?>
                    <?php $no = 1; foreach ($krs_list as $mk): ?>
                    <tr>
                        <td class="no"><?php echo $no++; ?></td>
                        <td class="kode"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                        <td class="matakuliah"><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                        <td class="sks"><?php echo $mk['sks']; ?></td>
                        <td class="kelas"><?php echo $mk['kelas']; ?></td>
                        <td class="dosen">
                            <?php echo htmlspecialchars($mk['dosen1']); ?>
                            <?php if (!empty($mk['dosen2'])): ?>
                                <br><?php echo htmlspecialchars($mk['dosen2']); ?>
                            <?php endif; ?>
                        </td>
                        <td class="hari"><?php echo htmlspecialchars($mk['hari'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="waktu"><?php echo htmlspecialchars($mk['jam_mulai'] . '-' . $mk['jam_selesai'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">Belum ada matakuliah yang dikontrak</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">Jumlah Total</td>
                    <td class="sks"><?php echo $total_sks; ?></td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer Signature -->
        <div class="footer-section">
            <div class="signature">
                <div class="title">Mengetahui,<br>Wakil Dekan 1</div>
                <div class="name">Dr. Judy O. Waani ST, MT</div>
                <div class="nip">198410181905121001</div>
            </div>
            <div class="signature">
                <div class="title">Menyetujui,<br>Dosen PA</div>
                <div class="name"><?php echo htmlspecialchars($dosen_pa); ?></div>
                <div class="nip">196705271995121001</div>
            </div>
            <div class="signature">
                <div class="title">Manado, <?php echo $tanggal_cetak; ?><br>Mahasiswa</div>
                <div class="name"><?php echo htmlspecialchars($user_data['nama']); ?></div>
                <div class="nip"><?php echo htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="footer-info">
            <a href="mailto:cetak@inspire.unsrat.ac.id">cetak@inspire.unsrat.ac.id</a>
        </div>
    </div>
</body>
</html>
