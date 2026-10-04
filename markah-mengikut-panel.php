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
    u.id AS user_id,
    u.name,
    u.email,
    k.tajuk AS kumpulan_tajuk,
    SUM(m.markah) AS total_markah
  FROM users u
  INNER JOIN penilaian p ON u.id = p.id_penilai
  INNER JOIN kumpulan k ON k.id_kumpulan = p.id_kumpulan
  LEFT JOIN markah m ON m.id_penilaian = p.id_penilaian
  WHERE u.role = 'hakim'
  GROUP BY p.id_penilaian
  ORDER BY u.name
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
              <h2 class="pageheader-title">Markah Mengikut Panel / Hakim</h2>
 
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Markah Mengikut Panel / Hakim</li>
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
              <h5 class="card-header">Senarai Markah Mengikut Panel / Hakim </h5>
 
              <div class="card-body">
                <div class="table-responsive">
                  <?php
                  $data = [];
                  while ($row = $result->fetch_assoc()) {
                    $data[$row['user_id']]['name'] = $row['name'];
                    $data[$row['user_id']]['email'] = $row['email'];
                    $data[$row['user_id']]['penilaian'][] = [
                      'kumpulan_tajuk' => $row['kumpulan_tajuk'],
                      'total_markah' => $row['total_markah'],
                    ];
                  }
                  ?>
 
                  <table class="table table-bordered panel-markah-table">
                    <thead class="thead-dark">
                      <tr>
                        <th style="width: 25%;">Nama Panel / Hakim</th>
                        <th>Booth / Kumpulan Yang Dinilai</th>
                        <th>Markah</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($data as $panel): ?>
                        <?php $penilaian = $panel['penilaian']; ?>
                        <?php foreach ($penilaian as $index => $p): ?>
                          <tr>
                            <?php if ($index === 0): ?>
                              <td rowspan="<?= count($penilaian) ?>" class="panel-name-cell">
                                <div class="panel-name"><?= htmlspecialchars($panel['name']) ?></div>
                                <div class="panel-email"><?= htmlspecialchars($panel['email']) ?></div>
                              </td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($p['kumpulan_tajuk']) ?></td>
                            <td>
                              <?php if (!empty($p['total_markah'])): ?>
                                <span class="markah-value"><?= htmlspecialchars($p['total_markah']) ?></span>
                              <?php else: ?>
                                <span class="status-pill status-danger">Belum Dinilai</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
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
 
 
  <style>
    .panel-markah-table td,
    .panel-markah-table th {
      vertical-align: middle;
    }
 
    .panel-name-cell {
      background-color: #fafbfc;
    }
 
    .panel-name {
      font-weight: 600;
      color: #1f2937;
    }
 
    .panel-email {
      font-size: 12.5px;
      color: #9ca3af;
      margin-top: 2px;
    }
 
    .markah-value {
      font-size: 15px;
      font-weight: 700;
      color: #16a34a;
    }
 
    .status-pill {
      display: inline-flex;
      align-items: center;
      padding: 5px 14px;
      border-radius: 50px;
      font-size: 12px;
      font-weight: 600;
    }
 
    .status-danger {
      background-color: #fef2f2;
      color: #ef4444;
    }
  </style>
</body>
 
</html>
 