<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: index.php");
    exit();
}

// Get user info from session
$nim = isset($_SESSION['nim']) ? $_SESSION['nim'] : '';
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : $nim;

// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html style="height: auto;"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>INSPIRE Portal</title>
	<link rel="icon" href="assets/images/logo-unsrat-mosaic.png">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Portal INSPIRE Universitas Sam Ratulangi">
	<meta name="keywords" content="INSPIRE, UNSRAT, inspire, Universitas, Sam, Ratulangi">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<link rel="stylesheet" href="assets/css/dashboard.bundle.css">
	<link href="https://fonts.googleapis.com/css2?family=Philosopher:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
	<style>
		#navbar-title {
			font-family: 'Philosopher', sans-serif !important;
		}
	</style>



</head>

<body class="layout-navbar-fixed layout-fixed sidebar-mini" style="height: auto;">

	<span id="main_content">
		<div id="loading-container" style="display: none;">
			<div id="loading">
				<img alt="unsrat" id="loading-logo" src="assets/images/logo.png">
				memuat ..
			</div>
		</div>
		<div id="alert_js"></div>
		<div class="wrapper">
			<!-- Navbar -->
			<nav class="main-header navbar navbar-expand navbar-dark navbar-danger">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
					</li>
				</ul>

				<!-- Right navbar links -->
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a href="kalender.php">
							<span class="nav-link" title="INSPIRE Kalender">
								<i class="fas fa-calendar-alt"></i>
							</span>
						</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link pr-0 d-none d-sm-block" data-toggle="dropdown" href="#profile_dropdown">
							<div class="image">
								<img src="assets/images/user_default.png" class="img-circle" alt="User Image" width="22px">
							</div>
						</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link pl-2 pr-3 d-none d-sm-block" data-toggle="dropdown" href="#" style="line-height: 1em;">
							<small><span class="text-uppercase"><?php echo htmlspecialchars($nama); ?></span></small><br>
							<small><?php echo htmlspecialchars($nim); ?></small>
						</a>
						<a class="nav-link button pl-2 pr-3 d-block d-sm-none" data-toggle="dropdown" href="#">
							<i class="fas fa-user"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="profile_dropdown">
							<span class="dropdown-item dropdown-header">
								<img alt="foto profil" src="assets/images/user_default.png" style="width: 80px;">
							</span>
							<a href="ubah_password.php" class="dropdown-item dropdown-footer text-bold text-uppercase">
								Ubah Password<i class="nav-icon fas fa-unlock-alt ml-2"></i>
							</a>
							<a href="#" class="dropdown-item dropdown-footer text-bold text-uppercase" data-toggle="modal" data-target="#mdl-logout">
								KELUAR<i class="nav-icon fas fa-sign-out-alt ml-2"></i>
							</a>
						</div>
					</li>
				</ul>
			</nav>

			<!-- Sidebar -->
			<aside class="main-sidebar sidebar-light-danger elevation-4">
				<a href="dashboard.php" class="brand-link bg-danger">
					<img src="assets/images/logo_mosaic.png" alt="Logo" class="brand-image img-circle elevation-1" style="opacity: .8; filter: brightness(0) invert(1);">
					<span class="brand-text font-weight-light" style="font-size: 15pt" id="navbar-title">INSPIRE</span>
				</a>

				<!-- sidebar -->
				<div class="sidebar">
					<nav class="mt-4">
						<ul class="nav nav-pills nav-sidebar nav-child-indent flex-column text-sm" data-widget="treeview" role="menu" data-accordion="true">
							<!-- Beranda -->
							<li class="nav-item">
								<a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
									<i class="nav-icon fas fa-home"></i>
									<p>Beranda</p>
								</a>
							</li>
							
							<!-- Biodata -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-user"></i>
									<p>Biodata <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="biodata.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Biodata Saya</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Personal Page</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ubah Data Pribadi</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Perkuliahan -->
							<?php 
							$perkuliahan_pages = ['jadwal.php', 'krs.php', 'perencanaan.php', 'khs.php', 'transkrip.php'];
							$is_perkuliahan_active = in_array($current_page, $perkuliahan_pages);
							?>
							<li class="nav-item has-treeview <?php echo $is_perkuliahan_active ? 'menu-open' : ''; ?>">
								<a href="#" class="nav-link <?php echo $is_perkuliahan_active ? 'active' : ''; ?>">
									<i class="nav-icon fas fa-book-reader"></i>
									<p>Perkuliahan <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Kampus Merdeka <i class="fas fa-angle-left right"></i></p></a>
										<ul class="nav nav-treeview">
											<li class="nav-item">
												<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pendaftaran</p></a>
											</li>
											<li class="nav-item">
												<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Komunikasi</p></a>
											</li>
										</ul>
									</li>
									<li class="nav-item">
										<a href="jadwal.php" class="nav-link <?php echo ($current_page == 'jadwal.php') ? 'active' : ''; ?>"><i class="far fa-circle nav-icon"></i><p>Jadwal</p></a>
									</li>
									<li class="nav-item">
								<a href="krs.php" class="nav-link <?php echo ($current_page == 'krs.php') ? 'active' : ''; ?>"><i class="far fa-circle nav-icon"></i><p>KRS</p></a>
							</li>
							<li class="nav-item">
								<a href="perencanaan.php" class="nav-link <?php echo ($current_page == 'perencanaan.php') ? 'active' : ''; ?>"><i class="far fa-circle nav-icon"></i><p>Perencanaan Studi</p></a>
							</li>
							<li class="nav-item">
								<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Presensi</p></a>
							</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Materi Kuliah</p></a>
									</li>
									<li class="nav-item has-treeview">
							<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tugas Kuliah <i class="fas fa-angle-left right"></i></p></a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tugas Individu</p></a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tugas Studi Kasus</p></a>
								</li>
								<li class="nav-item">
									<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tugas Proyek</p></a>
								</li>
							</ul>
						</li>
						<li class="nav-item">
							<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Forum Diskusi</p></a>
						</li>
									<li class="nav-item">
							<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ujian Akhir Semester</p></a>
						</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>EPOM</p></a>
									</li>
									<li class="nav-item">
										<a href="khs.php" class="nav-link <?php echo ($current_page == 'khs.php') ? 'active' : ''; ?>"><i class="far fa-circle nav-icon"></i><p>KHS</p></a>
									</li>
									<li class="nav-item">
										<a href="transkrip.php" class="nav-link <?php echo ($current_page == 'transkrip.php') ? 'active' : ''; ?>"><i class="far fa-circle nav-icon"></i><p>Transkrip</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Bimbingan Khusus</p></a>
									</li>
								</ul>
							</li>
							
							<!-- E-Learning Unsrat -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-book-open"></i>
									<p>E-Learning Unsrat <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Unsrat@Learn</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Google Classroom</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Kemahasiswaan -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-user-friends"></i>
									<p>Kemahasiswaan <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Beasiswa</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Prestasi</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Organisasi Mahasiswa</p></a>
									</li>
								</ul>
							</li>
							
							<!-- KKT -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-street-view"></i>
									<p>KKT <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Informasi</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pendaftaran</p></a>
									</li>
								</ul>
							</li>
							
							<!-- PKM Penelitian Mahasiswa -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-microscope"></i>
									<p>PKM Penelitian Mahasiswa <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Informasi Proposal</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pengajuan Proposal</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Bimbingan Akademik -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-comments"></i>
									<p>Bimbingan Akademik <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Komunikasi Pembimbing</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Praktik Lapangan/Magang -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-landmark"></i>
									<p>Praktik Lapangan/Magang <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pengajuan Magang</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pelaksanaan Magang</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Skripsi / Tesis -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-book"></i>
									<p>Skripsi / Tesis <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Seminar Proposal</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pembimbingan</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Seminar Hasil</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Ujian Akhir</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Mahasiswa Kewirausahaan -->
							<li class="nav-item">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-coffee"></i>
									<p>Mahasiswa Kewirausahaan</p>
								</a>
							</li>
							
							<!-- Cuti & Pindah -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-calendar-times"></i>
									<p>Cuti & Pindah <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Cuti Akademik</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pindah Prodi</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Pengaktifan Kembali -->
							<li class="nav-item">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-user-check"></i>
									<p>Pengaktifan Kembali</p>
								</a>
							</li>
							
							<!-- Wisuda -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-user-graduate"></i>
									<p>Wisuda <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Info</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Kuesioner</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Daftar</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Perpustakaan -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-bookmark"></i>
									<p>Perpustakaan <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Perpustakaan Unsrat</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>e-Library Unsrat</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Naskah Dinas -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-envelope-open-text"></i>
									<p>Naskah Dinas <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Surat Edaran</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Layanan Kesehatan -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-syringe"></i>
									<p>Layanan Kesehatan <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Daftar Vaksinasi</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Lapor Vaksinasi</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Konseling -->
							<li class="nav-item">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-heart"></i>
									<p>Konseling</p>
								</a>
							</li>
							
							<!-- Layanan Lain -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-th-large"></i>
									<p>Layanan Lain <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="billing.php" class="nav-link"><i class="nav-icon fas fa-money-bill-alt"></i><p>Billing</p></a>
									</li>
									<li class="nav-item">
										<a href="kalender.php" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Kalender</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="nav-icon fas fa-envelope"></i><p>Email Unsrat</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="nav-icon fas fa-wifi"></i><p>UNSRAT WiFi</p></a>
									</li>
								</ul>
							</li>
							
							<!-- Bantuan Pengguna -->
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-info"></i>
									<p>Bantuan Pengguna <i class="fas fa-angle-left right"></i></p>
								</a>
								<ul class="nav nav-treeview">
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Panduan Inspire</p></a>
									</li>
									<li class="nav-item">
										<a href="#" class="nav-link"><i class="nav-icon fas fa-question-circle"></i><p>Helpdesk</p></a>
									</li>
								</ul>
							</li>
						</ul>
					</nav>
				</div>
			</aside>

			<!-- MAIN CONTENT WRAPPER START -->
