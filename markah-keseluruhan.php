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
  k.tajuk AS kumpulan_tajuk,
  u2.nama_pelajar AS ketua_kumpulan,
  k.nama_penyelia AS nama_penyelia,
  SUM(m.markah) AS total_markah,
  GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS panel_names
FROM penilaian p
INNER JOIN kumpulan k ON k.id_kumpulan = p.id_kumpulan
LEFT JOIN markah m ON m.id_penilaian = p.id_penilaian
LEFT JOIN users u ON p.id_penilai = u.id
LEFT JOIN pelajar u2 ON k.id_ketua_kumpulan = u2.id_pelajar
GROUP BY k.id_kumpulan
ORDER BY total_markah DESC
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
              <h5 class="card-header">Senarai Markah Keseluruhan</h5>
 
              <div class="card-body">
                <div class="table-responsive">
                  <?php
                  $kumpulanData = [];
 
                  while ($row = $result->fetch_assoc()) {
                    $kumpulanData[] = [
                      'tajuk' => $row['kumpulan_tajuk'],
                      'ketua' => $row['ketua_kumpulan'],
                      'penyelia' => $row['nama_penyelia'],
                      'markah' => number_format($row['total_markah'] / 4, 2),
                      'panels' => $row['panel_names'] // This is fetched correctly in the query
                    ];
                  }
                  ?>
 
                  <table class="table table-striped table-bordered ranking-table">
                    <thead class="thead-dark">
                      <tr>
                        <th>Ranking</th>
                        <th>Booth / Kumpulan</th>
                        <th>Ketua Kumpulan</th>
                        <th>Nama Penyelia PTA</th>
                        <th>Panel Yang Menilai</th>
                        <th>Jumlah Keseluruhan Markah Dinilai</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $ranking = 1; ?>
                      <?php foreach ($kumpulanData as $item): ?>
                        <tr class="<?= $ranking <= 3 ? 'rank-row-' . $ranking : '' ?>">
                          <td>
                            <?php
                            // Add badge styling for top 3
                            switch ($ranking) {
                              case 1:
                                echo '<span class="rank-medal rank-gold">🥇 <span>Emas</span></span>';
                                break;
                              case 2:
                                echo '<span class="rank-medal rank-silver">🥈 <span>Perak</span></span>';
                                break;
                              case 3:
                                echo '<span class="rank-medal rank-bronze">🥉 <span>Gangsa</span></span>';
                                break;
                              default:
                                echo '<span class="rank-number">' . $ranking . '</span>';
                            }
                            $ranking++;
                            ?>
                          </td>
                          <td><strong><?= htmlspecialchars($item['tajuk']) ?></strong></td>
                          <td><?= htmlspecialchars($item['ketua']) ?></td>
                          <td><?= htmlspecialchars($item['penyelia']) ?></td>
                          <td>
                            <?php
                            if (!empty($item['panels'])):
                              $panelNames = explode(', ', $item['panels']);
                            ?>
                              <div class="panel-chips">
                                <?php foreach ($panelNames as $panel): ?>
                                  <span class="panel-chip"><?= htmlspecialchars($panel) ?></span>
                                <?php endforeach; ?>
                              </div>
                            <?php else: ?>
                              <span class="status-pill status-pending">Tiada Penilai</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?= $item['markah'] > 0
                              ? '<span class="markah-value">' . htmlspecialchars($item['markah']) . '</span>'
                              : '<span class="status-pill status-danger">Belum Dinilai</span>' ?>
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
 
 
  <style>
    .ranking-table td,
    .ranking-table th {
      vertical-align: middle;
    }
 
    .rank-row-1 {
      background-color: #fffbeb !important;
    }
 
    .rank-row-2 {
      background-color: #f8fafc !important;
    }
 
    .rank-row-3 {
      background-color: #fdf4ec !important;
    }
 
    .rank-medal {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 50px;
      font-size: 12.5px;
      font-weight: 700;
      white-space: nowrap;
    }
 
    .rank-gold {
      background-color: #fef3c7;
      color: #92400e;
    }
 
    .rank-silver {
      background-color: #e5e7eb;
      color: #374151;
    }
 
    .rank-bronze {
      background-color: #fed7aa;
      color: #9a3412;
    }
 
    .rank-number {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background-color: #f1f3f9;
      color: #4b5563;
      font-weight: 700;
      font-size: 13px;
    }
 
    .panel-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
    }
 
    .panel-chip {
      background-color: #eef2ff;
      color: #4f46e5;
      border-radius: 50px;
      padding: 3px 12px;
      font-size: 12px;
      font-weight: 500;
      white-space: nowrap;
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
 
    .status-pending {
      background-color: #f1f3f9;
      color: #6b7280;
    }
 
    .markah-value {
      font-size: 15px;
      font-weight: 700;
      color: #16a34a;
    }
  </style>
</body>
 
</html>
 