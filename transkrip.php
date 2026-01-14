<?php include 'layout/header.php'; ?>

<?php
// Fetch user academic data from database
include 'config/database.php';

$user_data = null;
$transkrip_data = [];
$total_sks = 0;
$total_mk = 0;
$ips = 0;

if (isset($_SESSION['nim'])) {
    $nim = mysqli_real_escape_string($conn, $_SESSION['nim']);
    
    // Get user data
    $query = "SELECT * FROM users WHERE nim = '$nim'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
    
    // Get transkrip data grouped by semester (ordered by id to maintain original order)
    $query_transkrip = "SELECT * FROM transkrip WHERE nim = '$nim' ORDER BY semester ASC, id ASC";
    $result_transkrip = @mysqli_query($conn, $query_transkrip);
    if ($result_transkrip) {
        while ($row = mysqli_fetch_assoc($result_transkrip)) {
            $semester = $row['semester'];
            if (!isset($transkrip_data[$semester])) {
                $transkrip_data[$semester] = [];
            }
            $transkrip_data[$semester][] = $row;
            $total_sks += $row['sks'];
            $total_mk++;
        }
    }
    
    // Calculate IP Semester (using IPK from transkrip)
    $query_ips = "SELECT 
                    CASE 
                        WHEN SUM(sks) > 0 THEN ROUND(SUM(sks * bobot) / SUM(sks), 2)
                        ELSE 0 
                    END as ips
                  FROM transkrip 
                  WHERE nim = '$nim'";
    $result_ips = @mysqli_query($conn, $query_ips);
    if ($result_ips && $row_ips = mysqli_fetch_assoc($result_ips)) {
        $ips = $row_ips['ips'];
    }
}
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
                                    <?php echo $user_data ? htmlspecialchars($user_data['nama']) : '-'; ?>
                                </div>
                                <div class="col-md-4 col-sm-12 col-xs-12 mb-2">
                                    <label>NIM</label><br>
                                    <?php echo $user_data ? htmlspecialchars($user_data['nim']) : '-'; ?>
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
                                    <h4 class="mt-4">SEMESTER <?php echo $semester; ?></h4>
                                    <ul class="list-group mb-4" id="list-semester">
                                        <?php foreach ($matakuliah_list as $mk): ?>
                                            <li class="list-group-item list-group-item-action flex-column align-items-start <?php echo in_array($mk['nilai_huruf'], ['D', 'E']) ? 'border-left-danger' : ''; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($mk['nama_mk']); ?></h6>
                                                    <span class="<?php 
                                                        if ($mk['nilai_huruf'] == 'A') echo 'text-success';
                                                        elseif (in_array($mk['nilai_huruf'], ['D', 'E'])) echo 'text-danger';
                                                        else echo 'text-primary';
                                                    ?>">
                                                        <b><?php echo htmlspecialchars($mk['nilai_huruf']); ?></b>
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($mk['kode_mk']); ?> • <?php echo $mk['sks']; ?> SKS
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
