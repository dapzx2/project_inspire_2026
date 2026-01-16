<?php
// Form processing BEFORE any output (header include)
include 'config/database.php';

// Session already started in header.php, get nim for processing
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$nim = $_SESSION['nim'] ?? '';
$message = '';
$message_type = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($nim)) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && isset($_POST['kode_mk'])) {
            $kode_mk = $_POST['kode_mk'];
            // cek duplikat dengan prepared statement
            $stmt = $conn->prepare("SELECT id FROM perencanaan_studi WHERE nim = ? AND kode_mk = ?");
            $stmt->bind_param("ss", $nim, $kode_mk);
            $stmt->execute();
            $check = $stmt->get_result();
            if ($check->num_rows == 0) {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO perencanaan_studi (nim, kode_mk) VALUES (?, ?)");
                $stmt->bind_param("ss", $nim, $kode_mk);
                $stmt->execute();
                $stmt->close();
                $message = 'Mata kuliah berhasil ditambahkan ke perencanaan!';
                $message_type = 'success';
            } else {
                $message = 'Mata kuliah sudah ada dalam perencanaan!';
                $message_type = 'warning';
            }
        }
        
        // Tambah langsung ke KRS
        if ($_POST['action'] === 'add_krs' && isset($_POST['kode_mk'])) {
            $kode_mk = $_POST['kode_mk'];
            
            // Cek apakah sudah ada di KRS
            $stmt = $conn->prepare("SELECT id FROM krs WHERE nim = ? AND kode_mk = ?");
            $stmt->bind_param("ss", $nim, $kode_mk);
            $stmt->execute();
            $check_krs = $stmt->get_result();
            
            if ($check_krs->num_rows == 0) {
                $stmt->close();
                
                // Ambil info MK dari transkrip atau mata_kuliah
                $stmt = $conn->prepare("SELECT t.nama_mk, t.sks, m.jenis 
                             FROM transkrip t 
                             LEFT JOIN mata_kuliah m ON t.kode_mk = m.kode_mk 
                             WHERE t.kode_mk = ? AND t.nim = ? 
                             LIMIT 1");
                $stmt->bind_param("ss", $kode_mk, $nim);
                $stmt->execute();
                $result_mk = $stmt->get_result();
                
                if ($result_mk && $mk_data = $result_mk->fetch_assoc()) {
                    $nama_mk = strtoupper($mk_data['nama_mk']);
                    $sks = (int)$mk_data['sks'];
                    $jenis = ucfirst($mk_data['jenis'] ?? 'Wajib');
                    
                    // Default values
                    $kelas = 'A';
                    $dosen1 = 'Dosen Pengampu';
                    $hari = 'Senin';
                    $jam_mulai = '08:00';
                    $jam_selesai = '10:30';
                    $stmt->close();
                    
                    $stmt = $conn->prepare("INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssissssss", $nim, $kode_mk, $nama_mk, $sks, $jenis, $kelas, $dosen1, $hari, $jam_mulai, $jam_selesai);
                    
                    if ($stmt->execute()) {
                        $message = 'Mata kuliah berhasil ditambahkan ke KRS! <a href="krs.php" class="alert-link">Lihat KRS</a>';
                        $message_type = 'success';
                    } else {
                        $message = 'Gagal menambahkan ke KRS. Silakan coba lagi.';
                        $message_type = 'danger';
                        error_log('KRS Insert Error: ' . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $message = 'Data mata kuliah tidak ditemukan!';
                    $message_type = 'danger';
                    $stmt->close();
                }
            } else {
                $message = 'Mata kuliah sudah ada di KRS!';
                $message_type = 'warning';
                $stmt->close();
            }
        }
        
        if ($_POST['action'] === 'delete' && isset($_POST['kode_mk'])) {
            $kode_mk = $_POST['kode_mk'];
            $stmt = $conn->prepare("DELETE FROM perencanaan_studi WHERE nim = ? AND kode_mk = ?");
            $stmt->bind_param("ss", $nim, $kode_mk);
            $stmt->execute();
            $stmt->close();
            $message = 'Mata kuliah berhasil dihapus dari perencanaan!';
            $message_type = 'info';
        }
        
        // Delete all planned courses
        if ($_POST['action'] === 'delete_all') {
            $stmt = $conn->prepare("DELETE FROM perencanaan_studi WHERE nim = ?");
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $deleted_count = $stmt->affected_rows;
            $stmt->close();
            $message = "$deleted_count mata kuliah berhasil dihapus dari perencanaan!";
            $message_type = 'info';
        }
        
        // Transfer semua perencanaan ke KRS
        if ($_POST['action'] === 'transfer_all_krs') {
            // Ambil semua perencanaan
            $stmt = $conn->prepare("SELECT p.kode_mk, m.nama_mk, m.sks, m.jenis 
                           FROM perencanaan_studi p 
                           JOIN mata_kuliah m ON p.kode_mk = m.kode_mk 
                           WHERE p.nim = ?");
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $result_plans = $stmt->get_result();
            
            $success_count = 0;
            $skip_count = 0;
            
            // Prepare statements untuk loop
            $stmt_check = $conn->prepare("SELECT id FROM krs WHERE nim = ? AND kode_mk = ?");
            $stmt_insert = $conn->prepare("INSERT INTO krs (nim, kode_mk, nama_mk, sks, jenis, kelas, dosen1, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Default values
            $kelas = 'A';
            $dosen1 = 'Dosen Pengampu';
            $hari = 'Senin';
            $jam_mulai = '08:00';
            $jam_selesai = '10:30';
            
            if ($result_plans && $result_plans->num_rows > 0) {
                while ($plan = $result_plans->fetch_assoc()) {
                    // Cek apakah sudah ada di KRS
                    $stmt_check->bind_param("ss", $nim, $plan['kode_mk']);
                    $stmt_check->execute();
                    $check_result = $stmt_check->get_result();
                    
                    if ($check_result->num_rows == 0) {
                        $nama_mk = strtoupper($plan['nama_mk']);
                        $sks = (int)$plan['sks'];
                        $jenis = ucfirst($plan['jenis'] ?? 'Wajib');
                        
                        $stmt_insert->bind_param("sssissssss", $nim, $plan['kode_mk'], $nama_mk, $sks, $jenis, $kelas, $dosen1, $hari, $jam_mulai, $jam_selesai);
                        
                        if ($stmt_insert->execute()) {
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
            $stmt_check->close();
            $stmt_insert->close();
            $stmt->close();
        }
    }
    
    // Store message in session for display after redirect
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_message_type'] = $message_type;
    }
    
    // Check if AJAX request - don't redirect, let JS handle the response
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        // AJAX request - continue to render the page, JS will handle the response
        // Don't redirect, just continue
    } else {
        // Regular form submission - redirect to prevent "Confirm Form Resubmission"
        header('Location: perencanaan.php#rencana-studi');
        exit;
    }
}

// Include header AFTER form processing (so redirect works)
include 'layout/header.php';

// ============================================
// GET USER'S CURRENT SEMESTER
// ============================================
$current_semester = 8; // default
$stmt_user_sem = $conn->prepare("SELECT semester FROM users WHERE nim = ?");
$stmt_user_sem->bind_param("s", $nim);
$stmt_user_sem->execute();
$result_user_semester = $stmt_user_sem->get_result();
if ($result_user_semester && $row_sem = mysqli_fetch_assoc($result_user_semester)) {
    $current_semester = (int)$row_sem['semester'];
}

// ============================================
// PLANNING FOR NEXT SEMESTER
// ============================================
// Always plan for next semester (current + 1)
// User wants to prepare for semester 8 while currently in semester 7
$planning_semester = $current_semester + 1;

// ============================================
// SEMESTER TYPE VARIABLES
// ============================================

// Current semester type (for "Status Matakuliah yang Sudah Dikontrak")
$is_current_ganjil = ($current_semester % 2 == 1);
$current_semester_label = $is_current_ganjil ? 'Ganjil' : 'Genap';
$current_semester_icon = $is_current_ganjil ? 'fa-calendar-alt' : 'fa-calendar-check';
$current_semester_color = $is_current_ganjil ? 'primary' : 'success';

// Planning semester type (for recommendations and planning next semester)
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
$stmt_transkrip = $conn->prepare("SELECT t.*, m.nama_mk as mk_nama 
                    FROM transkrip t 
                    LEFT JOIN mata_kuliah m ON t.kode_mk = m.kode_mk
                    WHERE t.nim = ? 
                    ORDER BY t.semester ASC, t.kode_mk ASC");
$stmt_transkrip->bind_param("s", $nim);
$stmt_transkrip->execute();
$result_transkrip = $stmt_transkrip->get_result();

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

// Filter MK tidak lulus berdasarkan tipe semester (untuk Ringkasan)
$mk_tidak_lulus_genap = array_filter($mk_tidak_lulus, function($mk) { return $mk['semester'] % 2 == 0; });
$mk_tidak_lulus_ganjil = array_filter($mk_tidak_lulus, function($mk) { return $mk['semester'] % 2 == 1; });

// ============================================
// FETCH DATA UNTUK PERENCANAAN (existing logic)
// ============================================

// Fetch available mata kuliah
$mata_kuliah_list = [];
$result_mk = $conn->query("SELECT * FROM mata_kuliah ORDER BY semester ASC, kode_mk ASC");
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

$stmt_plan = $conn->prepare("SELECT p.*, m.nama_mk, m.sks, m.jenis, m.kategori, m.semester as mk_semester
               FROM perencanaan_studi p 
               JOIN mata_kuliah m ON p.kode_mk = m.kode_mk 
               WHERE p.nim = ? 
               ORDER BY m.jenis DESC, m.semester ASC, m.kode_mk ASC");
$stmt_plan->bind_param("s", $nim);
$stmt_plan->execute();
$result_plan = $stmt_plan->get_result();
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
// FETCH IPK DAN SKS AWAL UNTUK SIMULASI
// ============================================
$ipk_awal = 0;
$sks_awal = 0;

$stmt_ipk = $conn->prepare("SELECT 
    CASE WHEN SUM(sks) > 0 THEN ROUND(SUM(sks * bobot) / SUM(sks), 2) ELSE 0 END as ipk,
    COALESCE(SUM(CASE WHEN nilai_huruf NOT IN ('D', 'E') THEN sks ELSE 0 END), 0) as sks_lulus
    FROM transkrip WHERE nim = ?");
$stmt_ipk->bind_param("s", $nim);
$stmt_ipk->execute();
$result_ipk = $stmt_ipk->get_result();
if ($row_ipk = $result_ipk->fetch_assoc()) {
    $ipk_awal = (float) $row_ipk['ipk'];
    $sks_awal = (int) $row_ipk['sks_lulus'];
}
$stmt_ipk->close();

// ============================================
// FETCH MK YANG BELUM DIKONTRAK (GANJIL & GENAP)
// ============================================

// Get all kode_mk that have been contracted (in transkrip)
$contracted_codes = [];
$stmt_contracted = $conn->prepare("SELECT DISTINCT kode_mk FROM transkrip WHERE nim = ?");
$stmt_contracted->bind_param("s", $nim);
$stmt_contracted->execute();
$result_contracted = $stmt_contracted->get_result();
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
            <?php 
            // Read flash message from session
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                $message_type = $_SESSION['flash_message_type'];
                // Clear flash message after reading
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_message_type']);
            }
            ?>
            <?php if ($message): ?>
            <!-- Toast Notification (Fixed Position) -->
            <div id="toast-notification" class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" 
                 style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'info-circle')); ?> mr-2"></i>
                <?php echo $message; ?>
            </div>
            <script>
                // Auto-hide toast after 5 seconds
                setTimeout(function() {
                    var toast = document.getElementById('toast-notification');
                    if (toast) {
                        toast.classList.remove('show');
                        setTimeout(function() { toast.remove(); }, 150);
                    }
                }, 5000);
            </script>
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
                            // Select data based on PLANNING semester type (semester 8 = Genap)
                            $transkrip_aktif = $is_semester_ganjil ? $transkrip_ganjil : $transkrip_genap;
                            $semester_list = $is_semester_ganjil ? '1, 3, 5, 7' : '2, 4, 6, 8';
                            
                            // Apply Sorting
                            usort($transkrip_aktif, $sort_function);
                            ?>
                            
                            <?php if (count($transkrip_aktif) > 0): 
                                // Group by semester
                                $grouped_transkrip = [];
                                foreach ($transkrip_aktif as $mk) {
                                    $sem = $mk['semester'];
                                    if (!isset($grouped_transkrip[$sem])) {
                                        $grouped_transkrip[$sem] = [];
                                    }
                                    $grouped_transkrip[$sem][] = $mk;
                                }
                                // Sort by semester
                                ksort($grouped_transkrip);
                            ?>
                                <?php foreach ($grouped_transkrip as $semester_num => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-<?php echo $semester_type_color; ?>"><strong>Semester <?php echo $semester_num; ?></strong></h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered table-striped table-mobile">
                                        <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                            <tr>
                                                <th width="90">Kode MK</th>
                                                <th>Nama Matakuliah</th>
                                                <th width="50">SKS</th>
                                                <th width="60">Nilai</th>
                                                <th width="100">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr class="<?php echo !$mk['is_lulus'] ? 'table-danger' : ''; ?>">
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
                                <?php endforeach; ?>
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
                // Use the planning_semester that was already calculated above
                // planning_semester is already set to either current_semester or current_semester + 1
                $is_planning_ganjil = ($planning_semester % 2 == 1);
                
                // Filter mk_tidak_lulus based on planning semester type
                // If planning semester is ganjil, show ganjil MK. If planning is genap, show genap MK.
                $rekomendasi_aktif = array_filter($mk_tidak_lulus, function($mk) use ($is_planning_ganjil) {
                    $is_mk_ganjil = ($mk['semester'] % 2 == 1);
                    return $is_mk_ganjil == $is_planning_ganjil;
                });
                
                $semester_label = $is_planning_ganjil ? 'Ganjil' : 'Genap';
                $semester_icon = $is_planning_ganjil ? 'fa-calendar-alt' : 'fa-calendar-check';
                $semester_color = $is_planning_ganjil ? 'primary' : 'success';
                
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
                            
                            <?php if (count($rekomendasi_aktif) > 0): 
                                // Group by semester
                                $grouped_rekomendasi = [];
                                foreach ($rekomendasi_aktif as $mk) {
                                    $sem = $mk['semester'];
                                    if (!isset($grouped_rekomendasi[$sem])) {
                                        $grouped_rekomendasi[$sem] = [];
                                    }
                                    $grouped_rekomendasi[$sem][] = $mk;
                                }
                                ksort($grouped_rekomendasi);
                            ?>
                                <?php foreach ($grouped_rekomendasi as $semester_num => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-danger"><strong>Semester <?php echo $semester_num; ?></strong></h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped table-mobile">
                                        <thead class="bg-<?php echo $semester_color; ?> text-white">
                                            <tr>
                                                <th width="100">Kode MK</th>
                                                <th>Nama Matakuliah</th>
                                                <th width="60">SKS</th>
                                                <th width="70">Nilai</th>
                                                <th width="150">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr class="table-danger">
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
                                    </table>
                                </div>
                                <?php endforeach; ?>
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
                            <?php if (count($mk_belum_kontrak_aktif) > 0): 
                                // Separate MK Wajib and MK Pilihan
                                $mk_wajib_list = array_filter($mk_belum_kontrak_aktif, function($mk) { return $mk['jenis'] === 'wajib'; });
                                $mk_pilihan_list = array_filter($mk_belum_kontrak_aktif, function($mk) { return $mk['jenis'] === 'pilihan'; });
                                
                                // Group MK Wajib by semester
                                $grouped_wajib = [];
                                foreach ($mk_wajib_list as $mk) {
                                    $sem = $mk['semester'];
                                    if (!isset($grouped_wajib[$sem])) {
                                        $grouped_wajib[$sem] = [];
                                    }
                                    $grouped_wajib[$sem][] = $mk;
                                }
                                ksort($grouped_wajib);
                                
                                // Group MK Pilihan by kategori (MKP)
                                $grouped_pilihan = [];
                                foreach ($mk_pilihan_list as $mk) {
                                    $kategori = $mk['kategori'] ?? 'Lainnya';
                                    if (!isset($grouped_pilihan[$kategori])) {
                                        $grouped_pilihan[$kategori] = [];
                                    }
                                    $grouped_pilihan[$kategori][] = $mk;
                                }
                                ksort($grouped_pilihan);
                            ?>
                                <!-- MK WAJIB -->
                                <?php if (count($mk_wajib_list) > 0): ?>
                                <h5 class="mb-3"><i class="fas fa-book mr-2"></i>Matakuliah Wajib</h5>
                                <?php foreach ($grouped_wajib as $semester_num => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-<?php echo $semester_type_color; ?>"><strong>Semester <?php echo $semester_num; ?></strong></h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered table-striped table-mobile">
                                        <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                            <tr>
                                                <th width="90">Kode MK</th>
                                                <th>Nama Matakuliah</th>
                                                <th width="50">SKS</th>
                                                <th width="150">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr>
                                                <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                <td data-label="Nama MK"><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                                                <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td data-label="" class="text-center">
                                                    <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="source" value="mk-belum-kontrak">
                                                        <input type="hidden" name="kode_mk" value="<?php echo $mk['kode_mk']; ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm btn-add-mobile">
                                                            <i class="fas fa-plus"></i> <span class="btn-text-mobile">Tambah</span>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <span class="badge badge-info"><i class="fas fa-check"></i> Sudah</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- MK PILIHAN (PER KATEGORI MKP) -->
                                <?php if (count($mk_pilihan_list) > 0): ?>
                                <hr class="my-4">
                                <h5 class="mb-3"><i class="fas fa-cubes mr-2"></i>Paket Matakuliah Pilihan (MKP)</h5>
                                <?php foreach ($grouped_pilihan as $kategori => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-info"><strong><i class="fas fa-layer-group mr-1"></i><?php echo htmlspecialchars($kategori); ?></strong></h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-bordered table-striped table-mobile">
                                        <thead class="bg-info text-white">
                                            <tr>
                                                <th width="90">Kode MK</th>
                                                <th>Nama Matakuliah</th>
                                                <th width="50">SKS</th>
                                                <th width="60">Sem</th>
                                                <th width="150">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr>
                                                <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                <td data-label="Nama MK"><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                                                <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td data-label="Semester" class="text-center"><?php echo $mk['semester']; ?></td>
                                                <td data-label="" class="text-center">
                                                    <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="source" value="mk-belum-kontrak">
                                                        <input type="hidden" name="kode_mk" value="<?php echo $mk['kode_mk']; ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm btn-add-mobile">
                                                            <i class="fas fa-plus"></i> <span class="btn-text-mobile">Tambah</span>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <span class="badge badge-info"><i class="fas fa-check"></i> Sudah</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
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
                                Perencanaan Studi Semester <?php echo $planning_semester; ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-3">Rencana Mata Kuliah Semester <?php echo $planning_semester; ?></h5>
                                    <table class="table table-bordered table-striped mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="50">No</th>
                                                        <th width="100">Kode MK</th>
                                                        <th>Nama Mata Kuliah</th>
                                                        <th width="60">SKS</th>
                                                        <th width="80">Jenis</th>
                                                        <th width="120">Prediksi Nilai</th>
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
                                                                <?php if ($plan['jenis'] === 'wajib'): ?>
                                                                <span class="badge badge-primary">Wajib</span>
                                                                <?php else: ?>
                                                                <span class="badge badge-info" title="<?php echo htmlspecialchars($plan['kategori'] ?? 'Pilihan'); ?>">
                                                                    <?php echo htmlspecialchars($plan['kategori'] ?? 'Pilihan'); ?>
                                                                </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <select class="form-control form-control-sm prediksi-nilai" 
                                                                        data-sks="<?php echo (int) $plan['sks']; ?>" 
                                                                        onchange="hitungSimulasi()">
                                                                    <option value="">-</option>
                                                                    <option value="4.00">A</option>
                                                                    <option value="3.50">B+</option>
                                                                    <option value="3.00">B</option>
                                                                    <option value="2.50">C+</option>
                                                                    <option value="2.00">C</option>
                                                                    <option value="1.00">D</option>
                                                                    <option value="0.00">E</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-center">
                                                                <form method="POST" style="display:inline;">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="source" value="rencana-studi">
                                                                    <input type="hidden" name="kode_mk" value="<?php echo $plan['kode_mk']; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Hapus mata kuliah ini?">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-4">
                                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                                Belum ada mata kuliah yang direncanakan
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="bg-light">
                                                        <td colspan="4" class="text-center">
                                                            <strong>Total SKS Yang Di Rencanakan: <?php echo $total_sks; ?></strong>
                                                        </td>
                                                        <td colspan="3" class="text-center">
                                                            <?php if (count($perencanaan_list) > 0): ?>
                                                            <form method="POST" style="display:inline; width:100%;">
                                                                <input type="hidden" name="action" value="delete_all">
                                                                <input type="hidden" name="source" value="rencana-studi">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block" data-confirm="Hapus SEMUA mata kuliah yang direncanakan?">
                                                                    <i class="fas fa-trash-alt mr-1"></i>Hapus Semua
                                                                </button>
                                                            </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                </div>
                                <div class="col-md-4">
                                    <h5 class="mb-3">Ringkasan</h5>
                                            <table class="table table-bordered mb-0">
                                                <tr>
                                                    <td>Mata Kuliah Wajib</td>
                                                    <td class="text-center" width="60"><?php echo $mk_wajib; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Mata Kuliah Pilihan</td>
                                                    <td class="text-center"><?php echo $mk_pilihan; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total MK Direncanakan</strong></td>
                                                    <td class="text-center"><strong><?php echo $mk_wajib + $mk_pilihan; ?></strong></td>
                                                </tr>
                                            </table>
                                            
                                            <!-- SIMULASI IPK -->
                                            <div class="card card-outline card-primary mt-3 mb-0">
                                                <div class="card-header bg-primary py-2">
                                                    <h6 class="card-title text-white mb-0">
                                                        <i class="fas fa-calculator mr-2"></i>Simulasi IPK
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr>
                                                            <td class="py-1">IPK Saat Ini</td>
                                                            <td class="text-right py-1"><strong><?php echo number_format($ipk_awal, 2); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-1">SKS Lulus</td>
                                                            <td class="text-right py-1"><strong><?php echo $sks_awal; ?></strong></td>
                                                        </tr>
                                                        <tr class="border-top">
                                                            <td class="py-1"><strong>Estimasi IPS</strong></td>
                                                            <td class="text-right py-1"><strong id="estimasi-ips" class="text-info">0.00</strong></td>
                                                        </tr>
                                                        <tr class="bg-light">
                                                            <td class="py-2"><strong>Estimasi IPK Akhir</strong></td>
                                                            <td class="text-right py-2"><strong id="estimasi-ipk" class="text-primary" style="font-size: 1.2em;">0.00</strong></td>
                                                        </tr>
                                                    </table>
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-info-circle mr-1"></i>Pilih prediksi nilai untuk melihat simulasi
                                                    </small>
                                                </div>
                                            </div>
                                            
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
                                                <button type="submit" class="btn btn-success btn-block" data-confirm="Tambahkan semua mata kuliah yang direncanakan ke KRS?">
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
                
                // Save current scroll position to sessionStorage
                var scrollY = window.scrollY || window.pageYOffset;
                sessionStorage.setItem('perencanaan_scroll', scrollY);
                
                // Save prediksi nilai dropdown values before update
                var prediksiValues = {};
                document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
                    var row = select.closest('tr');
                    if (row) {
                        var kodeMkCell = row.querySelector('td:nth-child(2)');
                        if (kodeMkCell) {
                            var kodeMk = kodeMkCell.textContent.trim();
                            if (select.value) {
                                prediksiValues[kodeMk] = select.value;
                            }
                        }
                    }
                });
                sessionStorage.setItem('prediksi_values', JSON.stringify(prediksiValues));
                
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
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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
                        
                        // Restore scroll position from sessionStorage
                        var savedScroll = sessionStorage.getItem('perencanaan_scroll');
                        if (savedScroll) {
                            window.scrollTo(0, parseInt(savedScroll));
                            sessionStorage.removeItem('perencanaan_scroll');
                        }
                        
                        // Restore prediksi nilai values
                        var savedPrediksi = sessionStorage.getItem('prediksi_values');
                        if (savedPrediksi) {
                            var prediksiData = JSON.parse(savedPrediksi);
                            document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
                                var row = select.closest('tr');
                                if (row) {
                                    var kodeMkCell = row.querySelector('td:nth-child(2)');
                                    if (kodeMkCell) {
                                        var kodeMk = kodeMkCell.textContent.trim();
                                        if (prediksiData[kodeMk]) {
                                            select.value = prediksiData[kodeMk];
                                        }
                                    }
                                }
                            });
                            sessionStorage.removeItem('prediksi_values');
                            
                            // Trigger calculation after restoring values
                            if (typeof hitungSimulasi === 'function') {
                                hitungSimulasi();
                            }
                        }
                        
                        // Show toast if exists in new content
                        var toast = document.getElementById('toast-notification');
                        if (toast) {
                            setTimeout(function() {
                                if (toast) {
                                    toast.classList.remove('show');
                                    setTimeout(function() { toast.remove(); }, 150);
                                }
                            }, 5000);
                        }
                        
                        // Re-attach event listeners
                        attachFormListeners();
                    } else {
                        // Fallback - full page reload
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }
                    // Fallback on error
                    form.submit();
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
        // Hitung simulasi saat halaman dimuat
        hitungSimulasi();
    });

    // ============================================
    // SIMULASI IPK - Kalkulasi Real-time
    // ============================================
    const ipkAwal = <?php echo number_format($ipk_awal, 2); ?>;
    const sksAwal = <?php echo $sks_awal; ?>;
    const nimUser = '<?php echo htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?>';

    // Simpan prediksi nilai ke localStorage
    function simpanPrediksi() {
        const selects = document.querySelectorAll('.prediksi-nilai');
        const data = {};
        selects.forEach(function(select, index) {
            if (select.value !== '') {
                // Gunakan kode_mk dari row terdekat atau index
                const row = select.closest('tr');
                const kodeMk = row ? row.querySelector('td:nth-child(2)').textContent.trim() : index;
                data[kodeMk] = select.value;
            }
        });
        localStorage.setItem('prediksi_nilai_' + nimUser, JSON.stringify(data));
    }

    // Restore prediksi nilai dari localStorage
    function restorePrediksi() {
        const saved = localStorage.getItem('prediksi_nilai_' + nimUser);
        if (saved) {
            const data = JSON.parse(saved);
            const selects = document.querySelectorAll('.prediksi-nilai');
            selects.forEach(function(select) {
                const row = select.closest('tr');
                const kodeMk = row ? row.querySelector('td:nth-child(2)').textContent.trim() : null;
                if (kodeMk && data[kodeMk]) {
                    select.value = data[kodeMk];
                }
            });
        }
    }

    function hitungSimulasi() {
        const selects = document.querySelectorAll('.prediksi-nilai');
        let totalBobot = 0;
        let totalSksRencana = 0;

        selects.forEach(function(select) {
            const sks = parseInt(select.dataset.sks) || 0;
            const nilai = parseFloat(select.value);
            
            if (select.value !== '' && !isNaN(nilai)) {
                totalBobot += sks * nilai;
                totalSksRencana += sks;
            }
        });

        // Simpan ke localStorage setiap kali berubah
        simpanPrediksi();

        // Hitung IPS (dari MK yang sudah diisi)
        let ips = 0;
        if (totalSksRencana > 0) {
            ips = totalBobot / totalSksRencana;
        }

        // Hitung IPK Akhir Estimasi
        // Rumus: ((SKS Awal * IPK Awal) + (SKS Rencana * Bobot)) / (SKS Awal + SKS Rencana)
        let ipkAkhir = ipkAwal;
        if (totalSksRencana > 0) {
            ipkAkhir = ((sksAwal * ipkAwal) + totalBobot) / (sksAwal + totalSksRencana);
        }

        // Update tampilan
        const ipsEl = document.getElementById('estimasi-ips');
        const ipkEl = document.getElementById('estimasi-ipk');
        
        if (ipsEl) ipsEl.textContent = ips.toFixed(2);
        if (ipkEl) ipkEl.textContent = ipkAkhir.toFixed(2);

        // Warna berdasarkan nilai IPK
        if (ipkEl) {
            ipkEl.classList.remove('text-success', 'text-warning', 'text-danger', 'text-primary');
            if (totalSksRencana === 0) {
                ipkEl.classList.add('text-primary');
            } else if (ipkAkhir >= 3.0) {
                ipkEl.classList.add('text-success');
            } else if (ipkAkhir >= 2.0) {
                ipkEl.classList.add('text-warning');
            } else {
                ipkEl.classList.add('text-danger');
            }
        }
    }

    // Restore prediksi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        restorePrediksi();
        hitungSimulasi();
    });
</script>

<?php include 'layout/footer.php'; ?>
