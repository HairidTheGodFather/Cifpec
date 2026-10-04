<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";



$countResultHakim = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role ='hakim'");
$countRowHakim = $countResultHakim->fetch_assoc();
$totalHakim = $countRowHakim['total'];

$countResultKumpulan = $conn->query("SELECT COUNT(*) AS total FROM kumpulan");
$countRowKumpulan = $countResultKumpulan->fetch_assoc();
$totalKumpulan = $countRowKumpulan['total'];

$countResultPelajar = $conn->query("SELECT COUNT(*) AS total FROM pelajar");
$countRowPelajar = $countResultPelajar->fetch_assoc();
$totalPelajar = $countRowPelajar['total'];

$countResultPenilaian = $conn->query("SELECT COUNT(*) AS total FROM penilaian WHERE id_penilai = '" . $_SESSION['id'] . "'");
$countRowPenilaian = $countResultPenilaian->fetch_assoc();
$totalPenilaian = $countRowPenilaian['total'];

?>

<body>
  <!-- ============================================================== -->
  <!-- main wrapper -->
  <!-- ============================================================== -->
  <div class="dashboard-main-wrapper">
    <?php
    include "includes/navbar.php";
    include "includes/leftbar.php";

    ?>

    <!-- ============================================================== -->
    <!-- wrapper  -->
    <!-- ============================================================== -->
    <div class="dashboard-wrapper">
      <div class="container-fluid dashboard-content">
        <!-- ============================================================== -->
        <!-- pageheader -->
        <!-- ============================================================== -->
        <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
              <h2 class="pageheader-title">Halaman Utama <?= strtoupper(htmlspecialchars($_SESSION['name'])) ?></h2>

              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <!-- <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li> -->
                    <li class="breadcrumb-item active" aria-current="page">Halaman Utama</li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- end pageheader -->
        <!-- ============================================================== -->
        <div class="row">
          <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <div class="card">
                <div class="card-body">
                  <h5 class="text-muted">Jumlah Booth / Kumpulan</h5>
                  <div class="metric-value d-inline-block">
                    <h1 class="mb-1"><?= $totalKumpulan ?></h1>
                  </div>
                </div>

              </div>

          </div>
          <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="text-muted">Jumlah Panel / Hakim</h5>
                <div class="metric-value d-inline-block">
                  <h1 class="mb-1"><?= $totalHakim ?></h1>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="text-muted">Jumlah Peserta</h5>
                  <div class="metric-value d-inline-block">
                    <h1 class="mb-1"><?= $totalPelajar ?></h1>
                  </div>
                </div>

              </div>

        <?php endif; ?>


        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'hakim'): ?>
          <div class="card">
            <div class="card-body">
              <h5 class="text-muted">Jumlah Booth / Kumpulan Ditugaskan Untuk Menilai</h5>
              <div class="metric-value d-inline-block">
                <h1 class="mb-1"><?= $totalPenilaian ?></h1>
              </div>
            </div>

          </div>

        </div>
      <?php endif; ?>


      </div>
    </div>
    <?php
    // include "includes/footer.php" 
    ?>
  </div>
  <!-- ============================================================== -->
  <!-- end main wrapper -->
  <!-- ============================================================== -->
  </div>
  <!-- ============================================================== -->
  <!-- end main wrapper -->
  <!-- ============================================================== -->
  <!-- Optional JavaScript -->
  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>
</body>

</html>