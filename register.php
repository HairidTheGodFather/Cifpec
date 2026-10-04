<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';
session_start();

function generateOTP() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // 1. Semak kata laluan sepadan
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Kata Laluan dan Sahkan Kata Laluan tidak sama!';
        header("Location: register.php");
        exit();
    }

    // 2. Semak panjang kata laluan minimum
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Kata Laluan mesti sekurang-kurangnya 6 aksara!';
        header("Location: register.php");
        exit();
    }

    // 3. Semak email sudah wujud (guna prepared statement)
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email is already registered!';
        $stmt->close();
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // 4. JANGAN masukkan ke DB dahulu — simpan sementara dalam session, hantar OTP
    $otp = generateOTP();
    $_SESSION['pending_name']     = $name;
    $_SESSION['pending_email']    = $email;
    $_SESSION['pending_password'] = password_hash($password, PASSWORD_DEFAULT);
    $_SESSION['otp_code']         = $otp;
    $_SESSION['otp_expiry']       = time() + 300; // 5 minit
    $_SESSION['otp_attempts']     = 0;

    $sent = sendOTPEmail($email, $name, $otp);

    if ($sent) {
        header("Location: verify_otp.php");
        exit();
    } else {
        $_SESSION['error'] = 'Gagal menghantar OTP. Sila cuba lagi sebentar.';
        header("Location: register.php");
        exit();
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Akaun - CIFPECScore</title>

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
            max-width: 450px;
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

        .card-header h2 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-size: 18px;
            color: #334155;
            font-weight: 600;
        }

        .card-header p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 0;
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
                <h3 class="mb-1">Daftar Akaun</h3>
                <p>Sila masukkan maklumat anda.</p>
            </div>

            <div class="card-body">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                    <path d="M12 8v4" />
                                    <path d="M12 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <div class="d-flex">
                            <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="" method="post" id="registerForm">
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="text" name="name" required placeholder="Nama Penuh" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="email" name="email" required placeholder="E-mail" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="password" id="password" type="password" required placeholder="Kata Laluan" minlength="6">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" name="confirm_password" id="confirm_password" type="password" required placeholder="Sahkan Kata Laluan" minlength="6">
                        <small class="text-danger" id="pwMismatch" style="display:none;">Kata laluan tidak sama.</small>
                    </div>
                    <div class="form-group pt-2">
                        <button class="btn btn-block btn-primary" type="submit" name="register">Daftar Akaun</button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white text-center py-3">
                <p class="mb-0">Sudah mempunyai akaun? <a href="index.php" class="text-primary font-weight-bold">Log Masuk di sini.</a></p>
            </div>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            var pw = document.getElementById('password').value;
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