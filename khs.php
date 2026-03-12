<?php
include 'layout/header.php';
include 'config/database.php';

$user_data = null;
$khs_data = [];
$total_sks = 0;
$total_bobot = 0;
$total_mk = 0;
$ips = 0;
$selected_semester = $_GET['semester'] ?? '';
$semesters = [];

$nim = $_SESSION['nim'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

$stmt = $conn->prepare("SELECT DISTINCT semester, tahun_akademik, periode FROM khs WHERE nim = ? ORDER BY semester ASC");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $semesters[] = [
        'semester' => $row['semester'],
        'tahun_akademik' => $row['tahun_akademik'],
        'periode' => $row['periode']
    ];
}
$stmt->close();

if ($selected_semester !== '') {
    $stmt = $conn->prepare("SELECT * FROM khs WHERE nim = ? AND semester = ? ORDER BY id ASC");
    $stmt->bind_param("si", $nim, $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $khs_data[] = $row;
        $total_sks += (int) $row['sks'];
        $total_bobot += (int) $row['sks'] * (float) $row['bobot'];
        $total_mk++;
    }
    $stmt->close();
    
    $ips = ($total_sks > 0) ? round($total_bobot / $total_sks, 2) : 0;
}

function formatSemesterKHS($tahun_akademik, $periode) {
    if (empty($tahun_akademik)) return '-';
    return "$tahun_akademik $periode";
}

$selected_tahun_akademik = '';
$selected_periode = '';
if ($selected_semester !== '') {
    foreach ($semesters as $sem) {
        if ((string)$sem['semester'] === (string)$selected_semester) {
            $selected_tahun_akademik = $sem['tahun_akademik'];
            $selected_periode = $sem['periode'];
            break;
        }
    }
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
                                            <option value="<?php echo (int) $sem['semester']; ?>" <?php echo ((string)$selected_semester === (string)$sem['semester']) ? 'selected' : ''; ?>>
                                                Semester <?php echo (int) $sem['semester']; ?> - <?php echo formatSemesterKHS($sem['tahun_akademik'], $sem['periode']); ?>
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

                    <?php if ($selected_semester !== '' && !empty($khs_data)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kartu Hasil Studi</h3>
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
                                    <label>Semester</label><br>
                                    <?php echo formatSemesterKHS($selected_tahun_akademik, $selected_periode); ?>
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
                                <a href="khs_cetak.php?semester=<?php echo htmlspecialchars($selected_semester, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
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
                                            <h6><?php echo strtoupper(htmlspecialchars($khs['nama_mk'], ENT_QUOTES, 'UTF-8')); ?></h6>
                                            <div class="text-right">
                                                <?php if (!empty($khs['nilai_huruf'])): ?>
                                                <span class="text-dark mr-2">
                                                    <b><?php echo htmlspecialchars($khs['nilai_huruf'], ENT_QUOTES, 'UTF-8'); ?></b>
                                                </span>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-flat btn-xs btn-primary">
                                                    <i class="fa fa-edit"></i> Isi Kuesioner
                                                </a>
                                            </div>
                                        </div>
                                        <small>
                                            <?php echo htmlspecialchars($khs['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> • 
                                            Kelas <?php echo htmlspecialchars($khs['kelas'] ?? 'A', ENT_QUOTES, 'UTF-8'); ?> • 
                                            <?php echo (int) $khs['sks']; ?> SKS 
                                        </small>
                                    </div>
                                    <div id="collapse-<?php echo $index; ?>" class="collapse" data-parent="#accordion">
                                        <div class="card-body text-sm">
                                            Detail nilai per dosen:
                                            <br><br>
                                            <ol>
                                                <?php if (!empty($khs['dosen1'])): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($khs['dosen1'], ENT_QUOTES, 'UTF-8'); ?>:
                                                    <?php if (isset($khs['nilai_dosen1']) && $khs['nilai_dosen1'] !== null): ?>
                                                    <strong><label class="badge badge-light"><?php echo number_format((float) $khs['nilai_dosen1'], 2); ?></label></strong>
                                                    <?php else: ?>
                                                    <strong><label class="badge badge-warning">belum isi</label></strong>
                                                    <?php endif; ?>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (!empty($khs['dosen2'])): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($khs['dosen2'], ENT_QUOTES, 'UTF-8'); ?>:
                                                    <?php if (isset($khs['nilai_dosen2']) && $khs['nilai_dosen2'] !== null): ?>
                                                    <strong><label class="badge badge-light"><?php echo number_format((float) $khs['nilai_dosen2'], 2); ?></label></strong>
                                                    <?php else: ?>
                                                    <strong><label class="badge badge-warning">belum isi</label></strong>
                                                    <?php endif; ?>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (empty($khs['dosen1']) && empty($khs['dosen2'])): ?>
                                                <li>Data dosen belum tersedia</li>
                                                <?php endif; ?>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($selected_semester !== ''): ?>
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
