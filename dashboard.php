<?php include 'layout/header.php'; ?>

<?php
// konstanta evaluasi studi
define('MIN_IPK', 2.00);
define('MAX_MASA_STUDI', 14);

// include database hanya sekali karena header.php sudah include session
include 'config/database.php';

// init variabel
$user_data = null;
$sks_lulus = 0;
$ipk = 0.00;
$bahaya_sks = false;
$bahaya_ipk = false;
$tampilkan_warning = false;
$min_sks_semester = 0;
$semester_target = 1;
$semester_mahasiswa = 0;
$pengumuman_list = [];

// skip kalo belum login
if (!isset($_SESSION['nim'])) {
    header('Location: index.php');
    exit;
}

$nim = $_SESSION['nim'];

// ambil data user dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $semester_mahasiswa = (int) ($user_data['semester'] ?? 0);
}
$stmt->close();

// ============================================
// LOGIKA PERINGATAN EVALUASI STUDI BERTAHAP
// Berdasarkan standar Universitas Indonesia:
// Semester 1-2: SKS < 24 atau IPK < 2.00
// Semester 3-4: SKS < 48 atau IPK < 2.00
// Semester 5-6: SKS < 72 atau IPK < 2.00
// Semester 7+:  SKS < 96 atau IPK < 2.00
// ============================================

// hitung SKS lulus (nilai selain D dan E)
$stmt = $conn->prepare("SELECT COALESCE(SUM(sks), 0) as total_sks 
                        FROM transkrip 
                        WHERE nim = ? AND nilai_huruf NOT IN ('D', 'E')");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $sks_lulus = (int) $row['total_sks'];
}
$stmt->close();

// hitung IPK: SUM(sks * bobot) / SUM(sks)
$stmt = $conn->prepare("SELECT 
                CASE 
                    WHEN SUM(sks) > 0 THEN SUM(sks * bobot) / SUM(sks)
                    ELSE 0 
                END as ipk_hitung
              FROM transkrip 
              WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $ipk = (float) $row['ipk_hitung'];
}
$stmt->close();

// tentukan batas SKS berdasarkan semester
if ($semester_mahasiswa >= 7) {
    $min_sks_semester = 96;
    $semester_target = 8;
} elseif ($semester_mahasiswa >= 5) {
    $min_sks_semester = 72;
    $semester_target = $semester_mahasiswa + 1;
} elseif ($semester_mahasiswa >= 3) {
    $min_sks_semester = 48;
    $semester_target = $semester_mahasiswa + 1;
} else {
    $min_sks_semester = 24;
    $semester_target = $semester_mahasiswa + 1;
}

// cek status bahaya
$bahaya_ipk = ($ipk < MIN_IPK);
$bahaya_sks = ($sks_lulus < $min_sks_semester);
$tampilkan_warning = ($bahaya_sks || $bahaya_ipk);

// ambil pengumuman khusus user ini
$stmt = $conn->prepare("SELECT judul, isi, role, oleh, created_at 
                        FROM pengumuman 
                        WHERE nim = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pengumuman_list[] = $row;
}
$stmt->close();

// helper: format tanggal ke bahasa Indonesia
function formatTanggalID($datetime) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i:s', $ts);
}

// hitung masa studi
$masa_studi = $semester_mahasiswa;
$sisa_masa_studi = max(0, MAX_MASA_STUDI - $semester_mahasiswa);

// ambil first name untuk greeting
$first_name = isset($nama) ? strtoupper(explode(' ', $nama)[0]) : 'USER';
?>

