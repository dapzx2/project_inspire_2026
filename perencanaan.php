<?php
// Process form BEFORE any output (header include)
include 'config/database.php';
session_start();

$nim = isset($_SESSION['nim']) ? mysqli_real_escape_string($conn, $_SESSION['nim']) : '';
$message = '';
$message_type = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($nim)) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && isset($_POST['kode_mk'])) {
            $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
            $check = mysqli_query($conn, "SELECT * FROM perencanaan_studi WHERE nim = '$nim' AND kode_mk = '$kode_mk'");
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO perencanaan_studi (nim, kode_mk) VALUES ('$nim', '$kode_mk')");
                $message = 'Mata kuliah berhasil ditambahkan ke perencanaan!';
                $message_type = 'success';
            } else {
                $message = 'Mata kuliah sudah ada dalam perencanaan!';
                $message_type = 'warning';
            }
        }
        
        // NEW: Add directly to KRS
        if ($_POST['action'] === 'add_krs' && isset($_POST['kode_mk'])) {
            $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
            
            // Check if already in KRS
            $check_krs = mysqli_query($conn, "SELECT * FROM krs WHERE nim = '$nim' AND kode_mk = '$kode_mk'");
            if (mysqli_num_rows($check_krs) == 0) {
                // Get course info from transkrip or mata_kuliah
                $query_mk = "SELECT t.nama_mk, t.sks, m.jenis 
                             FROM transkrip t 
                             LEFT JOIN mata_kuliah m ON t.kode_mk = m.kode_mk 
                             WHERE t.kode_mk = '$kode_mk' AND t.nim = '$nim' 
                             LIMIT 1";
                $result_mk = mysqli_query($conn, $query_mk);
                
                if ($result_mk && $mk_data = mysqli_fetch_assoc($result_mk)) {
                    $nama_mk = mysqli_real_escape_string($conn, strtoupper($mk_data['nama_mk']));
                    $sks = $mk_data['sks'];
                    $jenis = ucfirst($mk_data['jenis'] ?? 'Wajib');
                    
                    // Default values for new KRS entry
                    $kelas = 'A';
                    $dosen1 = 'Dosen Pengampu';
                    $hari = 'Senin';
                    $jam_mulai = '08:00';
                    $jam_selesai = '10:30';
                    
                    $insert_krs = "INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, hari, jam_mulai, jam_selesai) 
                                   VALUES ('$nim', '$kode_mk', '$nama_mk', $sks, '$jenis', '$kelas', '$dosen1', '$hari', '$jam_mulai', '$jam_selesai')";
                    
                    if (mysqli_query($conn, $insert_krs)) {
                        $message = 'Mata kuliah berhasil ditambahkan ke KRS! <a href="krs.php" class="alert-link">Lihat KRS</a>';
                        $message_type = 'success';
                    } else {
                        $message = 'Gagal menambahkan ke KRS: ' . mysqli_error($conn);
                        $message_type = 'danger';
                    }
                } else {
                    $message = 'Data mata kuliah tidak ditemukan!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Mata kuliah sudah ada di KRS!';
                $message_type = 'warning';
            }
        }
        
        if ($_POST['action'] === 'delete' && isset($_POST['kode_mk'])) {
            $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
            mysqli_query($conn, "DELETE FROM perencanaan_studi WHERE nim = '$nim' AND kode_mk = '$kode_mk'");
            $message = 'Mata kuliah berhasil dihapus dari perencanaan!';
            $message_type = 'info';
        }
        
        // Transfer all planned courses to KRS
        if ($_POST['action'] === 'transfer_all_krs') {
            // Get all planned courses
            $query_plans = "SELECT p.kode_mk, m.nama_mk, m.sks, m.jenis 
                           FROM perencanaan_studi p 
                           JOIN mata_kuliah m ON p.kode_mk = m.kode_mk 
                           WHERE p.nim = '$nim'";
            $result_plans = mysqli_query($conn, $query_plans);
            
            $success_count = 0;
            $skip_count = 0;
            
            if ($result_plans && mysqli_num_rows($result_plans) > 0) {
                while ($plan = mysqli_fetch_assoc($result_plans)) {
                    // Check if already in KRS
                    $check_krs = mysqli_query($conn, "SELECT * FROM krs WHERE nim = '$nim' AND kode_mk = '{$plan['kode_mk']}'");
                    
                    if (mysqli_num_rows($check_krs) == 0) {
                        $nama_mk = mysqli_real_escape_string($conn, strtoupper($plan['nama_mk']));
                        $sks = $plan['sks'];
                        $jenis = ucfirst($plan['jenis'] ?? 'Wajib');
                        
                        // Default values
                        $kelas = 'A';
                        $dosen1 = 'Dosen Pengampu';
                        $hari = 'Senin';
                        $jam_mulai = '08:00';
                        $jam_selesai = '10:30';
                        
                        $insert_krs = "INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, hari, jam_mulai, jam_selesai) 
                                       VALUES ('$nim', '{$plan['kode_mk']}', '$nama_mk', $sks, '$jenis', '$kelas', '$dosen1', '$hari', '$jam_mulai', '$jam_selesai')";
                        
                        if (mysqli_query($conn, $insert_krs)) {
                            $success_count++;
                        }
                    } else {
                        $skip_count++;
                    }
                }
                
                if ($success_count > 0) {
                    $message = "$success_count mata kuliah berhasil ditransfer ke KRS!";
                    if ($skip_count > 0) {
                        $message .= " ($skip_count sudah ada di KRS)";
                    }
                    $message .= ' <a href="krs.php" class="alert-link">Lihat KRS</a>';
                    $message_type = 'success';
                } else {
                    $message = 'Semua mata kuliah sudah ada di KRS!';
                    $message_type = 'warning';
                }
            } else {
                $message = 'Tidak ada mata kuliah yang direncanakan!';
                $message_type = 'warning';
            }
        }
    }
    
    // Only redirect for regular (non-AJAX) requests
    // AJAX requests will continue to render the page for partial updates
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isFetch = isset($_SERVER['HTTP_SEC_FETCH_MODE']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false && isset($_SERVER['HTTP_ORIGIN']));
    
    if (!$isAjax && !isset($_SERVER['HTTP_ORIGIN'])) {
        // Regular form submission - redirect to prevent resubmission dialog
        header('Location: perencanaan.php');
        exit;
    }
    // For fetch/AJAX requests, continue to render the page
}

