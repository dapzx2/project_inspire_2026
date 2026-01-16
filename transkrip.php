<?php include 'layout/header.php'; ?>

<?php
include 'config/database.php';

$user_data = null;
$transkrip_data = [];
$total_sks = 0;
$total_mk = 0;
$ips = 0;

$nim = $_SESSION['nim'] ?? '';

// ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

// ambil data transkrip grouped by semester
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
    $total_mk++;
}
$stmt->close();

// hitung IPK
$stmt = $conn->prepare("SELECT 
            CASE 
                WHEN SUM(sks) > 0 THEN ROUND(SUM(sks * bobot) / SUM(sks), 2)
                ELSE 0 
            END as ips
          FROM transkrip 
          WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $ips = (float) $row['ips'];
}
$stmt->close();
?>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>Transkrip</h1></div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="callout callout-info">
                        Transkrip Nilai berisi informasi nilai hasil studi mahasiswa mulai dari semester awal sampai dengan semester terakhir mahasiswa. Transkrip ini dapat dicetak dalam bentuk transkrip satu halaman.
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Transkrip</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>Nama</label><br>
                                    <?php echo $user_data ? htmlspecialchars($user_data['nama'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>NIM</label><br>
                                    <?php echo $user_data ? htmlspecialchars($user_data['nim'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>Program Studi</label><br>
                                    S1 - TEKNIK INFORMATIKA
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>Jumlah SKS Diambil</label><br>
                                    <?php echo $total_sks; ?> SKS
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>Jumlah Matakuliah Diambil</label><br>
                                    <?php echo $total_mk; ?>
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>IP Semester</label><br>
                                    <?php echo number_format($ips, 2); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="text-right mb-2">
                                <a href="transkrip_cetak.php" target="_blank">
                                    <button type="button" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print mr-1"></i>Cetak
                                    </button>
                                </a>
                            </div>

                            <?php if (count($transkrip_data) > 0): ?>
                                <?php foreach ($transkrip_data as $semester => $matakuliah_list): ?>
                                    <h4 class="mt-4">SEMESTER <?php echo (int) $semester; ?></h4>
                                    <ul class="list-group mb-4" id="list-semester">
                                        <?php foreach ($matakuliah_list as $mk): ?>
                                            <?php $is_danger = in_array($mk['nilai_huruf'], ['D', 'E'], true); ?>
                                            <li class="list-group-item list-group-item-action flex-column align-items-start <?php echo $is_danger ? 'border-left-danger' : ''; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($mk['nama_mk'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                    <span class="<?php 
                                                        if ($mk['nilai_huruf'] === 'A') echo 'text-success';
                                                        elseif ($is_danger) echo 'text-danger';
                                                        else echo 'text-primary';
                                                    ?>">
                                                        <b><?php echo htmlspecialchars($mk['nilai_huruf'], ENT_QUOTES, 'UTF-8'); ?></b>
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo (int) $mk['sks']; ?> SKS
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Belum ada data transkrip yang tersedia.
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item h6 {
    font-weight: 600;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
.border-left-danger {
    border-left: 3px solid #dc3545 !important;
}
</style>

<?php include 'layout/footer.php'; ?>
