<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config/database.php';

$nim = $_SESSION['nim'] ?? '';

// ============================================
// AJAX Handler: Kontrak & Hapus MK
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] === 'kontrak_mk' && isset($_POST['kode_mk'])) {
        $kode_mk = $_POST['kode_mk'];
        
        // Ambil info MK dari mata_kuliah
        $stmt = $conn->prepare("SELECT nama_mk, sks, jenis FROM mata_kuliah WHERE kode_mk = ?");
        $stmt->bind_param("s", $kode_mk);
        $stmt->execute();
        $mk_result = $stmt->get_result();
        
        if ($mk_result && $mk = $mk_result->fetch_assoc()) {
            $stmt->close();
            
            // Cek apakah sudah ada di KRS semester ini
            $stmt = $conn->prepare("SELECT id FROM krs WHERE nim = ? AND kode_mk = ? AND semester_krs = '20252'");
            $stmt->bind_param("ss", $nim, $kode_mk);
            $stmt->execute();
            $check = $stmt->get_result();
            
            if ($check->num_rows == 0) {
                $stmt->close();
                
                // Cek batas maks SKS
                $ip_user = (float) ($conn->query("SELECT ip_semester FROM users WHERE nim = '" . $conn->real_escape_string($nim) . "'")->fetch_assoc()['ip_semester'] ?? 0);
                $maks = ($ip_user >= 3.00) ? 24 : (($ip_user >= 2.50) ? 21 : (($ip_user >= 2.00) ? 18 : 15));
                $sks_sekarang = (int) ($conn->query("SELECT COALESCE(SUM(sks), 0) as total FROM krs WHERE nim = '" . $conn->real_escape_string($nim) . "' AND semester_krs = '20252'")->fetch_assoc()['total']);
                
                if (($sks_sekarang + (int) $mk['sks']) > $maks) {
                    echo json_encode(['status' => 'error', 'message' => 'Melebihi batas maks SKS']);
                    exit;
                }
                
                // Cek juga di kelas table untuk info dosen & jadwal
                $dosen1 = 'TBD';
                $hari = 'TBD';
                $jam_mulai = '08:00';
                $jam_selesai = '10:30';
                $kelas = 'A';
                
                $stmt_kelas = $conn->prepare("SELECT kelas, dosen1, hari, jam_mulai, jam_selesai FROM kelas WHERE kode_mk = ? LIMIT 1");
                if ($stmt_kelas) {
                    $stmt_kelas->bind_param("s", $kode_mk);
                    $stmt_kelas->execute();
                    $kelas_result = $stmt_kelas->get_result();
                    if ($kelas_row = $kelas_result->fetch_assoc()) {
                        $kelas = $kelas_row['kelas'] ?? 'A';
                        $dosen1 = $kelas_row['dosen1'] ?? 'TBD';
                        $hari = $kelas_row['hari'] ?? 'TBD';
                        $jam_mulai = $kelas_row['jam_mulai'] ?? '08:00';
                        $jam_selesai = $kelas_row['jam_selesai'] ?? '10:30';
                    }
                    $stmt_kelas->close();
                }
                
                $stmt = $conn->prepare("INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, hari, jam_mulai, jam_selesai, semester_krs, status_krs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '20252', 'Disetujui')");
                $stmt->bind_param("sssissssss", $nim, $kode_mk, $mk['nama_mk'], $mk['sks'], $mk['jenis'], $kelas, $dosen1, $hari, $jam_mulai, $jam_selesai);
                
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Berhasil kontrak matakuliah']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal kontrak matakuliah']);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'Matakuliah sudah ada di KRS']);
                $stmt->close();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Matakuliah tidak ditemukan']);
            $stmt->close();
        }
        exit;
    }
    
    if ($_POST['ajax_action'] === 'hapus_mk' && isset($_POST['kode_mk'])) {
        $kode_mk = $_POST['kode_mk'];
        
        $stmt = $conn->prepare("DELETE FROM krs WHERE nim = ? AND kode_mk = ? AND semester_krs = '20252'");
        $stmt->bind_param("ss", $nim, $kode_mk);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Berhasil menghapus matakuliah dari KRS']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus matakuliah']);
        }
        $stmt->close();
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    exit;
}