// Include header AFTER form processing (so redirect works)
include 'layout/header.php';

// ============================================
// GET USER'S CURRENT SEMESTER
// ============================================
$current_semester = 8; // default
$query_user_semester = "SELECT semester FROM users WHERE nim = '$nim'";
$result_user_semester = @mysqli_query($conn, $query_user_semester);
if ($result_user_semester && $row_sem = mysqli_fetch_assoc($result_user_semester)) {
    $current_semester = (int)$row_sem['semester'];
}

// ============================================
// DETECT IF IN KRS FILING PERIOD
// ============================================
// Check if user has KRS entries for current semester = in KRS period
$is_in_krs_period = false;
$query_check_krs = "SELECT COUNT(*) as total FROM krs WHERE nim = '$nim'";
$result_check_krs = @mysqli_query($conn, $query_check_krs);
if ($result_check_krs && $row_krs = mysqli_fetch_assoc($result_check_krs)) {
    $is_in_krs_period = ($row_krs['total'] > 0);
}

// Determine which semester to plan for
if ($is_in_krs_period) {
    // Has KRS entries: KRS period is OVER, plan for NEXT semester
    $planning_semester = $current_semester + 1;
} else {
    // No KRS entries: currently in KRS filing period, show CURRENT semester
    $planning_semester = $current_semester;
}

// Determine if planning semester is ganjil (odd) or genap (even)
$is_semester_ganjil = ($planning_semester % 2 == 1);
$semester_type_label = $is_semester_ganjil ? 'Ganjil' : 'Genap';
$semester_type_icon = $is_semester_ganjil ? 'fa-calendar-alt' : 'fa-calendar-check';
$semester_type_color = $is_semester_ganjil ? 'primary' : 'success';

// ============================================
// FETCH TRANSKRIP DATA DENGAN STATUS LULUS/TIDAK LULUS
// ============================================

// Array untuk menyimpan data transkrip berdasarkan semester
$transkrip_ganjil = []; // Semester 1, 3, 5, 7
$transkrip_genap = [];  // Semester 2, 4, 6, 8

