<?php
session_start();
include 'config/database.php';

// cek login
if (!isset($_SESSION['nim'])) {
    header('Location: index.php');
    exit;
}

$nim = $_SESSION['nim'];
$selected_semester = $_GET['semester'] ?? '';

if ($selected_semester === '') {
    header('Location: khs.php');
    exit;
}

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

// ambil data KHS dari tabel KRS
$khs_data = [];
$total_sks = 0;
$total_nilai_sks = 0;

$stmt = $conn->prepare("SELECT * FROM krs WHERE nim = ? AND semester_krs = ? ORDER BY id ASC");
$stmt->bind_param("ss", $nim, $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $khs_data[] = $row;
    $total_sks += (int) $row['sks'];
    $total_nilai_sks += (int) $row['sks'] * (float) ($row['bobot'] ?? 0);
}
$stmt->close();

// Calculate IPS
$ips = ($total_sks > 0) ? round($total_nilai_sks / $total_sks, 2) : 0;

// Get IPK from user data
$ipk = isset($user_data['ipk']) ? $user_data['ipk'] : 0;

// Format semester
function formatSemester($code) {
    if (strlen($code) < 5) return $code;
    $year = substr($code, 0, 4);
    $period = substr($code, 4, 1);
    $nextYear = intval($year) + 1;
    $periodName = ($period == '1') ? 'Gasal' : 'Genap';
    return "$year / $nextYear $periodName";
}

// Format tanggal dalam Bahasa Indonesia
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tanggal_cetak = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');

// Extract data
$prodi = isset($user_data['prodi']) ? $user_data['prodi'] : 'Teknik Informatika';
$jenjang = isset($user_data['jenjang']) ? $user_data['jenjang'] : 'S1';
$angkatan = isset($user_data['angkatan']) ? $user_data['angkatan'] : '2022';
$pembimbing = isset($user_data['pembimbing_akademik']) ? $user_data['pembimbing_akademik'] : '-';

// Calculate max SKS for next semester based on IPS
$max_sks = 24;
if ($ips >= 3.00) $max_sks = 24;
elseif ($ips >= 2.50) $max_sks = 22;
elseif ($ips >= 2.00) $max_sks = 20;
else $max_sks = 18;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS <?php echo $nim; ?> - <?php echo formatSemester($selected_semester); ?></title>
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
            font-size: 9pt;
            line-height: 1.3;
            background: #fff;
            color: #000;
        }
        .container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 15px;
            background: white;
        }
        
        /* Header */
        .header {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #2c1654 0%, #4a2c7a 100%);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
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
            margin-bottom: 15px;
        }
        .title-section h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }
        .title-section p {
            font-size: 10pt;
            color: #666;
            margin-top: 3px;
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
            width: 170px;
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
            width: 90px;
            text-align: right;
        }
        .photo-box {
            width: 75px;
            height: 95px;
            border: 1px solid #ccc;
            overflow: hidden;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #999;
            background: #f5f5f5;
        }
        
        /* KHS Table */
        .khs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 15px;
        }
        .khs-table th {
            background: #e8e8e8;
            border: 1px solid #ccc;
            padding: 5px 4px;
            font-weight: bold;
            text-align: center;
        }
        .khs-table td {
            border: 1px solid #ccc;
            padding: 4px 5px;
        }
        .khs-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        .khs-table .center {
            text-align: center;
        }
        .khs-table .right {
            text-align: right;
        }
        .khs-table tfoot td {
            font-weight: bold;
            background: #f0f0f0;
        }
        
        /* Summary Box */
        .summary-box {
            background: #f5f5f5;
            border: 1px solid #ccc;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-box table {
            font-size: 9pt;
        }
        .summary-box table td {
            padding: 3px 0;
        }
        .summary-box table td:first-child {
            width: 250px;
        }
        .summary-box table td:last-child {
            font-weight: bold;
        }
        
        /* Footer Signature */
        .footer-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            font-size: 9pt;
        }
        .signature {
            text-align: center;
            width: 250px;
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
            margin-top: 15px;
            font-size: 8pt;
            color: #999;
            text-align: center;
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
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak KHS</button>

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
            <h1>KARTU HASIL STUDI</h1>
            <p>Semester: <?php echo formatSemester($selected_semester); ?></p>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <table class="info-table">
                    <tr>
                        <td>Nama Mahasiswa</td>
                        <td>:</td>
                        <td><?php echo $user_data ? htmlspecialchars($user_data['nama']) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Induk Mahasiswa</td>
                        <td>:</td>
                        <td><?php echo $nim; ?></td>
                    </tr>
                    <tr>
                        <td>Angkatan</td>
                        <td>:</td>
                        <td><?php echo $angkatan; ?></td>
                    </tr>
                    <tr>
                        <td>Program Studi</td>
                        <td>:</td>
                        <td><?php echo $jenjang; ?> - <?php echo strtoupper($prodi); ?></td>
                    </tr>
                    <tr>
                        <td>Pembimbing Akademik</td>
                        <td>:</td>
                        <td><?php echo htmlspecialchars($pembimbing); ?></td>
                    </tr>
                </table>
            </div>
            <div class="info-right">
                <div class="photo-box">FOTO</div>
            </div>
        </div>

        <!-- KHS Table -->
        <table class="khs-table">
            <thead>
                <tr>
                    <th rowspan="2" width="4%">No.</th>
                    <th colspan="2">Matakuliah</th>
                    <th rowspan="2" width="6%">SKS</th>
                    <th colspan="2">Nilai</th>
                    <th rowspan="2" width="10%">Nilai SKS</th>
                </tr>
                <tr>
                    <th width="10%">Kode</th>
                    <th>Nama</th>
                    <th width="8%">Huruf</th>
                    <th width="8%">Angka</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($khs_data as $khs): 
                    $nilai = isset($khs['nilai_huruf']) ? $khs['nilai_huruf'] : '-';
                    $bobot = isset($khs['bobot']) ? $khs['bobot'] : 0;
                ?>
                <tr>
                    <td class="center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($khs['kode_mk']); ?></td>
                    <td><?php echo htmlspecialchars($khs['nama_mk']); ?></td>
                    <td class="center"><?php echo $khs['sks']; ?></td>
                    <td class="center"><?php echo $nilai; ?></td>
                    <td class="center"><?php echo number_format($bobot, 2); ?></td>
                    <td class="center"><?php echo $khs['sks'] * $bobot; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right">Total SKS</td>
                    <td class="center"><?php echo $total_sks; ?></td>
                    <td colspan="2" class="right">Jumlah</td>
                    <td class="center"><?php echo $total_nilai_sks; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary Box -->
        <div class="summary-box">
            <table>
                <tr>
                    <td>IP Semester (IPS)</td>
                    <td>: <?php echo number_format($ips, 2); ?></td>
                </tr>
                <tr>
                    <td>IP Kumulatif (IPK)</td>
                    <td>: <?php echo number_format($ipk, 2); ?></td>
                </tr>
                <tr>
                    <td>Maks. Beban SKS semester berikutnya</td>
                    <td>: <?php echo $max_sks; ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer Signature -->
        <div class="footer-section">
            <div class="signature">
                <div class="title">Manado, <?php echo $tanggal_cetak; ?><br>Mengetahui,<br>Wakil Dekan 1</div>
                <div class="name">Dr. Judy O. Waani, ST, MT</div>
                <div class="nip">196410101995121001</div>
            </div>
        </div>

        <div class="footer-info">cetak@inspire.unsrat.ac.id</div>
    </div>
</body>
</html>
