<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";

//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}


$query = "
SELECT 
  k.id_kumpulan,
  k.tajuk AS kumpulan_tajuk,
  u2.nama_pelajar AS ketua_kumpulan,
  k.nama_penyelia AS nama_penyelia,
  u.name AS panel_name,
  m.markah AS markah_diberi
FROM penilaian p
INNER JOIN kumpulan k ON k.id_kumpulan = p.id_kumpulan
LEFT JOIN markah m ON m.id_penilaian = p.id_penilaian
LEFT JOIN users u ON p.id_penilai = u.id
LEFT JOIN pelajar u2 ON k.id_ketua_kumpulan = u2.id_pelajar
ORDER BY k.tajuk, u.name


";
$result = $conn->query($query);



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
              <h2 class="pageheader-title">Markah Keseluruhan</h2>

              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Markah Keseluruhan</li>
                  </ol>
                </nav>
              </div>
            </div>


            <?php if (!empty($_SESSION['success'])): ?>
              <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                  </div>
                </div>
              </div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>


            <?php if (!empty($_SESSION['error'])): ?>
              <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                  </div>
                </div>
              </div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

          </div>
        </div>
        <!-- ============================================================== -->
        <!-- end pageheader -->
        <!-- ============================================================== -->
        <div class="row">


          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
              <h5 class="card-header">Senarai Markah Keseluruhan </h5>

              <div class="card-body">
                <div class="table-responsive">
                  <?php
                  $kumpulanData = [];

                  while ($row = $result->fetch_assoc()) {
                    $id = $row['id_kumpulan'];

                    if (!isset($kumpulanData[$id])) {
                      $kumpulanData[$id] = [
                        'tajuk' => $row['kumpulan_tajuk'],
                        'ketua' => $row['ketua_kumpulan'],
                        'penyelia' => $row['nama_penyelia'],
                        'markah' => 0,
                        'penilai' => []
                      ];
                    }

                    // Add panel name with individual mark
                    if (!empty($row['panel_name'])) {
                      $kumpulanData[$id]['penilai'][] = [
                        'name' => $row['panel_name'],
                        'mark' => $row['markah_diberi']
                      ];
                    }

                    // Sum up markah (if any)
                    if (!empty($row['markah_diberi'])) {
                      $kumpulanData[$id]['markah'] += $row['markah_diberi'];
                    }
                  }
                  ?>
                  <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                      <tr>
                        <th>Booth / Kumpulan</th>
                        <th>Ketua Kumpulan</th>
                        <th>Nama Penyelia PTA</th>
                        <th>Jumlah Keseluruhan Markah Dinilai</th>
                        <th>Panel Yang Menilai</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($kumpulanData as $item): ?>
                        <tr>
                          <td><?= htmlspecialchars($item['tajuk']) ?></td>
                          <td><?= htmlspecialchars($item['ketua']) ?></td>
                          <td><?= htmlspecialchars($item['penyelia']) ?></td>
                          <td>
                            <?= $item['markah'] > 0
                              ? htmlspecialchars($item['markah'])
                              : '<span class="badge badge-danger">Belum Dinilai</span>' ?>
                          </td>
                          <td>
                            <?php if (!empty($item['penilai'])): ?>
                              <ul class="mb-0 pl-3">
                                <?php foreach ($item['penilai'] as $p): ?>
                                  <li>
                                    <?= htmlspecialchars($p['name']) ?>
                                    <?= $p['mark'] !== null ? "(<strong>{$p['mark']}</strong>)" : '<span class="badge badge-danger">Belum Dinilai</span>' ?>
                                  </li>
                                <?php endforeach; ?>
                              </ul>
                            <?php else: ?>
                              <span class="badge badge-secondary">Tiada Penilai</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>





                </div>
              </div>
            </div>
          </div>
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

  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/vendor/datatables/js/data-table.js"></script>


  <script>
  </script>
</body>

</html>