// Matakuliah yang tidak lulus (D atau E) - untuk rekomendasi
$mk_tidak_lulus = [];

// Query transkrip mahasiswa
$query_transkrip = "SELECT t.*, m.nama_mk as mk_nama 
                    FROM transkrip t 
                    LEFT JOIN mata_kuliah m ON t.kode_mk = m.kode_mk
                    WHERE t.nim = '$nim' 
                    ORDER BY t.semester ASC, t.kode_mk ASC";
$result_transkrip = @mysqli_query($conn, $query_transkrip);

if ($result_transkrip) {
    while ($row = mysqli_fetch_assoc($result_transkrip)) {
        // Gunakan nama_mk dari transkrip jika ada, kalau tidak dari mata_kuliah
        $row['display_nama'] = !empty($row['nama_mk']) ? $row['nama_mk'] : $row['mk_nama'];
        
        // Tentukan status lulus (C ke atas = lulus)
        $row['is_lulus'] = !in_array($row['nilai_huruf'], ['D', 'E']);
        
        // Pisahkan berdasarkan semester ganjil/genap
        if ($row['semester'] % 2 == 1) {
            $transkrip_ganjil[] = $row;
        } else {
            $transkrip_genap[] = $row;
        }
        
        // Jika tidak lulus, masukkan ke rekomendasi
        if (!$row['is_lulus']) {
            $mk_tidak_lulus[] = $row;
        }
    }
}

// ============================================
// FETCH DATA UNTUK PERENCANAAN (existing logic)
// ============================================

// Fetch available mata kuliah
$mata_kuliah_list = [];
$query_mk = "SELECT * FROM mata_kuliah ORDER BY semester ASC, kode_mk ASC";
$result_mk = @mysqli_query($conn, $query_mk);
if ($result_mk) {
    while ($row = mysqli_fetch_assoc($result_mk)) {
        $mata_kuliah_list[] = $row;
    }
}

// Fetch user's planned courses
$perencanaan_list = [];
$total_sks = 0;
$mk_wajib = 0;
$mk_pilihan = 0;

$query_plan = "SELECT p.*, m.nama_mk, m.sks, m.jenis, m.semester as mk_semester
               FROM perencanaan_studi p 
               JOIN mata_kuliah m ON p.kode_mk = m.kode_mk 
               WHERE p.nim = '$nim' 
               ORDER BY m.semester ASC, m.kode_mk ASC";
$result_plan = @mysqli_query($conn, $query_plan);
if ($result_plan) {
    while ($row = mysqli_fetch_assoc($result_plan)) {
        $perencanaan_list[] = $row;
        $total_sks += $row['sks'];
        if ($row['jenis'] === 'wajib') {
            $mk_wajib++;
        } else {
            $mk_pilihan++;
        }
    }
}

// Get already planned kode_mk for filtering dropdown
$planned_codes = array_column($perencanaan_list, 'kode_mk');

// ============================================
// FETCH MK YANG BELUM DIKONTRAK (GANJIL & GENAP)
// ============================================

// Get all kode_mk that have been contracted (in transkrip)
$contracted_codes = [];
$query_contracted = "SELECT DISTINCT kode_mk FROM transkrip WHERE nim = '$nim'";
$result_contracted = @mysqli_query($conn, $query_contracted);
if ($result_contracted) {
    while ($row = mysqli_fetch_assoc($result_contracted)) {
        $contracted_codes[] = $row['kode_mk'];
    }
}

// Get MK that haven't been contracted
$mk_belum_kontrak_ganjil = [];
$mk_belum_kontrak_genap = [];

foreach ($mata_kuliah_list as $mk) {
    if (!in_array($mk['kode_mk'], $contracted_codes)) {
        if ($mk['semester'] % 2 == 1) {
            $mk_belum_kontrak_ganjil[] = $mk;
        } else {
            $mk_belum_kontrak_genap[] = $mk;
        }
    }
}

// ============================================
// SORTING LOGIC: SEMESTER (ASC) -> JENIS (WAJIB > PILIHAN)
// ============================================
$sort_function = function($a, $b) {
    // 1. Sort by Jenis (Wajib before Pilihan)
    // Normalize to lowercase for comparison
    $jenis_a = strtolower($a['jenis'] ?? 'wajib');
    $jenis_b = strtolower($b['jenis'] ?? 'wajib');
    
    if ($jenis_a != $jenis_b) {
        // 'wajib' should come before 'pilihan'
        return ($jenis_a === 'wajib') ? -1 : 1;
    }

    // 2. Sort by Semester ASC
    if ($a['semester'] != $b['semester']) {
        return $a['semester'] - $b['semester'];
    }
    
    return 0; // Equal
};
?>

