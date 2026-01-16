<?php
session_start();

// kalo sudah login, redirect ke dashboard
if (isset($_SESSION['nim']) && !empty($_SESSION['nim'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portal INSPIRE Universitas Sam Ratulangi">
    <meta name="keywords" content="UNSRAT, INSPIRE, Akademik, Portal, Universitas, Sam, Ratulangi">

    <title>INSPIRE Portal - Login</title>

    <!-- Local CSS -->
    <link rel="icon" href="assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="assets/css/auth.bundle.css">
    <link href="https://fonts.googleapis.com/css2?family=Philosopher:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body class="login-page sidebar-collapse">

    <div id="container_start">
        <div class="btn_pulse_1">
            <img alt="unsrat" style="width: 4.8em;" src="assets/images/logo.png">
        </div>
        <div class="btn_pulse_ket">
            click to launch ...
        </div>
    </div>

    <div id="particles-js"><canvas class="particles-js-canvas-el" width="0" height="0" style="width: 100%; height: 100%;"></canvas></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-primary fixed-top navbar-transparent" color-on-scroll="400" style="z-index: 222;">
        <div class="container">
            <div class="dropdown button-dropdown d-lg-none d-xl-none">
                <a>
                    <span class="button-bar"></span>
                    <span class="button-bar"></span>
                    <span class="button-bar"></span>
                </a>
            </div>
            <div class="navbar-translate">
                <a class="navbar-brand"></a>
                <button class="navbar-toggler navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-bar top-bar"></span>
                    <span class="navbar-toggler-bar middle-bar"></span>
                    <span class="navbar-toggler-bar bottom-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse justify-content-end" id="navigation">
                <ul class="navbar-nav">
                    <li class="nav-item d-xl-none">
                        <a class="nav-link">
                            <h4>PORTAL INSPIRE</h4>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.unsrat.ac.id" style="text-decoration: underline;">www.unsrat.ac.id</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <p>Baca Peraturan Akademik</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <p>E-Logbook PPDS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.facebook.com/UNSRAT/" target="_blank">
                            <i class="fab fa-facebook-square"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://twitter.com/kampusunsrat" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.instagram.com/unsrat1961/" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.youtube.com/channel/UCoH5uiDbLL4G1Ri7C8w3_cA" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- content -->
    <div class="page-header clear-filter" id="container_login" filter-color="orange">
        <div class="page-header-image"></div>
        <div class="content">
            <div class="container">
                <br><br><br><br>
                <div class="col-md-4 ml-auto mr-auto">
                    <div class="card card-login card-plain">
                        <form action="auth.php" id="login-form" method="POST">
                            <!-- CSRF token -->
                            <?php
                            // generate CSRF token
                            if (empty($_SESSION['csrf_token'])) {
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            }
                            ?>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="text-center">
                                <img title="Arti INSPIRE" src="assets/images/logo_inspire.png">
                            </div>
                            <br>
                            <?php if (isset($_GET['pesan'])): ?>
                                <?php if ($_GET['pesan'] === 'gagal'): ?>
                                <div class="text-center text-white mb-2">
                                    Username atau Password salah
                                </div>
                                <?php elseif ($_GET['pesan'] === 'logout'): ?>
                                <div class="text-center text-white mb-2">
                                    Anda telah logout
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="input-group no-border input-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-user-circle"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="username">
                                </div>
                                <div class="input-group no-border input-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-key"></i>
                                        </span>
                                    </div>
                                    <input type="password" name="password" placeholder="Password" class="form-control" required autocomplete="current-password">
                                </div>
                                <div class="input-group no-border input-lg">
                                    <button type="submit" class="btn btn-danger btn-round btn-lg btn-block">LOGIN</button>
                                </div>
                                <a href="#">Lupa Password</a>
                                <br>
                                <a href="#">Request Reset Password</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <div class="copyright" id="copyright">
                    © <?php echo date('Y'); ?> - UPT TIK Unsrat
                </div>
            </div>
        </footer>
    </div>

    <script src="assets/js/auth.bundle.js" type="text/javascript"></script>

    <script>
    $(document).ready(function() {
        // clear pesan dari URL biar ga muncul pas refresh
        if (window.location.search.includes('pesan=')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    $('.btn_pulse_1').on('click', function() {
        $('#particles-js').hide();
        $('#container_start').fadeOut(1000);
        $('.btn_pulse_ket').fadeOut(500);
        $('#container_login').fadeIn(1000);
        $('.navbar').fadeIn(1000);
    });
    </script>
</body></html>