include 'layout/header.php';

// $nim sudah didefinisikan di atas

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

// ambil data KRS untuk semester 8 (20252 = Genap 2025/2026)
$krs_list = [];
$total_sks = 0;

$stmt = $conn->prepare("SELECT k.*, COALESCE(kl.hari, k.hari) as hari, COALESCE(kl.jam_mulai, k.jam_mulai) as jam_mulai, COALESCE(kl.jam_selesai, k.jam_selesai) as jam_selesai FROM krs k LEFT JOIN kelas kl ON k.kode_mk = kl.kode_mk WHERE k.nim = ? AND k.semester_krs = '20252' ORDER BY k.id ASC");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $krs_list[] = $row;
    $total_sks += (int) $row['sks'];
}
$stmt->close();

// IP semester sebelumnya - diambil dari tabel users
$ip_sebelum = (float) ($user_data['ip_semester'] ?? 0);

// Batas maks SKS berdasarkan IPS
if ($ip_sebelum >= 3.00) {
    $maks_sks = 24;
} elseif ($ip_sebelum >= 2.50) {
    $maks_sks = 21;
} elseif ($ip_sebelum >= 2.00) {
    $maks_sks = 18;
} else {
    $maks_sks = 15;
}
$sisa_sks = $maks_sks - $total_sks;

// ambil semua mata kuliah untuk modal kontrak
$mata_kuliah_per_semester = [];
for ($sem = 2; $sem <= 8; $sem += 2) { // hanya semester genap (2,4,6,8)
    $mata_kuliah_per_semester[$sem] = [];
    $stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE semester = ? ORDER BY CASE WHEN kode_mk LIKE 'CSP%' THEN 0 ELSE 1 END ASC, id ASC");
    $stmt->bind_param("i", $sem);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $mata_kuliah_per_semester[$sem][] = $row;
    }
    $stmt->close();
}

// juga ambil semester 7 untuk paket semester ganjil sebelumnya
$mata_kuliah_per_semester[7] = [];
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE semester = 7 ORDER BY CASE WHEN kode_mk LIKE 'CSP%' THEN 0 ELSE 1 END ASC, id ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $mata_kuliah_per_semester[7][] = $row;
}
$stmt->close();

// ambil data kelas per program (Kampus Merdeka)
$kelas_per_program = [];
$result = $conn->query("SELECT * FROM kelas ORDER BY CASE WHEN kode_mk LIKE 'CSP%' THEN 0 ELSE 1 END ASC, id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $program = $row['program'];
        if (!isset($kelas_per_program[$program])) {
            $kelas_per_program[$program] = [];
        }
        $kelas_per_program[$program][] = $row;
    }
}