<!-- CSS khusus dashboard -->
<style>
    .btn-peringatan {
        background-color: #dc3545 !important;
        border: 2px solid #fff !important;
        color: #fff !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }
    .btn-peringatan:hover {
        background-color: #a71d2a !important;
        color: #fff !important;
    }
</style>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>Beranda</h1></div>
    </div>
    <input type="hidden" id="user_session" value="<?php echo htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Welcome Section -->
                <div class="col-sm-12 col-md-12">
                    <div class="jumbotron bg-white mb-3">
                        <h2 class="display-6">Halo, <?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?> !</h2>
                        <p class="lead">Selamat datang di <span style="font-weight: bold; font-size: 16pt;">PORTAL INSPIRE</span> Universitas Sam Ratulangi.</p>
                        <a href="https://www.unsrat.ac.id" target="_blank">
                            <button class="btn btn-flat btn-primary">
                                <i class="fas fa-globe"></i> Website Unsrat
                            </button>
                        </a>
                        <a href="https://dashboard.unsrat.ac.id" target="_blank">
                            <button class="btn btn-flat btn-primary">
                                <i class="fas fa-chart-area"></i> Dashboard Unsrat
                            </button>
                        </a>
                    </div>
                </div>

                <!-- Academic Info Section -->
                <div class="col-sm-12 col-md-6">
                    <!-- Perhatian - Selalu ditampilkan -->
                    <div class="alert alert-danger alert-dismissible text-center">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian !</h5>
                        
                        <?php if ($tampilkan_warning): ?>
                            <?php if ($bahaya_sks && $bahaya_ipk): ?>
                            <!-- keduanya kurang -->
                            <p>Saat ini jumlah total SKS lulus anda adalah <?php echo $sks_lulus; ?> SKS, diharapkan untuk semester <?php echo $semester_target; ?> Anda mengontrak dan lulus lebih banyak SKS.</p>
                            <p><i>Jika jumlah SKS lulus tidak mencapai <?php echo $min_sks_semester; ?> SKS, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                            <hr>
                            <p>Saat ini Indeks Prestasi Kumulatif (IPK) anda adalah <strong><?php echo number_format($ipk, 2); ?></strong>, diharapkan untuk semester <?php echo $semester_target; ?> Anda memperbaiki nilai mata kuliah.</p>
                            <p><i>Jika IPK tidak mencapai 2.00, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                            
                            <?php elseif ($bahaya_sks): ?>
                            <!-- SKS kurang -->
                            <p>Saat ini jumlah total SKS lulus anda adalah <?php echo $sks_lulus; ?> SKS, diharapkan untuk semester <?php echo $semester_target; ?> Anda mengontrak dan lulus lebih banyak SKS.</p>
                            <p><i>Jika jumlah SKS lulus tidak mencapai <?php echo $min_sks_semester; ?> SKS, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                            
                            <?php elseif ($bahaya_ipk): ?>
                            <!-- IPK kurang -->
                            <p>Saat ini Indeks Prestasi Kumulatif (IPK) anda adalah <strong><?php echo number_format($ipk, 2); ?></strong>, diharapkan untuk semester <?php echo $semester_target; ?> Anda memperbaiki nilai mata kuliah.</p>
                            <p><i>Jika IPK tidak mencapai 2.00, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                            <?php endif; ?>
                            
                            <a href="perencanaan.php" class="btn btn-sm mt-2 btn-peringatan">
                                <i class="fas fa-list mr-1"></i> Lihat Perencanaan Studi
                            </a>
                        <?php endif; ?>
                        <!-- Jika tidak ada warning, alert tetap muncul tapi kosong -->
                    </div>

                    <!-- Semester Info -->
                    <div class="callout callout-info">
                        <?php if ($user_data): ?>
                        Saat ini Anda sedang menempuh semester <?php echo (int) $user_data['semester']; ?> T.A <?php echo htmlspecialchars($user_data['tahun_akademik'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($user_data['periode'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <br><br>
                        <label>MASA STUDI :</label> <?php echo $masa_studi; ?> Semester <br>
                        <label>SISA MASA STUDI MAKS. :</label> <?php echo $sisa_masa_studi; ?> Semester <br>
                        <label>STATUS PDDIKTI :</label> <?php echo htmlspecialchars($user_data['status_pddikti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> <br>
                        <?php else: ?>
                        Saat ini Anda sedang menempuh semester - T.A - <br><br>
                        <label>MASA STUDI :</label> - Semester <br>
                        <label>SISA MASA STUDI MAKS. :</label> - Semester <br>
                        <label>STATUS PDDIKTI :</label> - <br>
                        <?php endif; ?>
                    </div>

                    <!-- IPK & SKS Info -->
                    <div class="callout callout-info">
                        <label>IPK</label> : <?php echo number_format($ipk, 2); ?> <br>
                        <label>SKS Lulus</label> : <?php echo $sks_lulus; ?>
                    </div>
                </div>

                <!-- Pengumuman Section -->
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title text-uppercase w-100 text-center"><strong>Pengumuman</strong></h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="direct-chat-messages" style="height: 12em;">
                                <ul class="products-list product-list-in-card pl-2 pr-2">
                                    <?php if (count($pengumuman_list) > 0): ?>
                                        <?php foreach ($pengumuman_list as $pengumuman): ?>
                                        <li class="item">
                                            <div>
                                                <a href="#" class="product-title text-danger text-capitalize"><?php echo htmlspecialchars($pengumuman['judul'], ENT_QUOTES, 'UTF-8'); ?></a>
                                                <span class="float-right"><small class="text-muted"><i class="fas fa-clock mr-1"></i><?php echo formatTanggalID($pengumuman['created_at']); ?></small></span><br>
                                                Oleh: <label class="badge badge-success"><?php echo htmlspecialchars($pengumuman['role'], ENT_QUOTES, 'UTF-8'); ?></label> <?php echo htmlspecialchars($pengumuman['oleh'], ENT_QUOTES, 'UTF-8'); ?>
                                                <span class="product-description">
                                                    <small><?php echo htmlspecialchars($pengumuman['isi'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                </span>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="item">
                                            <div class="text-center text-muted p-3">
                                                <small>Tidak ada pengumuman</small>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="#" class="uppercase">LIHAT SEMUA</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kegiatan Hari Ini & Calendar Row -->
            <div class="row">
                <div class="col-sm-12 col-md-5 mt-3">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title text-uppercase w-100 text-center"><strong>Kegiatan hari ini</strong></h3>
                        </div>
                        <div class="card-body p-2">
                            <div class="alert alert-warning text-center">
                                <small>Tidak ada kegiatan hari ini yang terjadwal</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="mb-3" id="calendar"></div>
                </div>
            </div>

            <!-- Informasi Menu Section -->
            <div class="box-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-right">
                            <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                <i class="fas fa-book mr-1"></i> Informasi Menu
                            </button>
                        </p>
                        <div class="collapse" id="collapseExample">
                            <div class="card card-body">
                                <div class="row d-flex align-items-stretch">
                                    <?php 
                                    $menu_items = [
                                        ['title' => 'BIODATA', 'desc' => 'Untuk melakukan pergantian biodata. Contoh: (Foto untuk wisuda, Data pribadi, dan data lainnya)'],
                                        ['title' => 'PERKULIAHAN', 'desc' => 'Untuk melihat Jadwal, KRS, KHS dan Transkrip Mahasiswa'],
                                        ['title' => 'KEMAHASISWAAN', 'desc' => 'Aplikasi untuk pendaftaran beasiswa, isi prestasi dan organisasi mahasiswa'],
                                        ['title' => 'KKT', 'desc' => 'Aplikasi untuk pendaftaran KKT (Kuliah Kerja Terpadu)'],
                                        ['title' => 'BIMBINGAN AKADEMIK', 'desc' => 'Aplikasi untuk melakukan komunikasi dengan dosen pembimbing akademik'],
                                        ['title' => 'SKRIPSI', 'desc' => 'Aplikasi untuk pengurusan skripsi secara online'],
                                        ['title' => 'WISUDA', 'desc' => 'Aplikasi untuk pendaftaran wisuda.'],
                                        ['title' => 'BILLING', 'desc' => 'Aplikasi untuk generate billing pembayaran.'],
                                    ];
                                    foreach ($menu_items as $item): ?>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b><?php echo $item['title']; ?></b></h2>
                                                    <p class="text-muted text-sm"><?php echo $item['desc']; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Menu Modal -->
<div class="modal fade" id="modal-app-list">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title w-100">Menu Cepat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="text-center">
                            <?php 
                            $quick_menu = [
                                ['href' => 'dashboard.php', 'icon' => 'fa fa-home', 'label' => 'Beranda'],
                                ['href' => 'biodata.php', 'icon' => 'fas fa-user', 'label' => 'Biodata'],
                                ['href' => 'jadwal.php', 'icon' => 'fas fa-clock', 'label' => 'Jadwal Kuliah'],
                                ['href' => 'krs.php', 'icon' => 'fas fa-list', 'label' => 'KRS'],
                                ['href' => 'perencanaan.php', 'icon' => 'fas fa-calendar-check', 'label' => 'Perencanaan Studi'],
                                ['href' => 'khs.php', 'icon' => 'fas fa-list', 'label' => 'KHS'],
                                ['href' => 'transkrip.php', 'icon' => 'fas fa-list-alt', 'label' => 'Transkrip'],
                                ['href' => 'billing.php', 'icon' => 'fas fa-money-bill-alt', 'label' => 'Billing'],
                            ];
                            foreach ($quick_menu as $menu): ?>
                            <a class="btn btn-app" href="<?php echo $menu['href']; ?>">
                                <i class="<?php echo $menu['icon']; ?>"></i> <?php echo $menu['label']; ?>
                            </a>
                            <?php endforeach; ?>
                            <a class="btn btn-app" id="btn-app-logout" data-toggle="modal" data-target="#mdl-logout">
                                <i class="fas fa-power-off"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    if (calendarEl && typeof FullCalendar !== 'undefined') {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                start: 'prev,next today',
                center: 'title',
                end: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: []
        });
        calendar.render();
    }
});
</script>

<?php include 'layout/footer.php'; ?>
