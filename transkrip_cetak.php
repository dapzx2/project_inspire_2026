<?php
session_start();
include 'config/database.php';

// cek login
if (!isset($_SESSION['nim'])) {
    header('Location: index.php');
    exit;
}

$nim = $_SESSION['nim'];

// ambil data user
$user_data = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

// ambil data transkrip grouped by semester
$transkrip_data = [];
$total_sks = 0;
$sks_lulus = 0;

$stmt = $conn->prepare("SELECT * FROM transkrip WHERE nim = ? ORDER BY semester ASC, id ASC");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $semester = $row['semester'];
    if (!isset($transkrip_data[$semester])) {
        $transkrip_data[$semester] = [];
    }
    $transkrip_data[$semester][] = $row;
    $total_sks += (int) $row['sks'];
    // SKS lulus = tidak menghitung D dan E
    if (!in_array($row['nilai_huruf'], ['D', 'E'], true)) {
        $sks_lulus += (int) $row['sks'];
    }
}
$stmt->close();

// hitung IPK
$ipk = 0;
$stmt = $conn->prepare("SELECT CASE WHEN SUM(sks) > 0 THEN ROUND(SUM(sks * bobot) / SUM(sks), 2) ELSE 0 END as ipk FROM transkrip WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $ipk = (float) $row['ipk'];
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
$tempat_lahir = isset($user_data['tempat_lahir']) ? $user_data['tempat_lahir'] : 'Manado';
$tanggal_lahir_db = isset($user_data['tanggal_lahir']) ? $user_data['tanggal_lahir'] : '2004-10-02';
$prodi = isset($user_data['prodi']) ? $user_data['prodi'] : 'Teknik Informatika';
$jenjang = isset($user_data['jenjang']) ? $user_data['jenjang'] : 'S1';

// Format tanggal lahir
if ($tanggal_lahir_db) {
    $tgl_lahir_parts = explode('-', $tanggal_lahir_db);
    if (count($tgl_lahir_parts) == 3) {
        $tanggal_lahir_formatted = $tgl_lahir_parts[2] . ' ' . $bulan_indo[(int)$tgl_lahir_parts[1]] . ' ' . $tgl_lahir_parts[0];
    } else {
        $tanggal_lahir_formatted = $tanggal_lahir_db;
    }
} else {
    $tanggal_lahir_formatted = '';
}

// Tanggal masuk (1 Agustus tahun angkatan)
$tanggal_masuk = '01 Agustus ' . $angkatan;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip <?php echo htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" href="https://inspire.unsrat.ac.id/resources/img/logo-unsrat.png">
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.2;
            background: #fff;
            color: #000;
        }
        .container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        /* Header */
        .header {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #2c1654 0%, #4a2c7a 100%);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 60px;
            margin-right: 12px;
        }
        .header-logo img {
            width: 50px;
            height: auto;
        }
        .header-text {
            color: #fff;
        }
        .header-text h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        .header-text h3 {
            font-size: 10pt;
            font-weight: normal;
            margin: 0;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .title-section h1 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }
        
        /* Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-left {
            flex: 1;
        }
        .info-table {
            font-size: 8pt;
        }
        .info-table tr td {
            padding: 1px 0;
            vertical-align: top;
        }
        .info-table tr td:first-child {
            width: 140px;
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
            width: 80px;
            text-align: right;
        }
        .photo-box {
            width: 70px;
            height: 90px;
            border: 1px solid #ccc;
            overflow: hidden;
            margin-left: auto;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Two Column Layout */
        .main-content {
            display: flex;
            gap: 10px;
        }
        .left-column, .right-column {
            flex: 1;
        }
        
        /* Semester Header */
        .semester-header {
            background: linear-gradient(135deg, #2c1654 0%, #4a2c7a 100%);
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8pt;
            margin-top: 8px;
            border-radius: 3px 3px 0 0;
        }
        
        /* Transcript Table */
        .transcript-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 5px;
        }
        .transcript-table th {
            background: #e8e8e8;
            border: 1px solid #ccc;
            padding: 3px 2px;
            font-weight: bold;
            text-align: center;
        }
        .transcript-table td {
            border: 1px solid #ccc;
            padding: 2px 3px;
        }
        .transcript-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .transcript-table .no {
            width: 18px;
            text-align: center;
        }
        .transcript-table .kode {
            width: 50px;
        }
        .transcript-table .nama {
            /* auto width */
        }
        .transcript-table .sks {
            width: 22px;
            text-align: center;
        }
        .transcript-table .nilai {
            width: 25px;
            text-align: center;
        }
        
        /* Summary Box */
        .summary-box {
            background: #f5f5f5;
            border: 1px solid #ccc;
            padding: 8px 12px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .summary-box table {
            font-size: 9pt;
        }
        .summary-box table td {
            padding: 2px 0;
        }
        .summary-box table td:first-child {
            width: 180px;
            font-weight: bold;
        }
        
        /* Footer Signature */
        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 8pt;
        }
        .signature {
            text-align: center;
            width: 45%;
        }
        .signature .title {
            margin-bottom: 40px;
        }
        .signature .name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 2px;
        }
        .signature .nip {
            font-size: 7pt;
            color: #666;
        }
        
        /* Footer Info */
        .footer-info {
            margin-top: 10px;
            font-size: 7pt;
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
            .header, .semester-header { 
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
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak Transkrip</button>

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
            <h1>TRANSKRIP NILAI</h1>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <table class="info-table">
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($user_data['nama']); ?></td>
                    </tr>
                    <tr>
                        <td>Tempat / Tanggal Lahir</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($tempat_lahir . ', ' . $tanggal_lahir_formatted); ?></td>
                    </tr>
                    <tr>
                        <td>NIM / NRI</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($user_data['nim']); ?></td>
                    </tr>
                    <tr>
                        <td>Program Studi / Jenjang</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($prodi . ' / ' . $jenjang); ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal Masuk</td>
                        <td>:</td>
                        <td><?php echo $tanggal_masuk; ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>:</td>
                        <td><?php echo $tanggal_cetak; ?></td>
                    </tr>
                </table>
            </div>
            <div class="info-right">
                <div class="photo-box">
                    <img src="assets/images/user_default.png" alt="Foto">
                </div>
            </div>
        </div>

        <!-- Main Content - Two Columns -->
        <div class="main-content">
            <!-- Left Column (Semester Ganjil) -->
            <div class="left-column">
                <?php foreach ([1, 3, 5, 7] as $sem): if (isset($transkrip_data[$sem])): ?>
                <div class="semester-header">Semester <?php echo $sem; ?></div>
                <table class="transcript-table">
                    <thead>
                        <tr>
                            <th class="no">No</th>
                            <th class="kode">Kode</th>
                            <th class="nama">Mata Kuliah</th>
                            <th class="sks">SKS</th>
                            <th class="nilai">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($transkrip_data[$sem] as $mk): ?>
                        <tr>
                            <td class="no"><?php echo $no++; ?></td>
                            <td class="kode"><?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="nama"><?php echo htmlspecialchars($mk['nama_mk'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="sks"><?php echo $mk['sks']; ?></td>
                            <td class="nilai"><?php echo htmlspecialchars($mk['nilai_huruf'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; endforeach; ?>

                <!-- Summary -->
                <div class="summary-box">
                    <table>
                        <tr><td>Total SKS</td><td>: <?php echo $total_sks; ?></td></tr>
                        <tr><td>Total SKS Lulus</td><td>: <?php echo $sks_lulus; ?></td></tr>
                        <tr><td>Indeks Prestasi Kumulatif</td><td>: <?php echo number_format($ipk, 2); ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Right Column (Semester Genap) -->
            <div class="right-column">
                <?php foreach ([2, 4, 6, 8] as $sem): if (isset($transkrip_data[$sem])): ?>
                <div class="semester-header">Semester <?php echo $sem; ?></div>
                <table class="transcript-table">
                    <thead>
                        <tr>
                            <th class="no">No</th>
                            <th class="kode">Kode</th>
                            <th class="nama">Mata Kuliah</th>
                            <th class="sks">SKS</th>
                            <th class="nilai">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($transkrip_data[$sem] as $mk): ?>
                        <tr>
                            <td class="no"><?php echo $no++; ?></td>
                            <td class="kode"><?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="nama"><?php echo htmlspecialchars($mk['nama_mk'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="sks"><?php echo $mk['sks']; ?></td>
                            <td class="nilai"><?php echo htmlspecialchars($mk['nilai_huruf'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <!-- Footer Signature -->
        <div class="footer-section">
            <div class="signature">
                <!-- Empty left side -->
            </div>
            <div class="signature">
                <div class="title">Manado, <?php echo $tanggal_cetak; ?><br>Dekan,</div>
                <div class="name">Prof.Dr.Ir. Fabian Johanes Manoppo, M.Agr</div>
                <div class="nip">NIP. 196510301992031001</div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="footer-info">
            <a href="mailto:cetak@inspire.unsrat.ac.id">cetak@inspire.unsrat.ac.id</a>
        </div>
    </div>
</body>
</html>