// Deadline pengisian KRS (dinamis)
$krs_deadline = new DateTime('2027-02-07 23:59:59');
$krs_sudah_tutup = (new DateTime() > $krs_deadline);
?>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>KRS</h1></div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Alert Deadline Pengisian KRS -->
            <div class="row">
                <div class="col-12">
                    <?php if ($krs_sudah_tutup): ?>
                    <div class="alert alert-danger">
                        <span>
                            <i class="icon fas fa-exclamation-triangle"></i>
                            Pengisian KRS sudah ditutup pada 07 Februari 2027
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <span>
                            <i class="icon fas fa-exclamation-triangle"></i>
                            Pengisian KRS akan ditutup pada 07 Februari 2027
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Semester -->
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="callout callout-info">
                        <h5>Semester Genap 2025/2026</h5>
                        <label>Dosen Pembimbing Akademik</label>
                        <p>
                            <?php echo htmlspecialchars($user_data['pembimbing_akademik'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> <br>
                            NIP. <?php echo htmlspecialchars($user_data['nip_pembimbing'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <label>IP Semester Sebelum : </label> <?php echo $ip_sebelum; ?> <br>
                        <label>Jumlah SKS Dikontrak : </label> <?php echo $total_sks; ?>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <?php if (!$krs_sudah_tutup): ?>
            <div class="row mb-2">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-lihat-kelas">
                        <i class="fas fa-plus"></i> MATAKULIAH
                    </button>
                    <?php if (count($krs_list) > 0): ?>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-ajukan">
                        <i class="fas fa-paper-plane"></i> AJUKAN KRS
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Alert Draft KRS - hanya tampil jika ada matakuliah -->
            <?php if (count($krs_list) > 0): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <span>
                            <i class="icon fas fa-exclamation-triangle"></i>
                            KRS anda masih sebagai draf. Jika sudah selesai kontrak KRS, klik tombol AJUKAN KRS untuk mendapat persetujuan Dosen Pembimbing Akademik.
                        </span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Alert belum ada matakuliah dikontrak -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <span>
                            <i class="icon fas fa-exclamation-triangle"></i>
                            Anda belum mengontrak Matakuliah
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Matakuliah Dikontrak -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-solid">
                        <div class="card-header">
                            <h3 class="card-title">Matakuliah Dikontrak</h3>
                            <div class="card-tools"></div>
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
                                                <?php echo ucfirst(htmlspecialchars($mk['jenis'] ?? 'Wajib', ENT_QUOTES, 'UTF-8')); ?> •
                                                <div class="card-tools">
                                                    <?php if (!$krs_sudah_tutup): ?>
                                                    <button type="button" class="btn-hapus btn btn-danger btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h2 class="lead"><b><?php echo strtoupper(htmlspecialchars($mk['nama_mk'], ENT_QUOTES, 'UTF-8')); ?></b></h2>
                                                        <p class="text-muted text-sm"><b>KELAS:</b> <?php echo htmlspecialchars($mk['kelas'] ?? 'A', ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <ul class="ml-4 fa-ul text-muted">
                                                            <li class="small">
                                                                <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div>
                                                                <?php echo !empty($mk['dosen1']) ? htmlspecialchars($mk['dosen1'], ENT_QUOTES, 'UTF-8') : 'TBD'; ?>
                                                            </li>
                                                            <?php if (!in_array($mk['kode_mk'], ['TIK4010', 'TIK4020', 'TIK4040'])): ?>
                                                            <li class="small">
                                                                <span class="fa-li"><i class="fas fa-lg fa-clock"></i></span>
                                                                <?php echo htmlspecialchars($mk['hari'] ?? 'TBD', ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($mk['jam_mulai'] ?? '00:00', ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($mk['jam_selesai'] ?? '00:00', ENT_QUOTES, 'UTF-8'); ?>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kontrak Matakuliah -->
<div class="modal fade" id="modal-lihat-kelas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="position: sticky; top: 0; z-index: 10; background: #fff;">
                <h4 class="modal-title">Kontrak Matakuliah</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 85vh; overflow-y: auto;">
                <div id="accordion">
                    <!-- Paket Semester 2 -->
                    <div class="card card-success">
                        <div class="card-header" data-toggle="collapse" data-parent="#accordion" href="#paket-semester-2">
                            <h4 class="card-title">
                                <a data-toggle="collapse">Paket Semester 2</a>
                            </h4>
                        </div>
                        <div id="paket-semester-2" class="panel-collapse collapse">
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($mata_kuliah_per_semester[2] ?? [] as $mk): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($mk['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $mk['sks']; ?> SKS] <br>
                                        Kelas : A <br>
                                        <?php echo ucfirst($mk['jenis']); ?> <br>
                                        <div class="text-right">
                                            <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                KONTRAK
                                            </button>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Paket Semester 4 -->
                    <div class="card card-success">
                        <div class="card-header" data-toggle="collapse" data-parent="#accordion" href="#paket-semester-4">
                            <h4 class="card-title">
                                <a data-toggle="collapse">Paket Semester 4</a>
                            </h4>
                        </div>
                        <div id="paket-semester-4" class="panel-collapse collapse">
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($mata_kuliah_per_semester[4] ?? [] as $mk): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($mk['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $mk['sks']; ?> SKS] <br>
                                        Kelas : A <br>
                                        <?php echo ucfirst($mk['jenis']); ?> <br>
                                        <div class="text-right">
                                            <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                KONTRAK
                                            </button>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Paket Semester 6 -->
                    <div class="card card-success">
                        <div class="card-header" data-toggle="collapse" data-parent="#accordion" href="#paket-semester-6">
                            <h4 class="card-title">
                                <a data-toggle="collapse">Paket Semester 6</a>
                            </h4>
                        </div>
                        <div id="paket-semester-6" class="panel-collapse collapse">
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($mata_kuliah_per_semester[6] ?? [] as $mk): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($mk['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $mk['sks']; ?> SKS] <br>
                                        Kelas : A <br>
                                        <?php echo ucfirst($mk['jenis']); ?> <br>
                                        <div class="text-right">
                                            <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                KONTRAK
                                            </button>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Paket Semester 7 -->
                    <div class="card card-success">
                        <div class="card-header" data-toggle="collapse" data-parent="#accordion" href="#paket-semester-7">
                            <h4 class="card-title">
                                <a data-toggle="collapse">Paket Semester 7</a>
                            </h4>
                        </div>
                        <div id="paket-semester-7" class="panel-collapse collapse">
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($mata_kuliah_per_semester[7] ?? [] as $mk): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($mk['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $mk['sks']; ?> SKS] <br>
                                        Kelas : A <br>
                                        <?php echo ucfirst($mk['jenis']); ?> <br>
                                        <div class="text-right">
                                            <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                KONTRAK
                                            </button>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Paket Semester 8 -->
                    <div class="card card-success">
                        <div class="card-header" data-toggle="collapse" data-parent="#accordion" href="#paket-semester-8">
                            <h4 class="card-title">
                                <a data-toggle="collapse">Paket Semester 8</a>
                            </h4>
                        </div>
                        <div id="paket-semester-8" class="panel-collapse collapse">
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($mata_kuliah_per_semester[8] ?? [] as $mk): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($mk['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $mk['sks']; ?> SKS] <br>
                                        Kelas : A <br>
                                        <?php echo ucfirst($mk['jenis']); ?> <br>
                                        <div class="text-right">
                                            <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                KONTRAK
                                            </button>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Kampus Merdeka Accordion -->
                    <div id="accordionkm">
                        <?php 
                        $km_programs = array_filter(array_keys($kelas_per_program), function($key) {
                            return strpos($key, 'Kampus Merdeka') !== false;
                        });
                        $km_index = 0;
                        foreach ($km_programs as $program): 
                            $program_id = 'paket-km-' . $km_index;
                            $program_name = str_replace('Kampus Merdeka - ', '', $program);
                        ?>
                        <div class="card card-success">
                            <div class="card-header" data-toggle="collapse" data-parent="#accordionkm" href="#<?php echo $program_id; ?>">
                                <h4 class="card-title">
                                    <a data-toggle="collapse">Kampus Merdeka : Program Studi <?php echo htmlspecialchars(strtoupper($program_name), ENT_QUOTES, 'UTF-8'); ?></a>
                                </h4>
                            </div>
                            <div id="<?php echo $program_id; ?>" class="panel-collapse collapse">
                                <div class="card-body">
                                    <ul class="list-group">
                                        <?php foreach ($kelas_per_program[$program] as $kelas): ?>
                                        <li class="list-group-item">
                                            <?php echo htmlspecialchars($kelas['kode_mk'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(strtoupper($kelas['nama_mk']), ENT_QUOTES, 'UTF-8'); ?> [<?php echo (int) $kelas['sks']; ?> SKS]<br>
                                            Kelas : <?php echo htmlspecialchars($kelas['kelas'] ?? 'A', ENT_QUOTES, 'UTF-8'); ?> <br>
                                            KAMPUS MERDEKA <br>
                                            <?php if (!empty($kelas['dosen1'])): ?>
                                            <ul class="ml-4 mt-1 fa-ul text-muted">
                                                <li class="small">
                                                    <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div> 
                                                    <?php echo htmlspecialchars($kelas['dosen1'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                                <?php if (!empty($kelas['dosen2'])): ?>
                                                <li class="small">
                                                    <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div> 
                                                    <?php echo htmlspecialchars($kelas['dosen2'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (!empty($kelas['dosen3'])): ?>
                                                <li class="small">
                                                    <div class="fa-li"><i class="fas fa-lg fa-chalkboard-teacher"></i></div> 
                                                    <?php echo htmlspecialchars($kelas['dosen3'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (!empty($kelas['hari']) && !empty($kelas['jam_mulai'])): ?>
                                                <li class="small">
                                                    <span class="fa-li"><i class="fas fa-lg fa-clock"></i></span>
                                                    <?php echo htmlspecialchars($kelas['hari'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($kelas['jam_mulai'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($kelas['jam_selesai'], ENT_QUOTES, 'UTF-8'); ?>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                            <?php endif; ?>
                                            <div class="text-right">
                                                <button class="btn btn-kontrak btn-success btn-xs" data-kode="<?php echo htmlspecialchars($kelas['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    KONTRAK
                                                </button>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php 
                            $km_index++;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajukan KRS -->
<div class="modal fade" id="modal-ajukan" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajukan KRS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengajukan KRS untuk disetujui oleh Dosen Pembimbing Akademik?</p>
                <p><strong>Total SKS Dikontrak:</strong> <?php echo $total_sks; ?> SKS</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success">Ya, Ajukan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modal-konfirmasi-hapus" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                Apa anda yakin ingin batal kontrak Matakuliah ini ?
                <input type="hidden" id="hapus-kode-mk" value="">
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn-konfirmasi-hapus btn btn-danger">Ya</button>
            </div>
        </div>
    </div>
</div>



<!-- Alert Notification Container -->
<div class="alert alert-dismissible alert-success alert_js" id="alert-success" style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;">
    <h5><i class="icon fa fa-check"></i><span class="alert_js--text">Berhasil</span></h5>
</div>
<div class="alert alert-dismissible alert-danger alert_js" id="alert-danger" style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;">
    <h5><i class="icon fa fa-ban"></i><span class="alert_js--text">Gagal</span></h5>
</div>

<?php include 'layout/footer.php'; ?>

<script>
$(document).ready(function() {
    // Variabel untuk menyimpan kode MK yang akan dihapus
    var kodeMKHapus = '';
    
    // Ketika tombol hapus diklik, simpan kode MK dan tampilkan modal
    $(document).on('click', '.btn-hapus', function() {
        kodeMKHapus = $(this).data('kode');
        $('#modal-konfirmasi-hapus').modal('show');
    });
    
    // Ketika konfirmasi Ya diklik
    $(document).on('click', '.btn-konfirmasi-hapus', function() {
        $('#modal-konfirmasi-hapus').modal('hide');
        showLoading();
        
        $.ajax({
            url: 'krs.php',
            method: 'POST',
            data: { ajax_action: 'hapus_mk', kode_mk: kodeMKHapus },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.status === 'success') {
                    showAlert(response.message, 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                hideLoading();
                showAlert('Terjadi kesalahan. Coba lagi.', 'danger');
            }
        });
    });
    
    // Ketika tombol kontrak diklik
    $(document).on('click', '.btn-kontrak', function() {
        var kodeMK = $(this).data('kode');
        var btn = $(this);
        btn.prop('disabled', true).text('Memproses...');
        showLoading();
        
        $.ajax({
            url: 'krs.php',
            method: 'POST',
            data: { ajax_action: 'kontrak_mk', kode_mk: kodeMK },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.status === 'success') {
                    showAlert(response.message, 'success');
                    setTimeout(function() {
                        $('#modal-lihat-kelas').modal('hide');
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(response.message, 'danger');
                    btn.prop('disabled', false).text('KONTRAK');
                }
            },
            error: function() {
                hideLoading();
                showAlert('Terjadi kesalahan. Coba lagi.', 'danger');
                btn.prop('disabled', false).text('KONTRAK');
            }
        });
    });
    
    // Fungsi untuk menampilkan loading
    function showLoading() {
        $('#loading-container').css('display', 'flex');
    }
    
    // Fungsi untuk menyembunyikan loading
    function hideLoading() {
        $('#loading-container').hide();
    }
    
    // Fungsi untuk menampilkan alert notification
    function showAlert(message, type) {
        var alertId = type === 'success' ? '#alert-success' : '#alert-danger';
        $(alertId).find('.alert_js--text').text(message);
        $(alertId).fadeIn();
        
        // Auto hide after 3 seconds
        setTimeout(function() {
            $(alertId).fadeOut();
        }, 3000);
    }
});
</script>
