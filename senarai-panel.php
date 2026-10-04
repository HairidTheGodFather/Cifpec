<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";
 
//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}
 
// Halaman mana: ?jenis=poster atau ?jenis=projek
$jenisHalaman = (isset($_GET['jenis']) && $_GET['jenis'] === 'projek') ? 'projek' : 'poster';
$tajukHalaman = $jenisHalaman === 'poster' ? 'Senarai Panel untuk Poster' : 'Senarai Hakim untuk Projek';
 
// Poster guna jadual penilaian_poster, projek guna jadual penilaian
$tblPenilaian = $jenisHalaman === 'poster' ? 'penilaian_poster' : 'penilaian';
 
if (isset($_POST['submit_assign'])) {
  $id_penilai = (int) $_POST['id_penilai'];
  $id_kumpulan = (int) $_POST['id_ketua_kumpulan']; // ini sebenarnya id kumpulan
 
  // Semak sama ada tugasan sudah wujud
  $check = $conn->prepare("SELECT * FROM $tblPenilaian WHERE id_penilai = ? AND id_kumpulan = ?");
  $check->bind_param("ii", $id_penilai, $id_kumpulan);
  $check->execute();
  $result = $check->get_result();
 
  if ($result->num_rows > 0) {
    $_SESSION['error'] = "Penilai sudah ditugaskan kepada kumpulan ini.";
  } else {
    $stmt = $conn->prepare("INSERT INTO $tblPenilaian (id_penilai, id_kumpulan) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_penilai, $id_kumpulan);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Penilai berjaya ditugaskan!";
    } else {
      $_SESSION['error'] = "Ralat ketika menyimpan: " . $stmt->error;
    }
    $stmt->close();
  }
 
  $check->close();
  header("Location: senarai-panel.php?jenis=" . $jenisHalaman);
  exit;
}
 
if (isset($_POST['remove_penilaian'])) {
  $id_penilai = (int) $_POST['id_penilai'];
  $id_kumpulan = (int) $_POST['id_kumpulan'];
  $del = $conn->prepare("DELETE FROM $tblPenilaian WHERE id_penilai = ? AND id_kumpulan = ?");
  $del->bind_param("ii", $id_penilai, $id_kumpulan);
  $del->execute();
  $del->close();
  header("Location: senarai-panel.php?jenis=" . $jenisHalaman);
  exit;
}
 
$query = "
  SELECT 
    u.*, 
    GROUP_CONCAT(CONCAT(k.id_kumpulan, '::', k.tajuk) SEPARATOR ',') AS kumpulan_data
  FROM users u
  LEFT JOIN $tblPenilaian p ON u.id = p.id_penilai
  LEFT JOIN kumpulan k ON p.id_kumpulan = k.id_kumpulan
  WHERE u.role = 'hakim'
  GROUP BY u.id
";
$result = $conn->query($query);
 
$senaraiHakim = [];
while ($h = $result->fetch_assoc()) {
  $senaraiHakim[] = $h;
}
 
// Senarai kumpulan (sekali sahaja, untuk dropdown modal)
$kumpulanAll = [];
$kq = $conn->query("SELECT * FROM kumpulan");
while ($k = $kq->fetch_assoc()) {
  $kumpulanAll[] = $k;
}
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
              <h2 class="pageheader-title"><?= $tajukHalaman ?></h2>
 
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $tajukHalaman ?></li>
                  </ol>
                </nav>
              </div>
            </div>
 
            <?php if (!empty($_SESSION['success'])): ?>
              <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex"><div><?= htmlspecialchars($_SESSION['success']) ?></div></div>
              </div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
 
            <?php if (!empty($_SESSION['error'])): ?>
              <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex"><div><?= htmlspecialchars($_SESSION['error']) ?></div></div>
              </div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
          </div>
        </div>
 
        <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
              <h5 class="card-header"><?= $tajukHalaman ?> (<?= count($senaraiHakim) ?>)</h5>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered first">
                    <thead>
                      <tr>
                        <th>Nama Panel / Hakim</th>
                        <th>Email</th>
                        <th>Booth Perlu Dinilai</th>
                        <th class="text-center">Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($senaraiHakim as $row): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['name']) ?></td>
                          <td><?= htmlspecialchars($row['email']) ?></td>
                          <td>
                            <?php if ($row['kumpulan_data']): ?>
                              <?php
                              foreach (explode(',', $row['kumpulan_data']) as $kumpulanItem):
                                list($id_kumpulan, $tajuk) = explode('::', $kumpulanItem);
                              ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                  <span><?= htmlspecialchars($tajuk) ?></span>
                                  <form method="POST" action="" onsubmit="return confirm('Padam data ini?')">
                                    <input type="hidden" name="id_penilai" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="id_kumpulan" value="<?= $id_kumpulan ?>">
                                    <button type="submit" name="remove_penilaian" class="btn-action btn-delete ml-2">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                      </svg>
                                      <span>Buang</span>
                                    </button>
                                  </form>
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <span class="text-muted">Tiada</span>
                            <?php endif; ?>
                          </td>
 
                          <td class="text-center">
                            <a href="#" class="btn-action btn-assign" data-toggle="modal" data-target="#assignPenilaian<?= $row['id'] ?>" title="Assign Penilai">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <line x1="19" y1="8" x2="19" y2="14"></line>
                                <line x1="22" y1="11" x2="16" y2="11"></line>
                              </svg>
                              <span>Assign Penilai</span>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
 
                <?php foreach ($senaraiHakim as $row): ?>
                  <!-- Modal -->
                  <div class="modal fade" id="assignPenilaian<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form action="" method="POST">
                          <div class="modal-header">
                            <h5 class="modal-title">Assign Penilai Booth / Kumpulan (<?= $jenisHalaman === 'poster' ? 'Poster' : 'Projek' ?>)</h5>
                            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </a>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_penilai" value="<?= $row['id'] ?>">
                            <div class="form-group">
                              <label>Booth / Kumpulan</label>
                              <select class="form-control" name="id_ketua_kumpulan" required>
                                <option value="" selected disabled>-- Sila Pilih Booth --</option>
                                <?php foreach ($kumpulanAll as $pel): ?>
                                  <option value="<?= $pel['id_kumpulan'] ?>"><?= htmlspecialchars($pel['tajuk']) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
                            <button type="submit" name="submit_assign" class="btn btn-success">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
 
  <style>
    .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: none; border-radius: 50px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background-color .15s ease, color .15s ease, transform .1s ease; text-decoration: none; line-height: 1.4; }
    .btn-action:hover { transform: translateY(-1px); text-decoration: none; }
    .btn-action svg { flex-shrink: 0; }
    .btn-delete { background-color: #fef2f2; color: #ef4444; }
    .btn-delete:hover { background-color: #ef4444; color: #ffffff; }
    .btn-assign { background-color: #eef2ff; color: #4f46e5; }
    .btn-assign:hover { background-color: #4f46e5; color: #ffffff; }
  </style>
 
  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>
 
  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/vendor/datatables/js/data-table.js"></script>
</body>
 
</html>