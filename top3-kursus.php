<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";

//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}

include "includes/ranking-helper.php";
$kumpulanKursus = kumpulkanIkutKursus(ambilRanking($conn, 'keseluruhan'));
?>

<body>
  <div class="dashboard-main-wrapper">
    <?php
    include "includes/navbar.php";
    include "includes/leftbar.php";
    ?>

    <div class="dashboard-wrapper">
      <div class="container-fluid dashboard-content">
        <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
              <h2 class="pageheader-title">Top 3 Mengikut Kursus</h2>
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item">Markah</li>
                    <li class="breadcrumb-item active" aria-current="page">Top 3 Mengikut Kursus</li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <?php foreach ($kumpulanKursus as $namaKursus => $senarai): ?>
              <div class="card">
                <h5 class="card-header">Kursus: <?= htmlspecialchars($namaKursus) ?></h5>
                <div class="card-body">
                  <?php paparTabelRanking($senarai, 100, 3); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>

  <?php include "includes/ranking-style.php"; ?>
</body>

</html>
