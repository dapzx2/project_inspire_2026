<?php include 'layout/header.php'; ?>

<?php
// Fetch user academic data from database
include 'config/database.php';

$user_data = null;
$sks_lulus = 0;
$ipk = 0.00;
$bahaya_sks = false;
$bahaya_ipk = false;
$tampilkan_warning = false;

if (isset($_SESSION['nim'])) {
    $nim = mysqli_real_escape_string($conn, $_SESSION['nim']);
    $query = "SELECT * FROM users WHERE nim = '$nim'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
    
    // ============================================
    // LOGIKA PERINGATAN EVALUASI STUDI
    // ============================================
    
    // Hitung SKS Lulus dari transkrip (nilai != 'E' dan != 'D')
    $query_sks_lulus = "SELECT COALESCE(SUM(sks), 0) as total_sks 
                        FROM transkrip 
                        WHERE nim = '$nim' AND nilai_huruf NOT IN ('D', 'E')";
    $result_sks = @mysqli_query($conn, $query_sks_lulus);
    if ($result_sks && $row_sks = mysqli_fetch_assoc($result_sks)) {
        $sks_lulus = (int) $row_sks['total_sks'];
    }
    
    // Hitung IPK dari transkrip: SUM(sks * bobot) / SUM(sks)
    $query_ipk = "SELECT 
                    CASE 
                        WHEN SUM(sks) > 0 THEN SUM(sks * bobot) / SUM(sks)
                        ELSE 0 
                    END as ipk_hitung
                  FROM transkrip 
                  WHERE nim = '$nim'";
    $result_ipk = @mysqli_query($conn, $query_ipk);
    if ($result_ipk && $row_ipk = mysqli_fetch_assoc($result_ipk)) {
        $ipk = (float) $row_ipk['ipk_hitung'];
    }
    
    // Tentukan status bahaya
    $bahaya_sks = ($sks_lulus < 96);
    $bahaya_ipk = ($ipk < 2.00);
    
    // Tentukan apakah warning ditampilkan (hanya jika semester >= 7)
    $semester_mahasiswa = isset($user_data['semester']) ? (int) $user_data['semester'] : 0;
    $tampilkan_warning = (($bahaya_sks || $bahaya_ipk) && $semester_mahasiswa >= 7);
}

// Fetch pengumuman (with error handling)
$pengumuman_list = [];
$query_pengumuman = "SELECT * FROM pengumuman ORDER BY created_at DESC LIMIT 5";
$result_pengumuman = @mysqli_query($conn, $query_pengumuman);
if ($result_pengumuman) {
    while ($row = mysqli_fetch_assoc($result_pengumuman)) {
        $pengumuman_list[] = $row;
    }
}
?>

