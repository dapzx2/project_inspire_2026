<?php include 'layout/header.php'; ?>

<?php
include 'config/database.php';

$nim = isset($_SESSION['nim']) ? mysqli_real_escape_string($conn, $_SESSION['nim']) : '';

// Fetch user data
$user_data = [];
$query_user = "SELECT * FROM users WHERE nim = '$nim'";
$result_user = @mysqli_query($conn, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user_data = mysqli_fetch_assoc($result_user);
}

// Fetch KRS data from krs table
$krs_list = [];
$total_sks = 0;

$query_krs = "SELECT * FROM krs WHERE nim = '$nim' ORDER BY hari ASC, jam_mulai ASC";
$result_krs = @mysqli_query($conn, $query_krs);
if ($result_krs) {
    while ($row = mysqli_fetch_assoc($result_krs)) {
        $krs_list[] = $row;
        $total_sks += $row['sks'];
    }
}

// Get IP Semester sebelumnya (semester 6)
$ip_sebelum = 0;
$query_ip = "SELECT 
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
    ) / SUM(sks) as ip
    FROM transkrip 
    WHERE nim = '$nim' AND semester = 6";
$result_ip = @mysqli_query($conn, $query_ip);
if ($result_ip && $row = mysqli_fetch_assoc($result_ip)) {
    $ip_sebelum = $row['ip'] ? number_format($row['ip'], 2) : 3.39;
}

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
                        <h5>Semester Gasal 2025/2026</h5>
                        <label>Dosen Pembimbing Akademik</label>
                        <p>
                            MEICSY ELDAD ISRAEL NAJOAN ST, MT<br>
                            NIP. 196705271995121001
                        </p>
                        <label>IP Semester Sebelum : </label> <?php echo $ip_sebelum; ?> <br>
                        <label>Jumlah SKS Maksimal : </label> <?php echo $max_sks; ?> <br>
                        <label>Jumlah SKS Dikontrak : </label> <?php echo $total_sks; ?>
                    </div>
                </div>
            </div>

            <!-- KRS Status -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success">
                        <span><i class="icon fas fa-check-circle"></i> KRS sudah disetujui oleh Dosen Pembimbing Akademik</span>
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
                                                <?php echo htmlspecialchars($mk['kode_mk']); ?> • 
                                                <?php echo $mk['sks']; ?> SKS • 
                                                <?php echo $mk['jenis']; ?> •
                                                <br>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h2 class="lead"><b><?php echo htmlspecialchars($mk['nama_mk']); ?></b></h2>
                                                        <p class="text-muted text-sm"><b>KELAS:</b> <?php echo $mk['kelas']; ?></p>
                                                        <ul class="ml-4 fa-ul text-muted">
                                                            <?php if (!empty($mk['dosen1'])): ?>
                                                            <li class="small">
                                                                <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div>
                                                                <?php echo htmlspecialchars($mk['dosen1']); ?>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if (!empty($mk['dosen2'])): ?>
                                                            <li class="small">
                                                                <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div>
                                                                <?php echo htmlspecialchars($mk['dosen2']); ?>
                                                            </li>
                                                            <?php endif; ?>
                                                            <br>
                                                            <li class="small">
                                                                <span class="fa-li"><i class="fas fa-lg fa-clock"></i></span>
                                                                <?php echo $mk['hari']; ?>, <?php echo $mk['jam_mulai']; ?> - <?php echo $mk['jam_selesai']; ?>
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
                                            Belum ada matakuliah yang dikontrak. Pastikan tabel <code>krs</code> sudah ada di database.
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
