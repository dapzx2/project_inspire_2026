<?php include 'layout/header.php'; ?>

<?php
include 'config/database.php';

$nim = $_SESSION['nim'] ?? '';

// ambil data user
$user_data = [];
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

$stmt = $conn->prepare("SELECT * FROM krs WHERE nim = ? AND semester_krs = '20251' ORDER BY hari ASC, jam_mulai ASC");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $krs_list[] = $row;
    $total_sks += (int) $row['sks'];
}
$stmt->close();

// hitung IP semester sebelumnya (dinamis berdasarkan semester user)
$semester_sekarang = (int) ($user_data['semester'] ?? 7);
$semester_sebelum = max(1, $semester_sekarang - 1);
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

// tentukan maks SKS berdasarkan IP
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

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid">
            <h1>KRS</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Alert Info -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <span><i class="icon fas fa-exclamation-triangle"></i> Tidak dalam periode pengisian KRS</span>
                    </div>
                </div>
            </div>

            <!-- Info Semester -->
            <div class="row">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h5>Semester <?php echo htmlspecialchars($user_data['periode'] ?? 'Gasal', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($user_data['tahun_akademik'] ?? '2025/2026', ENT_QUOTES, 'UTF-8'); ?></h5>
                        <label>Dosen Pembimbing Akademik</label>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($user_data['pembimbing_akademik'] ?? '-', ENT_QUOTES, 'UTF-8'); ?><br>
                            NIP. <?php echo htmlspecialchars($user_data['nip_pembimbing'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <br>
                        <label>IP Semester Sebelum : </label> <?php echo $ip_sebelum; ?> <br>
                        <label>Jumlah SKS Maksimal : </label> <?php echo $max_sks; ?> <br>
                        <label>Jumlah SKS Dikontrak : </label> <?php echo $total_sks; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success">
                        <span><i class="icon fas fa-exclamation-triangle"></i> KRS sudah disetujui oleh Dosen Pembimbing Akademik</span><br>
                    </div>
                </div>
            </div>

            <!-- Matakuliah Dikontrak -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-solid">
                        <div class="card-header">
                            <h3 class="card-title">Matakuliah Dikontrak</h3>
                            <div class="card-tools">
                                <a href="krs_cetak.php" target="_blank">
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i> CETAK</button>
                                </a>
                            </div>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row d-flex align-items-stretch">
                                <?php if (count($krs_list) > 0): ?>
                                    <?php foreach ($krs_list as $mk): ?>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="card card-success card-outline">
                                            <div class="card-header text-muted border-bottom-0">
                                                <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> • 
                                                <?php echo (int) $mk['sks']; ?> SKS • 
                                                <?php echo htmlspecialchars($mk['jenis'] ?? '', ENT_QUOTES, 'UTF-8'); ?> •
                                                <br>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h2 class="lead"><b><?php echo htmlspecialchars($mk['nama_mk'], ENT_QUOTES, 'UTF-8'); ?></b></h2>
                                                        <p class="text-muted text-sm"><b>KELAS:</b> <?php echo htmlspecialchars($mk['kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <ul class="ml-4 fa-ul text-muted">
                                                            <?php if (!empty($mk['dosen1'])): ?>
                                                            <li class="small">
                                                                <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div>
                                                                <?php echo htmlspecialchars($mk['dosen1'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if (!empty($mk['dosen2'])): ?>
                                                            <li class="small">
                                                                <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div>
                                                                <?php echo htmlspecialchars($mk['dosen2'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </li>
                                                            <?php endif; ?>
                                                            <br>
                                                            <li class="small">
                                                                <span class="fa-li"><i class="fas fa-lg fa-clock"></i></span>
                                                                <?php echo htmlspecialchars($mk['hari'] ?? '', ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($mk['jam_mulai'] ?? '', ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($mk['jam_selesai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Belum ada matakuliah yang dikontrak.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
