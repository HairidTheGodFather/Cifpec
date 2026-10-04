<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';
session_start();

// ----- Paparan popup "Kata Laluan Berjaya Ditukar" -----
if (isset($_GET['success']) && $_GET['success'] === '1') {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Kata Laluan Ditukar - CIFPECScore</title>
        <style>
            html, body { height: 100%; margin: 0; }
            body {
                display: flex; align-items: center; justify-content: center;
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 100%);
            }
        </style>
    </head>
    <body>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Kata Laluan Berjaya Ditukar!',
                text: 'Sila log masuk menggunakan kata laluan baharu anda.',
                confirmButtonText: 'Log Masuk Sekarang',
                confirmButtonColor: '#4f46e5',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function () {
                window.location.href = 'index.php';
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Kalau tiada permintaan reset pending, hantar balik ke forgot-password
if (empty($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

function generateOTP() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// ----- Hantar semula OTP -----
if (isset($_POST['resend'])) {
    $otp = generateOTP();
    $_SESSION['reset_otp']        = $otp;
    $_SESSION['reset_otp_expiry'] = time() + 300;
    $_SESSION['reset_attempts']   = 0;

    sendOTPEmail($_SESSION['reset_email'], $_SESSION['reset_name'], $otp);

    $_SESSION['info'] = 'Kod OTP baharu telah dihantar ke email anda.';
    header("Location: reset-password.php");
    exit();
}

// ----- Sahkan OTP + Tetapkan Kata Laluan Baharu -----
if (isset($_POST['reset_password'])) {
    $inputOtp        = trim($_POST['otp']);
    $newPassword     = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (time() > $_SESSION['reset_otp_expiry']) {
        $_SESSION['error'] = 'Kod OTP telah tamat tempoh. Sila minta kod baharu.';
    } elseif (($_SESSION['reset_attempts'] ?? 0) >= 5) {
        $_SESSION['error'] = 'Terlalu banyak percubaan salah. Sila minta kod baharu.';
    } elseif ($inputOtp !== $_SESSION['reset_otp']) {
        $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;
        $_SESSION['error'] = 'Kod OTP salah. Sila cuba lagi.';
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Kata Laluan Baharu dan Sahkan Kata Laluan tidak sama!';
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['error'] = 'Kata Laluan mesti sekurang-kurangnya 6 aksara!';
    } else {
        // Semua sah -> kemaskini kata laluan dalam DB
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();
        $stmt->close();

        // Bersihkan session reset
        unset(
            $_SESSION['reset_email'],
            $_SESSION['reset_name'],
            $_SESSION['reset_otp'],
            $_SESSION['reset_otp_expiry'],
            $_SESSION['reset_attempts']
        );

        header("Location: reset-password.php?success=1");
        exit();
    }

    header("Location: reset-password.php");
    exit();
}

// Emel ter-mask untuk paparan (contoh: a***@gmail.com)
$maskedEmail = preg_replace('/(?<=.).(?=[^@]*?.@)/', '*', $_SESSION['reset_email']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tetapkan Kata Laluan Baharu - CIFPECScore</title>

    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link href="assets/vendor/fonts/circular-std/style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/libs/css/style.css">
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome/css/fontawesome-all.css">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 0;
            font-family: 'Circular Std Book', Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 100%);
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 30px 30px, 60px 60px, 60px 60px;
            z-index: -2;
        }

        .glow-shape-1, .glow-shape-2 {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            z-index: -2;
            animation: pulseGlow 6s infinite alternate;
        }
        .glow-shape-1 {
            width: 350px; height: 350px;
            background: rgba(79, 70, 229, 0.25);
            top: 10%; left: 15%;
        }
        .glow-shape-2 {
            width: 300px; height: 300px;
            background: rgba(147, 51, 234, 0.2);
            bottom: 10%; right: 15%;
            animation-delay: -3s;
        }

        .animated-objects {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .tech-obj {
            position: absolute;
            color: rgba(167, 139, 250, 0.15);
            text-shadow: 0 0 25px rgba(139, 92, 246, 0.4);
            animation: floatObj 6s ease-in-out infinite;
        }

        .obj-desktop { top: 15%; left: 12%; font-size: 140px; animation-delay: 0s; }
        .obj-laptop { top: 20%; right: 15%; font-size: 120px; animation-delay: -2s; color: rgba(99, 102, 241, 0.15); text-shadow: 0 0 25px rgba(99, 102, 241, 0.4); }
        .obj-iot { bottom: 15%; left: 18%; font-size: 110px; animation-delay: -4s; color: rgba(56, 189, 248, 0.15); text-shadow: 0 0 25px rgba(56, 189, 248, 0.4); }
        .obj-server { bottom: 20%; right: 12%; font-size: 130px; animation-delay: -1.5s; color: rgba(236, 72, 153, 0.15); text-shadow: 0 0 25px rgba(236, 72, 153, 0.4); }

        @keyframes floatObj {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-25px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulseGlow {
            0% { opacity: 0.6; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.1); }
        }

        .splash-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
            z-index: 10;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 30px 20px 15px;
            text-align: center;
        }

        .card-header h2 { color: #1e293b; font-weight: 700; margin-bottom: 15px; }
        .card-header h3 { font-size: 18px; color: #334155; font-weight: 600; }
        .card-header p { color: #64748b; font-size: 14px; margin-bottom: 0; }

        .otp-input {
            letter-spacing: 10px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
        }

        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #4338ca; border-color: #4338ca;
            transform: translateY(-2px);
        }
        .btn-link { color: #4f46e5; font-weight: 600; }
    </style>
</head>

<body>
    <div class="glow-shape-1"></div>
    <div class="glow-shape-2"></div>

    <div class="animated-objects">
        <i class="fas fa-desktop tech-obj obj-desktop"></i>
        <i class="fas fa-laptop tech-obj obj-laptop"></i>
        <i class="fas fa-microchip tech-obj obj-iot"></i>
        <i class="fas fa-server tech-obj obj-server"></i>
    </div>

    <div class="splash-container">
        <div class="card">
            <div class="card-header">
                <h2>CIFPECScore</h2>
                <h3 class="mb-1">Tetapkan Kata Laluan Baharu</h3>
                <p>Kod 6-digit telah dihantar ke <strong><?= htmlspecialchars($maskedEmail) ?></strong></p>
            </div>

            <div class="card-body">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['info'])): ?>
                    <div class="alert alert-info" role="alert"><?= htmlspecialchars($_SESSION['info']) ?></div>
                    <?php unset($_SESSION['info']); ?>
                <?php endif; ?>

                <form action="" method="post" id="resetForm">
                    <div class="form-group">
                        <input class="form-control form-control-lg otp-input" type="text" name="otp"
                               maxlength="6" pattern="\d{6}" inputmode="numeric" required
                               placeholder="------" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="new_password" id="new_password" type="password" required placeholder="Kata Laluan Baharu" minlength="6">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="confirm_password" id="confirm_password" type="password" required placeholder="Sahkan Kata Laluan Baharu" minlength="6">
                        <small class="text-danger" id="pwMismatch" style="display:none;">Kata laluan tidak sama.</small>
                    </div>
                    <div class="form-group pt-2">
                        <button class="btn btn-block btn-primary" type="submit" name="reset_password">Tukar Kata Laluan</button>
                    </div>
                </form>

                <form action="" method="post" class="text-center mt-2">
                    <button class="btn btn-link p-0" type="submit" name="resend">Hantar Semula Kod OTP</button>
                </form>
            </div>

            <div class="card-footer bg-white text-center py-3">
                <p class="mb-0"><a href="index.php" class="text-muted">&larr; Kembali ke Log Masuk</a></p>
            </div>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', function (e) {
            var pw = document.getElementById('new_password').value;
            var cpw = document.getElementById('confirm_password').value;
            var warn = document.getElementById('pwMismatch');

            if (pw !== cpw) {
                e.preventDefault();
                warn.style.display = 'block';
            } else {
                warn.style.display = 'none';
            }
        });
    </script>
</body>

</html>