<!-- Responsive Styles for Mobile -->
<style>
/* Mobile responsive styles */
@media (max-width: 576px) {
    /* Hide breadcrumb on small screens */
    .breadcrumb {
        display: none !important;
    }
    
    /* Smaller title */
    .content-header h1 {
        font-size: 1.25rem;
    }
    
    /* Card title smaller */
    .card-header .card-title {
        font-size: 0.85rem;
    }
    
    /* Hide badge in card tools on mobile */
    .card-header .card-tools {
        display: none;
    }
    
    /* ========================================== */
    /* TRANSFORM TABLE TO CARD LAYOUT ON MOBILE  */
    /* ========================================== */
    
    /* Disable table-responsive scroll */
    .table-responsive {
        overflow: visible !important;
    }
    
    /* Hide table headers */
    .table-mobile thead {
        display: none !important;
    }
    
    /* Hide tfoot on mobile */
    .table-mobile tfoot {
        display: none !important;
    }
    
    /* Reset table styles */
    .table-mobile {
        border: none !important;
    }
    
    .table-mobile tbody {
        display: block;
    }
    
    /* Make each row a card */
    .table-mobile tbody tr {
        display: block !important;
        background: #fff !important;
        border: 1px solid #ddd !important;
        border-radius: 10px !important;
        margin-bottom: 12px !important;
        padding: 12px 15px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    
    .table-mobile tbody tr.table-danger {
        background: #f8d7da !important;
        border: 2px solid #dc3545 !important;
        border-left: 5px solid #dc3545 !important;
    }
    
    /* Stack cells vertically */
    .table-mobile tbody td {
        display: block !important;
        text-align: left !important;
        padding: 8px 0 !important;
        border: none !important;
        border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        font-size: 0.9rem;
        width: 100% !important;
        background: transparent !important;
    }
    
    .table-mobile tbody td:last-child {
        border-bottom: none !important;
        text-align: center !important;
        padding-top: 12px !important;
    }
    
    /* Label styling */
    .table-mobile tbody td::before {
        content: attr(data-label);
        display: inline-block;
        font-weight: 700;
        color: #495057;
        font-size: 0.8rem;
        min-width: 80px;
        margin-right: 10px;
    }
    
    .table-mobile tbody td:last-child::before {
        display: none;
    }
    
    /* Button styling for mobile */
    .btn-add-mobile {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 8px 16px !important;
        font-size: 0.85rem !important;
        border-radius: 6px;
    }
    
    .btn-add-mobile .btn-text-mobile {
        display: inline !important;
        margin-left: 5px;
    }
    
    /* Badge adjustments */
    .table-mobile .badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    /* Card body padding reduction */
    .card-body {
        padding: 0.75rem !important;
    }
    
    /* Alert smaller */
    .alert {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>Perencanaan Studi</h1></div>
    </div>
    
    <div class="content">
        <div class="container-fluid">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- ============================================ -->
            <!-- SECTION: STATUS MATAKULIAH YANG SUDAH DIKONTRAK -->
            <!-- ============================================ -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-<?php echo $semester_type_color; ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas <?php echo $semester_type_icon; ?> mr-2"></i>
                                Status Matakuliah Semester <?php echo $semester_type_label; ?> yang Sudah Dikontrak
                            </h3>
                            <!-- Badge semester disembunyikan -->
                        </div>
                        <div class="card-body">
                            <?php 
                            // Select data based on current semester type
                            $transkrip_aktif = $is_semester_ganjil ? $transkrip_ganjil : $transkrip_genap;
                            $semester_list = $is_semester_ganjil ? '1, 3, 5, 7' : '2, 4, 6, 8';
                            
                            // Apply Sorting
                            usort($transkrip_aktif, $sort_function);
                            ?>
                            
                            <?php if (count($transkrip_aktif) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-mobile">
                                    <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                        <tr>
                                            <th width="50">Semester</th>
                                            <th width="90">Kode MK</th>
                                            <th>Nama Matakuliah</th>
                                            <th width="50">SKS</th>
                                            <th width="60">Nilai</th>
                                            <th width="100">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transkrip_aktif as $mk): ?>
                                        <tr class="<?php echo !$mk['is_lulus'] ? 'table-danger' : ''; ?>">
                                            <td data-label="Semester" class="text-center"><?php echo $mk['semester']; ?></td>
                                            <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                            <td data-label="Nama MK"><?php echo htmlspecialchars($mk['display_nama']); ?></td>
                                            <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                            <td data-label="Nilai" class="text-center">
                                                <span class="badge badge-<?php echo $mk['is_lulus'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $mk['nilai_huruf']; ?>
                                                </span>
                                            </td>
                                            <td data-label="Status" class="text-center">
                                                <?php if ($mk['is_lulus']): ?>
                                                    <span class="text-success"><i class="fas fa-check-circle"></i> Lulus</span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Tidak Lulus</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-light text-center">
                                <i class="fas fa-info-circle mr-1"></i> Belum ada data matakuliah semester <?php echo strtolower($semester_type_label); ?> (<?php echo $semester_list; ?>)
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- SECTION: REKOMENDASI KONTRAK (MK TIDAK LULUS) -->
            <!-- ============================================ -->
            <?php if (count($mk_tidak_lulus) > 0): 
                // Get current semester from user data
                $query_semester = "SELECT semester FROM users WHERE nim = '$nim'";
                $result_semester = @mysqli_query($conn, $query_semester);
                $current_semester = 8; // default
                if ($result_semester && $row_sem = mysqli_fetch_assoc($result_semester)) {
                    $current_semester = $row_sem['semester'];
                }
                // Next semester is current + 1
                $next_semester = $current_semester + 1;
                $is_next_ganjil = ($next_semester % 2 == 1);
                
                // Filter mk_tidak_lulus based on next semester type
                // If next semester is ganjil, show ganjil MK. If next is genap, show genap MK.
                $rekomendasi_aktif = array_filter($mk_tidak_lulus, function($mk) use ($is_next_ganjil) {
                    $is_mk_ganjil = ($mk['semester'] % 2 == 1);
                    return $is_mk_ganjil == $is_next_ganjil;
                });
                
                $semester_label = $is_next_ganjil ? 'Ganjil' : 'Genap';
                $semester_icon = $is_next_ganjil ? 'fa-calendar-alt' : 'fa-calendar-check';
                $semester_color = $is_next_ganjil ? 'primary' : 'success';

                // Next semester for display purposes
                $next_semester = $planning_semester;
                
                // Apply Sorting
                usort($rekomendasi_aktif, $sort_function);
            ?>
            <div class="row" id="rekomendasi">
                <div class="col-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header bg-danger">
                            <h3 class="card-title text-white">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Rekomendasi Matakuliah untuk Dikontrak Ulang (Semester <?php echo $semester_label; ?>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Perhatian!</strong> Anda saat ini di semester <strong><?php echo $current_semester; ?></strong>. 
                                Berikut adalah matakuliah dengan nilai D/E yang dapat dikontrak ulang di semester <strong><?php echo $planning_semester; ?> (<?php echo $semester_label; ?>)</strong>.
                            </div>
                            
                            <?php if (count($rekomendasi_aktif) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-mobile">
                                    <thead class="bg-<?php echo $semester_color; ?> text-white">
                                        <tr>
                                            <th width="70">Semester</th>
                                            <th width="100">Kode MK</th>
                                            <th>Nama Matakuliah</th>
                                            <th width="60">SKS</th>
                                            <th width="70">Nilai</th>
                                            <th width="150">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rekomendasi_aktif as $mk): ?>
                                        <tr class="table-danger">
                                            <td data-label="Semester" class="text-center"><?php echo $mk['semester']; ?></td>
                                            <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                            <td data-label="Nama MK"><?php echo htmlspecialchars($mk['display_nama']); ?></td>
                                            <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                            <td data-label="Nilai" class="text-center">
                                                <span class="badge badge-danger"><?php echo $mk['nilai_huruf']; ?></span>
                                            </td>
                                            <td data-label="" class="text-center">
                                                <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="source" value="rekomendasi">
                                                    <input type="hidden" name="kode_mk" value="<?php echo $mk['kode_mk']; ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm btn-add-mobile">
                                                        <i class="fas fa-plus"></i> <span class="btn-text-mobile">Tambah ke Rencana</span>
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <span class="badge badge-info"><i class="fas fa-check"></i> Sudah Direncanakan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="2" class="text-right"><strong>Total SKS:</strong></td>
                                            <td class="text-center"><strong><?php echo array_sum(array_column($rekomendasi_aktif, 'sks')); ?></strong></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Bagus!</strong> Tidak ada matakuliah semester <?php echo strtolower($semester_label); ?> yang perlu diulang.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================ -->
            <!-- SECTION: MK BELUM DIKONTRAK -->
            <!-- ============================================ -->
            <?php 
            // Select MK belum kontrak based on current semester type
            $mk_belum_kontrak_aktif = $is_semester_ganjil ? $mk_belum_kontrak_ganjil : $mk_belum_kontrak_genap;
            
            // Apply Sorting
            usort($mk_belum_kontrak_aktif, $sort_function);
            ?>
            <div class="row" id="mk-belum-kontrak">
                <div class="col-12">
                    <div class="card card-outline card-<?php echo $semester_type_color; ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-book-open mr-2"></i>
                                Matakuliah Semester <?php echo $semester_type_label; ?> yang Belum Dikontrak
                            </h3>
                            <!-- Badge semester disembunyikan -->
                        </div>
                        <div class="card-body">
                            <?php if (count($mk_belum_kontrak_aktif) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-mobile">
                                    <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                        <tr>
                                            <th width="70">Semester</th>
                                            <th width="90">Kode MK</th>
                                            <th>Nama Matakuliah</th>
                                            <th width="50">SKS</th>
                                            <th width="70">Jenis</th>
                                            <th width="150">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mk_belum_kontrak_aktif as $mk): ?>
                                        <tr>
                                            <td data-label="Semester" class="text-center"><?php echo $mk['semester']; ?></td>
                                            <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                            <td data-label="Nama MK"><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                                            <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                            <td data-label="Jenis" class="text-center">
                                                <span class="badge badge-<?php echo $mk['jenis'] === 'wajib' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst($mk['jenis']); ?>
                                                </span>
                                            </td>
                                            <td data-label="" class="text-center">
                                                <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="source" value="mk-belum-kontrak">
                                                    <input type="hidden" name="kode_mk" value="<?php echo $mk['kode_mk']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm btn-add-mobile">
                                                    <i class="fas fa-plus"></i> <span class="btn-text-mobile">Tambah ke Rencana</span>
                                                </button>
                                                </form>
                                                <?php else: ?>
                                                <span class="badge badge-info"><i class="fas fa-check"></i> Sudah Direncanakan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="2" class="text-right"><strong>Total SKS:</strong></td>
                                            <td class="text-center"><strong><?php echo array_sum(array_column($mk_belum_kontrak_aktif, 'sks')); ?></strong></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle mr-1"></i> Semua MK semester <?php echo strtolower($semester_type_label); ?> sudah dikontrak!
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- SECTION: PERENCANAAN STUDI (existing) -->
            <!-- ============================================ -->
            <div class="row" id="rencana-studi">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Perencanaan Studi Semester 8
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-3">Rencana Mata Kuliah Semester 8</h5>
                                    <table class="table table-bordered table-striped mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="50">No</th>
                                                        <th width="100">Kode MK</th>
                                                        <th>Nama Mata Kuliah</th>
                                                        <th width="60">SKS</th>
                                                        <th width="80">Jenis</th>
                                                        <th width="80">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (count($perencanaan_list) > 0): ?>
                                                        <?php $no = 1; foreach ($perencanaan_list as $plan): ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo $no++; ?></td>
                                                            <td><?php echo htmlspecialchars($plan['kode_mk']); ?></td>
                                                            <td><?php echo htmlspecialchars($plan['nama_mk']); ?></td>
                                                            <td class="text-center"><?php echo $plan['sks']; ?></td>
                                                            <td class="text-center">
                                                                <span class="badge badge-<?php echo $plan['jenis'] === 'wajib' ? 'primary' : 'secondary'; ?>">
                                                                    <?php echo ucfirst($plan['jenis']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <form method="POST" style="display:inline;">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="source" value="rencana-studi">
                                                                    <input type="hidden" name="kode_mk" value="<?php echo $plan['kode_mk']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus mata kuliah ini?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                                Belum ada mata kuliah yang direncanakan
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="bg-light">
                                                        <td colspan="3" class="text-right"><strong>Total SKS:</strong></td>
                                                        <td class="text-center"><strong><?php echo $total_sks; ?></strong></td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                </div>
                                <div class="col-md-4">
                                    <h5 class="mb-3">Ringkasan</h5>
                                            <table class="table table-bordered mb-0">
                                                <tr>
                                                    <td><strong>Total SKS Direncanakan</strong></td>
                                                    <td class="text-center" width="60"><strong><?php echo $total_sks; ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Mata Kuliah Wajib</td>
                                                    <td class="text-center"><?php echo $mk_wajib; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Mata Kuliah Pilihan</td>
                                                    <td class="text-center"><?php echo $mk_pilihan; ?></td>
                                                </tr>
                                                <tr class="<?php echo count($mk_tidak_lulus) > 0 ? 'text-danger' : ''; ?>">
                                                    <td><strong>MK Perlu Diulang</strong></td>
                                                    <td class="text-center"><strong><?php echo count($mk_tidak_lulus); ?></strong></td>
                                                </tr>
                                            </table>
                                            
                                            <?php if ($total_sks > 24): ?>
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                SKS melebihi batas maksimal (24 SKS)!
                                            </div>
                                            <?php elseif ($total_sks > 0): ?>
                                            <div class="alert alert-success mt-3 mb-3">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Perencanaan dalam batas wajar.
                                            </div>
                                            
                                            <!-- Tombol Transfer ke KRS -->
                                            <?php 
                                            // Check if in KRS filling period (for now, set to false - not in period)
                                            $dalam_periode_krs = false; // Set true when KRS period is open
                                            ?>
                                            
                                            <?php if ($dalam_periode_krs): ?>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="transfer_all_krs">
                                                <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Tambahkan semua mata kuliah yang direncanakan ke KRS?')">
                                                    <i class="fas fa-paper-plane mr-2"></i>
                                                    Tambahkan Semua ke KRS
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-secondary btn-block" disabled>
                                                <i class="fas fa-lock mr-2"></i>
                                                Tambahkan Semua ke KRS
                                            </button>
                                            <small class="text-muted d-block mt-2 text-center">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Tidak dalam periode pengisian KRS
                                            </small>
                                            <?php endif; ?>
                                            <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // AJAX-based form submission to prevent page scroll/reload
    document.addEventListener("DOMContentLoaded", function() {
        attachFormListeners();
    });
    
    function attachFormListeners() {
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            // Remove existing listeners to prevent duplicates
            form.replaceWith(form.cloneNode(true));
        });
        
        // Re-select forms after cloning
        forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Save current scroll position BEFORE anything else
                var scrollY = window.scrollY || window.pageYOffset;
                
                var formData = new FormData(form);
                var submitBtn = form.querySelector('button[type="submit"]');
                var originalHtml = submitBtn ? submitBtn.innerHTML : '';
                
                // Show loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }
                
                fetch('perencanaan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Parse the response HTML
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    
                    // Update only the content section
                    var newContent = doc.querySelector('.content');
                    var oldContent = document.querySelector('.content');
                    
                    if (newContent && oldContent) {
                        oldContent.innerHTML = newContent.innerHTML;
                        
                        // Restore scroll position immediately
                        window.scrollTo(0, scrollY);
                        
                        // Also restore after a small delay to handle any layout shifts
                        requestAnimationFrame(function() {
                            window.scrollTo(0, scrollY);
                        });
                        
                        // Re-attach event listeners
                        attachFormListeners();
                    } else {
                        // Fallback
                        localStorage.setItem('scrollpos_perencanaan', scrollY);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }
                });
            });
        });
    }
    
    // Fallback scroll restore on page load
    window.addEventListener('load', function() {
        var scrollpos = localStorage.getItem('scrollpos_perencanaan');
        if (scrollpos) {
            window.scrollTo(0, parseInt(scrollpos));
            localStorage.removeItem('scrollpos_perencanaan');
        }
    });
</script>

<?php include 'layout/footer.php'; ?>
