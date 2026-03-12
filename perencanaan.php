<?php
// proses form dulu sebelum nampilkan halaman
include 'config/database.php';

// mulai session kalo belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$nim = $_SESSION['nim'] ?? '';
$message = '';
$message_type = '';

// proses aksi dari form
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
                $message = 'Mata Kuliah berhasil ditambahkan ke Perencanaan!';
                $message_type = 'success';
            } else {
                $message = 'Mata Kuliah sudah ada dalam Perencanaan!';
                $message_type = 'warning';
            }
        }

        
        if ($_POST['action'] === 'delete' && isset($_POST['kode_mk'])) {
            $kode_mk = $_POST['kode_mk'];
            $stmt = $conn->prepare("DELETE FROM perencanaan_studi WHERE nim = ? AND kode_mk = ?");
            $stmt->bind_param("ss", $nim, $kode_mk);
            $stmt->execute();
            $stmt->close();
            $message = 'Mata Kuliah berhasil dihapus dari Perencanaan!';
            $message_type = 'info';
        }
        
        // hapus semua rencana MK
        if ($_POST['action'] === 'delete_all') {
            $stmt = $conn->prepare("DELETE FROM perencanaan_studi WHERE nim = ?");
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $deleted_count = $stmt->affected_rows;
            $stmt->close();
            $message = "$deleted_count Mata Kuliah berhasil dihapus dari Perencanaan!";
            $message_type = 'info';
        }

    }
    
    // simpan pesan ke session buat ditampilin setelah redirect
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_message_type'] = $message_type;
    }
    
    // cek apakah request dari AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        // kalo AJAX, lanjut render halaman aja
        // gak perlu redirect
    } else {
        // kalo bukan AJAX, redirect biar gak muncul "Confirm Form Resubmission"
        header('Location: perencanaan.php#rencana-studi');
        exit;
        
    }
}

// include header setelah proses form (biar redirect bisa jalan)
include 'layout/header.php';

