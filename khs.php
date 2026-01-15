<?php include 'layout/header.php'; ?>

<?php
include 'config/database.php';

$user_data = null;
$khs_data = [];
$total_sks = 0;
$total_bobot = 0;
$total_mk = 0;
$ips = 0;
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

if (isset($_SESSION['nim'])) {
    $nim = mysqli_real_escape_string($conn, $_SESSION['nim']);
    
    // Get user data
    $query = "SELECT * FROM users WHERE nim = '$nim'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
    
    // Get available semesters from KRS
    $semesters = [];
    $query_sem = "SELECT DISTINCT semester_krs FROM krs WHERE nim = '$nim' ORDER BY semester_krs ASC";
    $result_sem = @mysqli_query($conn, $query_sem);
    if ($result_sem) {
        while ($row = mysqli_fetch_assoc($result_sem)) {
            $semesters[] = $row['semester_krs'];
        }
    }
    
    // If semester selected, get KHS data from KRS table
    if ($selected_semester) {
        $query_khs = "SELECT * FROM krs WHERE nim = '$nim' AND semester_krs = '$selected_semester' ORDER BY id ASC";
        $result_khs = @mysqli_query($conn, $query_khs);
        if ($result_khs) {
            while ($row = mysqli_fetch_assoc($result_khs)) {
                $khs_data[] = $row;
                $total_sks += $row['sks'];
                $total_bobot += $row['sks'] * $row['bobot'];
                $total_mk++;
            }
        }
        
        // Calculate IPS
        $ips = ($total_sks > 0) ? round($total_bobot / $total_sks, 2) : 0;
    }
}

// Helper function to format semester
function formatSemester($code) {
    if (strlen($code) < 5) return $code;
    $year = substr($code, 0, 4);
    $period = substr($code, 4, 1);
    $nextYear = intval($year) + 1;
    $periodName = ($period == '1') ? 'Gasal' : 'Genap';
    return "$year / $nextYear $periodName";
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>KHS</h1></div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="callout callout-info">
                        Kartu Hasil Studi merupakan fasilitas yang dapat digunakan untuk melihat hasil studi mahasiswa per semester. Selain dapat dilihat secara online, hasil studi ini juga dapat dicetak.
                    </div>

                    <div class="card">
                        <form action="khs.php" method="GET">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12 col-xs-12 col-md-4 mb-2">
                                        <h4>Semester</h4>
                                        <select name="semester" class="form-control" required>
                                            <option value="">-- Pilih Semester --</option>
                                            <?php foreach ($semesters as $sem): ?>
                                            <option value="<?php echo $sem; ?>" <?php echo ($selected_semester == $sem) ? 'selected' : ''; ?>>
                                                <?php echo formatSemester($sem); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary btn-sm"><b>LIHAT KHS</b></button>
                            </div>
                        </form>
                    </div>

                    <?php if ($selected_semester && !empty($khs_data)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kartu Hasil Studi</h3>
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
                                    <label>Semester</label><br>
                                    <?php echo formatSemester($selected_semester); ?>
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

                            <div class="text-right mb-3">
                                <a href="khs_cetak.php?semester=<?php echo $selected_semester; ?>" target="_blank">
                                    <button type="button" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print mr-1"></i> Cetak
                                    </button>
                                </a>
                            </div>

                            <div id="accordion">
                                <?php foreach ($khs_data as $index => $khs): ?>
                                <div class="card card-default">
                                    <div class="card-header" data-toggle="collapse" href="#collapse-<?php echo $index; ?>" style="cursor: pointer;">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6><?php echo htmlspecialchars($khs['nama_mk']); ?></h6>
                                            <div class="text-right">
                                                <?php if (!empty($khs['nilai_huruf'])): ?>
                                                <span class="badge badge-<?php echo ($khs['bobot'] >= 3) ? 'success' : (($khs['bobot'] >= 2) ? 'warning' : 'danger'); ?> mr-2">
                                                    <?php echo $khs['nilai_huruf']; ?>
                                                </span>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-flat btn-xs btn-primary">
                                                    <i class="fa fa-edit"></i> Isi Kuesioner
                                                </a>
                                            </div>
                                        </div>
                                        <small>
                                            <?php echo htmlspecialchars($khs['kode_mk']); ?> • 
                                            Kelas <?php echo htmlspecialchars($khs['kelas']); ?> • 
                                            <?php echo $khs['sks']; ?> SKS 
                                        </small>
                                    </div>
                                    <div id="collapse-<?php echo $index; ?>" class="collapse" data-parent="#accordion">
                                        <div class="card-body text-sm">
                                            Detail nilai per dosen:
                                            <br>
                                            <br>
                                            <ol>
                                                <?php if ($khs['dosen1']): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($khs['dosen1']); ?>:
                                                    <?php if (isset($khs['nilai_dosen1']) && $khs['nilai_dosen1'] !== null): ?>
                                                    <strong><label class="badge badge-light"><?php echo number_format($khs['nilai_dosen1'], 2); ?></label></strong>
                                                    <?php else: ?>
                                                    <strong><label class="badge badge-warning">belum isi</label></strong>
                                                    <?php endif; ?>
                                                </li>
                                                <?php endif; ?>
                                                <?php if ($khs['dosen2']): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($khs['dosen2']); ?>:
                                                    <?php if (isset($khs['nilai_dosen2']) && $khs['nilai_dosen2'] !== null): ?>
                                                    <strong><label class="badge badge-light"><?php echo number_format($khs['nilai_dosen2'], 2); ?></label></strong>
                                                    <?php else: ?>
                                                    <strong><label class="badge badge-warning">belum isi</label></strong>
                                                    <?php endif; ?>
                                                </li>
                                                <?php endif; ?>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($selected_semester): ?>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Tidak ada data KHS untuk semester ini.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