<!-- Additional CSS for Dashboard -->

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
    <input class="" type="hidden" id="user_session" value="<?php echo htmlspecialchars($nim); ?>">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Welcome Section -->
                <div class="col-sm-12 col-md-12">
                    <div class="jumbotron bg-white mb-3">
                        <?php 
                        // Extract first name only and convert to UPPERCASE
                        $first_name = strtoupper(explode(' ', $nama)[0]);
                        ?>
                        <h2 class="display-6">Halo, <?php echo htmlspecialchars($first_name); ?> !</h2>
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
                    <?php if ($tampilkan_warning): ?>
                    <!-- Peringatan Evaluasi Studi - Hanya ditampilkan jika ada bahaya -->
                    <div class="alert alert-danger" style="text-align: center;">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian !</h5>
                        
                        <?php if ($bahaya_sks && $bahaya_ipk): ?>
                        <!-- KONDISI 3: Keduanya Kurang -->
                        <p>Saat ini jumlah total SKS lulus anda adalah <?php echo $sks_lulus; ?> SKS, diharapkan untuk semester 8 Anda mengontrak dan lulus lebih banyak SKS.</p>
                        <p><i>Jika jumlah SKS lulus tidak mencapai 96 SKS, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                        <hr>
                        <p>Saat ini Indeks Prestasi Kumulatif (IPK) anda adalah <strong><?php echo number_format($ipk, 2); ?></strong>, diharapkan untuk semester 8 Anda memperbaiki nilai mata kuliah.</p>
                        <p><i>Jika IPK tidak mencapai 2.00, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                        
                        <?php elseif ($bahaya_sks): ?>
                        <!-- KONDISI 1: SKS Kurang -->
                        <p>Saat ini jumlah total SKS lulus anda adalah <?php echo $sks_lulus; ?> SKS, diharapkan untuk semester 8 Anda mengontrak dan lulus lebih banyak SKS.</p>
                        <p><i>Jika jumlah SKS lulus tidak mencapai 96 SKS, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                        
                        <?php elseif ($bahaya_ipk): ?>
                        <!-- KONDISI 2: IPK Kurang -->
                        <p>Saat ini Indeks Prestasi Kumulatif (IPK) anda adalah <strong><?php echo number_format($ipk, 2); ?></strong>, diharapkan untuk semester 8 Anda memperbaiki nilai mata kuliah.</p>
                        <p><i>Jika IPK tidak mencapai 2.00, maka akan diberikan sanksi maksimal diberhentikan sebagai mahasiswa karena alasan akademik.</i></p>
                        <?php endif; ?>
                        
                        <a href="perencanaan.php" class="btn btn-sm mt-2 btn-peringatan">
                            <i class="fas fa-list mr-1"></i> Lihat Perencanaan Studi
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Semester Info -->
                    <div class="callout callout-info">
                        <?php if ($user_data): ?>
                        <?php 
                        $masa_studi_curr = $semester_mahasiswa;
                        $sisa_masa_studi_curr = max(0, 14 - $semester_mahasiswa);
                        ?>
                        Saat ini Anda sedang menempuh semester <?php echo $user_data['semester']; ?> T.A <?php echo htmlspecialchars($user_data['tahun_akademik']); ?> <?php echo htmlspecialchars($user_data['periode']); ?> <br><br>
                        <label>MASA STUDI :</label> <?php echo $masa_studi_curr; ?> Semester <br>
                        <label>SISA MASA STUDI MAKS. :</label> <?php echo $sisa_masa_studi_curr; ?> Semester <br>
                        <label>STATUS PDDIKTI :</label> <?php echo htmlspecialchars($user_data['status_pddikti']); ?> <br>
                        <?php else: ?>
                        Saat ini Anda sedang menempuh semester - T.A - <br><br>
                        <label>MASA STUDI :</label> - Semester <br>
                        <label>SISA MASA STUDI MAKS. :</label> - Semester <br>
                        <label>STATUS PDDIKTI :</label> - <br>
                        <?php endif; ?>
                    </div>

                    <!-- IPK & SKS Info -->
                    <div class="callout callout-info">
                        <?php if ($user_data): ?>
                        <label>IPK</label> : <?php echo number_format($ipk, 2); ?> <br>
                        <label>SKS Lulus</label> : <?php echo $sks_lulus; ?>
                        <?php else: ?>
                        <label>IPK</label> : - <br>
                        <label>SKS Lulus</label> : -
                        <?php endif; ?>
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
                                            <div class="">
                                                <a href="#" class="product-title text-danger text-capitalize"><?php echo htmlspecialchars($pengumuman['judul']); ?></a>
                                                <span class="float-right"><small class="text-muted"><i class="fas fa-clock mr-1"></i><?php 
                                                    $date_str = date('d F Y H:i:s', strtotime($pengumuman['created_at']));
                                                    $en_months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                                    $id_months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                    echo str_replace($en_months, $id_months, $date_str);
                                                ?></small></span><br>
                                                Oleh: <label class="badge badge-success"><?php echo htmlspecialchars($pengumuman['role']); ?></label> <?php echo htmlspecialchars($pengumuman['oleh']); ?>
                                                <span class="product-description">
                                                    <small><?php echo htmlspecialchars($pengumuman['isi']); ?></small>
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
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>BIODATA</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Untuk melakukan pergantian biodata. Contoh: (Foto untuk wisuda, Data pribadi, dan data lainnya)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>PERKULIAHAN</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Untuk melihat Jadwal, KRS, KHS dan Transkrip Mahasiswa
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>KEMAHASISWAAN</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk pendaftaran beasiswa, isi prestasi dan organisasi mahasiswa
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>KKT</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk pendaftaran KKT (Kuliah Kerja Terpadu)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>BIMBINGAN AKADEMIK</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk melakukan komunikasi dengan dosen pembimbing akademik
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>SKRIPSI</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk pengurusan skripsi secara online
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>WISUDA</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk pendaftaran wisuda.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 d-flex align-items-stretch">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <h2 class="lead"><b>BILLING</b></h2>
                                                    <p class="text-muted text-sm">
                                                        Aplikasi untuk generate billing pembayaran.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                            <a class="btn btn-app" href="dashboard.php">
                                <i class="fa fa-home"></i> Beranda
                            </a>
                            <a class="btn btn-app" href="biodata.php">
                                <i class="fas fa-user"></i> Biodata
                            </a>
                            <a class="btn btn-app" href="jadwal.php">
                                <i class="fas fa-clock"></i> Jadwal Kuliah
                            </a>
                            <a class="btn btn-app" href="krs.php">
                                <i class="fas fa-list"></i> KRS
                            </a>
                            <a class="btn btn-app" href="perencanaan.php">
                                <i class="fas fa-calendar-check"></i> Perencanaan Studi
                            </a>
                            <a class="btn btn-app" href="khs.php">
                                <i class="fas fa-list"></i> KHS
                            </a>
                            <a class="btn btn-app" href="transkrip.php">
                                <i class="fas fa-list-alt"></i> Transkrip
                            </a>
                            <a class="btn btn-app" href="billing.php">
                                <i class="fas fa-money-bill-alt"></i> Billing
                            </a>
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
