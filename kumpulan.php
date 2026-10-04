<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";
 
//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
  // UPDATE
  if (isset($_POST['submit_edit'])) {
    $id = $_POST['id_kumpulan'];
    $tajuk = $_POST['tajuk'];
    $nama_penyelia = $_POST['nama_penyelia'];
 
    $stmt = $conn->prepare("UPDATE kumpulan SET tajuk=?, nama_penyelia=? WHERE id_kumpulan=?");
    $stmt->bind_param("ssi", $tajuk, $nama_penyelia, $id);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Kumpulan berjaya dikemaskini!";
    } else {
      $_SESSION['error'] = "Ralat kemaskini: " . $stmt->error;
    }
    $stmt->close();
    header("Location: kumpulan.php");
    exit;
  }
 
  // INSERT
  if (isset($_POST['tajuk']) && isset($_POST['nama_penyelia']) && isset($_POST['id_ketua_kumpulan'])) {
    $tajuk = $_POST['tajuk'];
    $nama_penyelia = $_POST['nama_penyelia'];
    $id_ketua_kumpulan = $_POST['id_ketua_kumpulan'];
 
    $sql = "INSERT INTO kumpulan (tajuk, nama_penyelia, id_ketua_kumpulan) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $tajuk, $nama_penyelia, $id_ketua_kumpulan);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Kumpulan berjaya ditambah!";
    } else {
      $_SESSION['error'] = "Ralat tambah: " . $stmt->error;
    }
    $stmt->close();
    header("Location: kumpulan.php");
    exit;
  }
}
 
 
// DELETE: Delete kumpulan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_delete'])) {
  $idToDelete = $_POST['delete_id_kumpulan'] ?? null;
 
  if ($idToDelete) {
    $stmt = $conn->prepare("DELETE FROM kumpulan WHERE id_kumpulan = ?");
    $stmt->bind_param("i", $idToDelete);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Kumpulan berjaya dihapuskan!";
    } else {
      $_SESSION['error'] = "Ralat hapus: " . $stmt->error;
    }
    $stmt->close();
    header("Location: kumpulan.php");
    exit;
  }
}
 
 
$query = "SELECT * FROM kumpulan JOIN pelajar WHERE kumpulan.id_ketua_kumpulan = pelajar.id_pelajar";
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
              <h2 class="pageheader-title">Kumpulan</h2>
 
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Kumpulan</li>
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
 
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- end pageheader -->
        <!-- ============================================================== -->
        <div class="row">
 
 
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
              <h5 class="card-header">Senarai Kumpulan <a href="#" class="btn btn-primary btn-sm btn-rounded" data-toggle="modal" data-target="#tambahKumpulan">
                  + Tambah Kumpulan
                </a></h5>
 
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered first">
                    <thead>
                      <tr>
                        <th>Tajuk Reka Cipta</th>
                        <th>Nama Penyelia PTA</th>
                        <th>Ketua Kumpulan</th>
                        <th class="text-center">Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
 
                          <td><?= $row['tajuk'] ?></td>
                          <td><?= $row['nama_penyelia'] ?></td>
                          <td><?= $row['nama_pelajar'] ?></td>
                          <td class="text-center">
                            <div class="action-buttons">
                              <a href="#" class="btn-action btn-edit" data-toggle="modal" data-target="#editKumpulan<?= $row['id_kumpulan'] ?>" title="Kemaskini">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Kemaskini</span>
                              </a>
 
                              <form method="POST" onsubmit="return confirm('Padam kumpulan ini?')">
                                <input type="hidden" name="delete_id_kumpulan" value="<?= $row['id_kumpulan'] ?>">
                                <button type="submit" name="submit_delete" class="btn-action btn-delete" title="Hapus">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                  </svg>
                                  <span>Hapus</span>
                                </button>
                              </form>
                            </div>
                          </td>
 
                        </tr>
 
 
                        <!-- Modal Edit Kumpulan -->
                        <div class="modal fade" id="editKumpulan<?= $row['id_kumpulan'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $row['id_kumpulan'] ?>" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <form action="" method="POST">
                                <input type="hidden" name="id_kumpulan" value="<?= $row['id_kumpulan'] ?>">
 
                                <div class="modal-header">
                                  <h5 class="modal-title" id="editModalLabel<?= $row['id_kumpulan'] ?>">Kemaskini Kumpulan</h5>
                                  <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </a>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="id_kumpulan" value="<?= $row['id_kumpulan'] ?>">
 
                                  <div class="form-group">
                                    <label for="tajuk">Tajuk Reka Cipta</label>
                                    <input name="tajuk" type="text" value="<?= htmlspecialchars($row['tajuk']) ?>" class="form-control" required>
                                  </div>
 
                                  <div class="form-group">
                                    <label for="nama_penyelia">Nama Penyelia PTA</label>
                                    <input name="nama_penyelia" type="text" value="<?= htmlspecialchars($row['nama_penyelia']) ?>" class="form-control" required>
                                  </div>
 
                                  <div class="form-group">
                                    <label for="id_ketua_kumpulan">Ketua Kumpulan</label>
                                    <select class="form-control" name="id_ketua_kumpulan" required>
                                      <option value="" selected disabled>-- Sila Pilih Ketua Kumpulan --</option>
                                      <?php
                                      $pelajarList = $conn->query("SELECT * FROM pelajar");
                                      while ($pel = $pelajarList->fetch_assoc()):
                                        $selected = ($pel['id_pelajar'] == $row['id_ketua_kumpulan']) ? 'selected' : '';
                                      ?>
                                        <option value="<?= $pel['id_pelajar'] ?>" <?= $selected ?>><?= $pel['nama_pelajar'] ?></option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
 
 
                                </div>
                                <div class="modal-footer">
                                  <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
                                  <button type="submit" name="submit_edit" class="btn btn-primary">Kemaskini</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
 
 
 
                      <?php endwhile; ?>
 
 
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
 
 
 
 
  <!-- Modal  Tambah-->
  <div class="modal fade" id="tambahKumpulan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Kumpulan</h5>
            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </a>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="tajuk">Tajuk Reka Cipta</label>
              <input name="tajuk" type="text" placeholder="" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="nama_penyelia">Nama Penyelia PTA</label>
              <input name="nama_penyelia" type="text" placeholder="" class="form-control" required>
            </div>
 
            <div class="form-group">
              <label for="id_ketua_kumpulan">Ketua Kumpulan</label>
              <select class="form-control" name="id_ketua_kumpulan" required>
                <option value="" selected disabled>-- Sila Pilih Ketua Kumpulan --</option>
                <?php
                $pelajarList = $conn->query("SELECT * FROM pelajar");
                while ($pel = $pelajarList->fetch_assoc()):
                ?>
                  <option value="<?= $pel['id_pelajar'] ?>"><?= $pel['nama_pelajar'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
 
          </div>
          <div class="modal-footer">
            <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
 
 
 
  <style>
    .action-buttons {
      display: flex;
      gap: 8px;
      justify-content: center;
      align-items: center;
    }
 
    .action-buttons form {
      display: inline-flex;
      margin: 0;
    }
 
    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border: none;
      border-radius: 50px;
      font-size: 12.5px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color .15s ease, color .15s ease, transform .1s ease;
      text-decoration: none;
      line-height: 1.4;
    }
 
    .btn-action:hover {
      transform: translateY(-1px);
      text-decoration: none;
    }
 
    .btn-action svg {
      flex-shrink: 0;
    }
 
    .btn-edit {
      background-color: #eef2ff;
      color: #4f46e5;
    }
 
    .btn-edit:hover {
      background-color: #4f46e5;
      color: #ffffff;
    }
 
    .btn-delete {
      background-color: #fef2f2;
      color: #ef4444;
    }
 
    .btn-delete:hover {
      background-color: #ef4444;
      color: #ffffff;
    }
  </style>
 
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