// ============================================
// AMBIL SEMESTER MAHASISWA SEKARANG
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
// HITUNG MKP (MATA KULIAH PILIHAN) YANG LULUS
// ============================================
// Rule: 6 MKP total (2 per semester di sem 5,6,7)
$mkp_lulus = 0;
$mkp_required = 6;
$stmt_mkp = $conn->prepare("SELECT COUNT(DISTINCT t.kode_mk) as total_mkp 
                            FROM transkrip t
                            JOIN mata_kuliah mk ON t.kode_mk = mk.kode_mk
                            WHERE t.nim = ? 
                            AND mk.jenis = 'pilihan'
                            AND t.nilai_huruf NOT IN ('D', 'E', 'N')");
$stmt_mkp->bind_param("s", $nim);
$stmt_mkp->execute();
$result_mkp = $stmt_mkp->get_result();
if ($result_mkp && $row_mkp = $result_mkp->fetch_assoc()) {
    $mkp_lulus = (int) $row_mkp['total_mkp'];
}
$stmt_mkp->close();

// ============================================
// PERENCANAAN UNTUK SEMESTER INI
// ============================================
// rencanakan MK semester ini (tampilkan MK sesuai tipe semester)
$planning_semester = $current_semester;

// ============================================
// VARIABEL TIPE SEMESTER
// ============================================

// tipe semester sekarang (buat "Status Matakuliah yang Sudah Dikontrak")
$is_current_ganjil = ($current_semester % 2 == 1);
$current_semester_label = $is_current_ganjil ? 'Ganjil' : 'Genap';
$current_semester_icon = $is_current_ganjil ? 'fa-calendar-alt' : 'fa-calendar-check';
$current_semester_color = $is_current_ganjil ? 'primary' : 'success';

// tipe semester untuk perencanaan (buat rekomendasi)
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
                    ORDER BY t.semester ASC, t.id ASC");
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
// AMBIL DATA UNTUK PERENCANAAN
// ============================================

// ambil semua matakuliah
$mata_kuliah_list = [];
$result_mk = $conn->query("SELECT * FROM mata_kuliah ORDER BY semester ASC, id ASC");
if ($result_mk) {
    while ($row = mysqli_fetch_assoc($result_mk)) {
        $mata_kuliah_list[] = $row;
    }
}

// ambil MK yang direncanakan user
$perencanaan_list = [];
$total_sks = 0;
$mk_wajib = 0;
$mk_pilihan = 0;

$stmt_plan = $conn->prepare("SELECT p.*, m.nama_mk, m.sks, m.jenis, m.kategori, m.semester as mk_semester
               FROM perencanaan_studi p 
               JOIN mata_kuliah m ON p.kode_mk = m.kode_mk 
               WHERE p.nim = ? 
               ORDER BY p.id ASC");
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

// ambil kode MK yang sudah direncanakan buat filter dropdown
$planned_codes = array_column($perencanaan_list, 'kode_mk');

// ============================================
// FETCH IPK DAN SKS AWAL UNTUK SIMULASI
// ============================================
$ipk_awal = 0;
$sks_awal = 0;

$stmt_ipk = $conn->prepare("SELECT 
    CASE WHEN SUM(mk.sks) > 0 THEN ROUND(SUM(mk.sks * t.bobot) / SUM(mk.sks), 2) ELSE 0 END as ipk,
    COALESCE(SUM(CASE WHEN t.nilai_huruf NOT IN ('D', 'E') THEN mk.sks ELSE 0 END), 0) as sks_lulus
    FROM transkrip t
    JOIN mata_kuliah mk ON t.kode_mk = mk.kode_mk
    WHERE t.nim = ?");
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

// ambil semua kode_mk yang sudah pernah dikontrak (ada di transkrip)
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

// cari MK yang belum pernah dikontrak
$mk_belum_kontrak_ganjil = [];
$mk_belum_kontrak_genap = [];

// MK khusus yang bisa dikontrak di semester Ganjil maupun Genap
$mk_special_codes = ['TIK4010', 'TIK4020', 'TIK4040']; // KKT, Magang, Skripsi

foreach ($mata_kuliah_list as $mk) {
    if (!in_array($mk['kode_mk'], $contracted_codes)) {
        // MK khusus masuk ke dua-duanya (ganjil & genap)
        if (in_array($mk['kode_mk'], $mk_special_codes)) {
            $mk_belum_kontrak_ganjil[] = $mk;
            $mk_belum_kontrak_genap[] = $mk;
        } elseif ($mk['semester'] % 2 == 1) {
            $mk_belum_kontrak_ganjil[] = $mk;
        } else {
            $mk_belum_kontrak_genap[] = $mk;
        }
    }
}

// ============================================
// FUNGSI PENGURUTAN: BERDASARKAN ID (URUTAN PORTAL)
// ============================================
$sort_function = function($a, $b) {
    // urutkan berdasarkan ID (urutan portal)
    $id_a = $a['id'] ?? 0;
    $id_b = $b['id'] ?? 0;
    
    if ($id_a != $id_b) {
        return $id_a - $id_b;
    }
    return 0;
};
?>

<!-- Content Wrapper -->
<div class="content-wrapper" style="min-height: 799.031px;">
    <div class="content-header">
        <div class="container-fluid"><h1>Perencanaan Studi</h1></div>
    </div>
    
    <div class="content">
        <div class="container-fluid">
            <?php 
            // baca pesan flash dari session
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
                 style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 280px; max-width: 90%; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding-right: 2.5rem;">
                <button type="button" class="close" data-dismiss="alert" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); padding: 0; font-size: 1.2rem;">&times;</button>
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'info-circle')); ?> mr-2"></i>
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
                            // Select data based on PLANNING semester type (semester 8 = Genap)
                            $transkrip_aktif = $is_semester_ganjil ? $transkrip_ganjil : $transkrip_genap;
                            $semester_list = $is_semester_ganjil ? '1, 3, 5, 7' : '2, 4, 6, 8';
                            
                            // Apply Sorting
                            usort($transkrip_aktif, $sort_function);
                            ?>
                            
                            <?php if (count($transkrip_aktif) > 0): 
                                // kelompokkan berdasarkan semester
                                $grouped_transkrip = [];
                                foreach ($transkrip_aktif as $mk) {
                                    $sem = $mk['semester'];
                                    if (!isset($grouped_transkrip[$sem])) {
                                        $grouped_transkrip[$sem] = [];
                                    }
                                    $grouped_transkrip[$sem][] = $mk;
                                }
                                // urutkan berdasarkan semester
                                ksort($grouped_transkrip);
                            ?>
                                <?php foreach ($grouped_transkrip as $semester_num => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-<?php echo $semester_type_color; ?> semester-heading"><strong>Semester <?php echo $semester_num; ?></strong></h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped table-mobile table-sm mb-0" style="min-width: 600px; width: 100%;">
                                        <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                            <tr>
                                                <th style="width: 90px;">Kode MK</th>
                                                <th style="width: auto;">Nama Matakuliah</th>
                                                <th style="width: 50px;" class="text-center">SKS</th>
                                                <th style="width: 60px;" class="text-center">Nilai</th>
                                                <th style="width: 100px;" class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr class="<?php echo !$mk['is_lulus'] ? 'table-danger' : ''; ?>">
                                                <td><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                <td><?php echo strtoupper(htmlspecialchars($mk['display_nama'])); ?></td>
                                                <td class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td class="text-center">
                                                    <?php echo $mk['nilai_huruf']; ?>
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <?php if ($mk['is_lulus']): ?>
                                                        <span class="text-success"><i class="fas fa-check-circle"></i><span class="status-text"> Lulus</span></span>
                                                    <?php else: ?>
                                                        <span class="text-danger"><i class="fas fa-times-circle"></i><span class="status-text"> Gagal</span></span>
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
                // pakai planning_semester yang sudah dihitung diatas
                // planning_semester is already set to either current_semester or current_semester + 1
                $is_planning_ganjil = ($planning_semester % 2 == 1);
                
                // filter MK tidak lulus berdasarkan tipe semester perencanaan
                // If planning semester is ganjil, show ganjil MK. If planning is genap, show genap MK.
                // Special courses (KKT, Magang, Skripsi) always shown
                $rekomendasi_aktif = array_filter($mk_tidak_lulus, function($mk) use ($is_planning_ganjil, $mk_special_codes) {
                    // Special courses always shown
                    if (in_array($mk['kode_mk'], $mk_special_codes)) {
                        return true;
                    }
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
                                // kelompokkan berdasarkan semester
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
                                    <table class="table table-bordered table-striped table-mobile table-sm" style="min-width: 600px; width: 100%;">
                                        <thead class="bg-<?php echo $semester_color; ?> text-white">
                                            <tr>
                                                <th style="width: 90px;">Kode MK</th>
                                                <th style="width: auto;">Nama Matakuliah</th>
                                                <th style="width: 50px;" class="text-center">SKS</th>
                                                <th style="width: 60px;" class="text-center">Nilai</th>
                                                <th style="width: 160px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr class="table-danger">
                                                <td data-label="Kode MK"><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                 <td data-label="Nama MK"><?php echo strtoupper(htmlspecialchars($mk['display_nama'])); ?></td>
                                                <td data-label="SKS" class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td data-label="Nilai" class="text-center">
                                                    <span class="text-dark"><b><?php echo $mk['nilai_huruf']; ?></b></span>
                                                </td>
                                                <td data-label="Aksi" class="text-center">
                                                    <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="source" value="rekomendasi">
                                                        <input type="hidden" name="kode_mk" value="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" style="width: 140px; font-weight: 500;">
                                                            <i class="fas fa-plus mr-1"></i> TAMBAH
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button class="btn btn-info btn-sm disabled" style="width: 140px; cursor: default; opacity: 1; font-weight: 500;">
                                                        <i class="fas fa-check mr-1"></i> TERENCANA
                                                    </button>
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
                                <h6 class="mt-3 mb-2 text-<?php echo $semester_type_color; ?> semester-heading"><strong>Semester <?php echo $semester_num; ?></strong></h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped table-mobile table-sm mb-0" style="min-width: 600px; width: 100%;">
                                        <thead class="bg-<?php echo $semester_type_color; ?> text-white">
                                            <tr>
                                                <th style="width: 90px;">Kode MK</th>
                                                <th style="width: auto;">Nama Matakuliah</th>
                                                <th style="width: 50px;" class="text-center">SKS</th>
                                                <th style="width: 160px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                <td><?php echo strtoupper(htmlspecialchars($mk['nama_mk'])); ?></td>
                                                <td class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td class="text-center text-nowrap">
                                                    <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="source" value="mk-belum-kontrak">
                                                        <input type="hidden" name="kode_mk" value="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" style="width: 140px; font-weight: 500;">
                                                            <i class="fas fa-plus mr-1"></i> TAMBAH
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button class="btn btn-info btn-sm disabled" style="width: 140px; cursor: default; opacity: 1; font-weight: 500;">
                                                        <i class="fas fa-check mr-1"></i> TERENCANA
                                                    </button>
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
                                <h5 class="mb-3 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-cubes mr-2"></i>Paket Matakuliah Pilihan (MKP)</span>
                                    <span class="badge badge-<?php echo ($mkp_lulus >= $mkp_required) ? 'success' : 'warning'; ?>">MKP Lulus: <?php echo $mkp_lulus; ?>/<?php echo $mkp_required; ?></span>
                                </h5>
                                <?php foreach ($grouped_pilihan as $kategori => $mk_list): ?>
                                <h6 class="mt-3 mb-2 text-info semester-heading"><strong><i class="fas fa-layer-group mr-1"></i><?php echo htmlspecialchars($kategori); ?></strong></h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped table-mobile table-sm mb-0" style="min-width: 600px; width: 100%;">
                                        <thead class="bg-info text-white">
                                            <tr>
                                                <th style="width: 90px;">Kode MK</th>
                                                <th style="width: auto;">Nama Matakuliah</th>
                                                <th style="width: 50px;" class="text-center">Sem</th>
                                                <th style="width: 50px;" class="text-center">SKS</th>
                                                <th style="width: 160px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($mk['kode_mk']); ?></td>
                                                <td><?php echo strtoupper(htmlspecialchars($mk['nama_mk'])); ?></td>
                                                <td class="text-center"><?php echo $mk['semester']; ?></td>
                                                <td class="text-center"><?php echo $mk['sks']; ?></td>
                                                <td class="text-center text-nowrap">
                                                    <?php if (!in_array($mk['kode_mk'], $planned_codes)): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="add">
                                                        <input type="hidden" name="source" value="mk-belum-kontrak">
                                                        <input type="hidden" name="kode_mk" value="<?php echo htmlspecialchars($mk['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" style="width: 140px; font-weight: 500;">
                                                            <i class="fas fa-plus mr-1"></i> TAMBAH
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button class="btn btn-info btn-sm disabled" style="width: 140px; cursor: default; opacity: 1; font-weight: 500;">
                                                        <i class="fas fa-check mr-1"></i> TERENCANA
                                                    </button>
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
                                <div class="col-12 col-md-8 mb-3">
                                    <h5 class="mb-3 semester-heading">Rencana Mata Kuliah Semester <?php echo $planning_semester; ?></h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-mobile table-sm table-rencana mb-0" style="min-width: 700px; width: 100%;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 40px;" class="text-center">No</th>
                                                    <th style="width: 90px;">Kode MK</th>
                                                    <th style="width: auto;">Nama Matakuliah</th>
                                                    <th style="width: 50px;" class="text-center">SKS</th>
                                                    <th style="width: 70px;" class="text-center">Jenis</th>
                                                    <th style="width: 100px;" class="text-center">Prediksi</th>
                                                    <th style="width: 60px;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($perencanaan_list) > 0): ?>
                                                    <?php $no = 1; foreach ($perencanaan_list as $plan): ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td class="text-nowrap"><?php echo htmlspecialchars($plan['kode_mk']); ?></td>
                                                        <td><?php echo strtoupper(htmlspecialchars($plan['nama_mk'])); ?></td>
                                                        <td class="text-center"><?php echo $plan['sks']; ?></td>
                                                        <td class="text-center text-nowrap">
                                                            <?php echo $plan['jenis'] === 'wajib' ? 'Wajib' : 'Pilihan'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <select class="form-control form-control-sm prediksi-nilai" 
                                                                    data-sks="<?php echo (int) $plan['sks']; ?>" 
                                                                    onchange="hitungSimulasi()" style="min-width:50px;">
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
                                                                <input type="hidden" name="kode_mk" value="<?php echo htmlspecialchars($plan['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <button type="submit" class="btn btn-danger btn-xs" data-confirm="Hapus Mata Kuliah ini?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-3">
                                                            <i class="fas fa-inbox mb-1 d-block"></i>
                                                            Belum ada MK direncanakan
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <td colspan="3"><strong>Total SKS: <?php echo $total_sks; ?></strong></td>
                                                    <td colspan="4" class="text-right">
                                                        <?php if (count($perencanaan_list) > 0): ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="action" value="delete_all">
                                                            <input type="hidden" name="source" value="rencana-studi">
                                                            <button type="submit" class="btn btn-outline-danger btn-xs" data-confirm="Hapus SEMUA?">
                                                                <i class="fas fa-trash-alt mr-1"></i>Hapus Semua
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <h5 class="mb-3 semester-heading">Ringkasan</h5>
                                    <table class="table table-sm table-bordered table-compact table-ringkasan mb-0">
                                        <tr>
                                            <td>MK Wajib</td>
                                            <td class="text-center" width="50"><?php echo $mk_wajib; ?></td>
                                        </tr>
                                        <tr>
                                            <td>MK Pilihan</td>
                                            <td class="text-center"><?php echo $mk_pilihan; ?></td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td><strong>Total MK</strong></td>
                                            <td class="text-center"><strong><?php echo $mk_wajib + $mk_pilihan; ?></strong></td>
                                        </tr>
                                    </table>
                                            
                                            <!-- SIMULASI IPK -->
                                            <div class="card card-primary mt-3 mb-0 card-simulasi">
                                                <div class="card-header bg-primary py-2">
                                                    <h6 class="card-title text-white mb-0">
                                                        <i class="fas fa-calculator mr-2"></i>Simulasi IPK
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr>
                                                            <td class="py-1 text-nowrap">IPK Saat Ini</td>
                                                            <td class="text-right py-1"><strong><?php echo number_format($ipk_awal, 2); ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-1 text-nowrap">SKS Lulus</td>
                                                            <td class="text-right py-1"><strong><?php echo $sks_awal; ?></strong></td>
                                                        </tr>
                                                        <tr class="border-top">
                                                            <td class="py-1 text-nowrap"><strong>Estimasi IPS</strong></td>
                                                            <td class="text-right py-1"><strong id="estimasi-ips">0.00</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-1 text-nowrap"><strong>Estimasi IPK Akhir</strong></td>
                                                            <td class="text-right py-1"><strong id="estimasi-ipk">0.00</strong></td>
                                                        </tr>
                                                        <?php 
                                                        $estimasi_sks = $sks_awal + $total_sks;
                                                        
                                                        // Target SKS kumulatif berdasarkan kurikulum TI UNSRAT
                                                        $target_per_semester = [
                                                            1 => 21,   // Semester 1: 21
                                                            2 => 41,   // Semester 2: 21+20 = 41
                                                            3 => 60,   // Semester 3: 41+19 = 60
                                                            4 => 79,   // Semester 4: 60+19 = 79
                                                            5 => 98,   // Semester 5: 79+19 = 98
                                                            6 => 118,  // Semester 6: 98+20 = 118
                                                            7 => 138,  // Semester 7: 118+20 = 138
                                                            8 => 144   // Semester 8: 138+6 = 144
                                                        ];
                                                        
                                                        // Batas minimum SKS untuk menghindari sanksi DO
                                                        $min_sks_semester = [
                                                            1 => 12, 2 => 24, 3 => 36, 4 => 48,
                                                            5 => 60, 6 => 72, 7 => 84, 8 => 96
                                                        ];
                                                        
                                                        $target_sks = $target_per_semester[$planning_semester] ?? 144;
                                                        $min_sks = $min_sks_semester[$planning_semester] ?? 96;
                                                        $target_lulus = 144;
                                                        
                                                        // Warna hanya merah jika di bawah batas minimum (risiko DO)
                                                        $sks_color = ($estimasi_sks < $min_sks) ? 'danger' : '';
                                                        ?>
                                                        <tr class="bg-light">
                                                            <td class="py-2 text-nowrap"><strong>Estimasi SKS Lulus</strong></td>
                                                            <td class="text-right py-2">
                                                                <strong id="estimasi-sks" <?php echo $sks_color ? 'class="text-'.$sks_color.'"' : ''; ?>>
                                                                    <?php echo $estimasi_sks; ?> / <?php echo $target_lulus; ?>
                                                                </strong>
                                                            </td>
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
                                                Melebihi batas maks SKS
                                            </div>
                                            <?php elseif ($total_sks > 0): ?>
                                            <div class="alert alert-success mt-3 mb-3">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Perencanaan dalam batas wajar.
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
                    // parse response HTML-nya
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
                        
                        // tampilkan toast kalo ada di konten baru
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
