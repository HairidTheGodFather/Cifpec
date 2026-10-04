<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";
 
//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
  // UPDATE first
  if (isset($_POST['submit_edit'])) {
    $id = $_POST['id_pelajar'];
    $nama = $_POST['nama_pelajar'];
    $bidang = $_POST['bidang'];
    $kursus = $_POST['kursus'];
    $ahli_kumpulan = $_POST['ahli_kumpulan'];
 
    $stmt = $conn->prepare("UPDATE pelajar SET nama_pelajar=?, bidang=?, kursus=?, ahli_kumpulan=? WHERE id_pelajar=?");
    $stmt->bind_param("ssssi", $nama, $bidang, $kursus, $ahli_kumpulan, $id);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya dikemaskini!";
    } else {
      $_SESSION['error'] = "Ralat kemaskini: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }
 
  // INSERT fallback
  if (isset($_POST['nama_pelajar']) && isset($_POST['bidang']) && isset($_POST['kursus']) && isset($_POST['ahli_kumpulan'])) {
    $nama = $_POST['nama_pelajar'];
    $bidang = $_POST['bidang'];
    $kursus = $_POST['kursus'];
    $ahli_kumpulan = $_POST['ahli_kumpulan'];
 
    $sql = "INSERT INTO pelajar (nama_pelajar, bidang, kursus,ahli_kumpulan) VALUES (?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $bidang, $kursus, $ahli_kumpulan);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya ditambah!";
    } else {
      $_SESSION['error'] = "Ralat tambah: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }
}
 
 
// DELETE: Delete pelajar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_delete'])) {
  $idToDelete = $_POST['delete_id_pelajar'] ?? null;
 
  if ($idToDelete) {
    $stmt = $conn->prepare("DELETE FROM pelajar WHERE id_pelajar = ?");
    $stmt->bind_param("i", $idToDelete);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya dihapuskan!";
    } else {
      $_SESSION['error'] = "Ralat hapus: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }
}
 
 
$query = "SELECT * FROM pelajar";
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
              <h2 class="pageheader-title">Pelajar</h2>
 
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Pelajar</li>
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

            <!-- BARU: mesej ralat (untuk import & operasi lain) -->
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
              <h5 class="card-header">Senarai Pelajar <a href="#" class="btn btn-primary btn-sm btn-rounded" data-toggle="modal" data-target="#tambahPelajar">
                  + Tambah Pelajar
                </a>
                <!-- BARU: butang Import CSV -->
                <a href="#" class="btn btn-success btn-sm btn-rounded" data-toggle="modal" data-target="#importPelajar">
                  Import CSV
                </a></h5>
 
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered first">
                    <thead>
                      <tr>
                        <th>Nama</th>
                        <th>Bidang</th>
                        <th>Kursus</th>
                        <th>Ahli Kumpulan</th>
                        <th class="text-center">Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
 
                          <td><?= $row['nama_pelajar'] ?></td>
                          <td><?= $row['bidang'] ?></td>
                          <td><?= $row['kursus'] ?></td>
                          <td><?= $row['ahli_kumpulan'] ?></td>
                          <td class="text-center">
                            <div class="action-buttons">
                              <a href="#" class="btn-action btn-edit" data-toggle="modal" data-target="#editPelajar<?= $row['id_pelajar'] ?>" title="Kemaskini">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Kemaskini</span>
                              </a>
 
                              <form method="POST" onsubmit="return confirm('Padam pelajar ini?')">
                                <input type="hidden" name="delete_id_pelajar" value="<?= $row['id_pelajar'] ?>">
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
 
 
                        <!-- Modal Edit Pelajar -->
                        <div class="modal fade" id="editPelajar<?= $row['id_pelajar'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $row['id_pelajar'] ?>" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <form action="" method="POST">
                                <input type="hidden" name="id_pelajar" value="<?= $row['id_pelajar'] ?>">
 
                                <div class="modal-header">
                                  <h5 class="modal-title" id="editModalLabel<?= $row['id_pelajar'] ?>">Kemaskini Pelajar</h5>
                                  <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </a>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="id_pelajar" value="<?= $row['id_pelajar'] ?>">
 
                                  <div class="form-group">
                                    <label for="nama_pelajar">Nama Penuh Pelajar</label>
                                    <input name="nama_pelajar" type="text" value="<?= htmlspecialchars($row['nama_pelajar']) ?>" class="form-control" required>
                                  </div>
 
                                  <div class="form-group">
                                    <label for="bidang">Bidang</label>
                                    <input name="bidang" type="text" value="<?= htmlspecialchars($row['bidang']) ?>" class="form-control" required>
                                  </div>
 
                                  <div class="form-group">
                                    <label for="kursus">Kursus</label>
                                    <input name="kursus" type="text" value="<?= htmlspecialchars($row['kursus']) ?>" class="form-control" required>
                                  </div>
 
                                  <div class="form-group">
                                    <label for="ahli_kumpulan">Ahli Kumpulan</label>
                                    <textarea name="ahli_kumpulan" type="text" value="<?= $row['ahli_kumpulan'] ?>" class="form-control"><?= $row['ahli_kumpulan'] ?></textarea>
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
  <div class="modal fade" id="tambahPelajar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Pelajar</h5>
            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </a>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="nama_pelajar">Nama Penuh Pelajar</label>
              <input name="nama_pelajar" type="text" placeholder="Masukkan Nama Penuh Pelajar" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="bidang">Bidang</label>
              <input name="bidang" type="text" placeholder="Masukkan Bidang" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="kursus">Kursus</label>
              <input name="kursus" type="text" placeholder="Kursus" class="form-control" required>
            </div>
 
            <div class="form-group">
              <label for="ahli_kumpulan">Ahli Kumpulan</label>
              <textarea name="ahli_kumpulan" type="text" class="form-control"></textarea>
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


  <!-- BARU: Modal Import CSV -->
  <div class="modal fade" id="importPelajar" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="import_pelajar.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="importModalLabel">Import Pelajar (CSV)</h5>
            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </a>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <a href="template/template_pelajar.xlsx" download class="btn btn-info btn-sm">Muat Turun Template (Excel)</a>
              <a href="template/template_pelajar.csv" download class="btn btn-outline-info btn-sm">Muat Turun Template (CSV)</a>
            </div>
            <p class="mb-2">
              1. Isi maklumat pelajar, padam baris contoh<br>
              2. Simpan sebagai <strong>CSV UTF-8 (Comma delimited)</strong><br>
              3. Pilih fail CSV di bawah
            </p>
            <div class="form-group">
              <label for="csv">Fail CSV</label>
              <input name="csv" type="file" accept=".csv" class="form-control-file" required>
            </div>
          </div>
          <div class="modal-footer">
            <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
            <button type="submit" class="btn btn-success">Import</button>
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
      position: relative;
      overflow: hidden;
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
 
    .btn-action:active {
      transform: scale(.94);
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
 
    .ripple {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, .6);
      transform: scale(0);
      animation: ripple-effect .5s linear;
      pointer-events: none;
    }
 
    @keyframes ripple-effect {
      to {
        transform: scale(4);
        opacity: 0;
      }
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
    document.querySelectorAll('.btn-action').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        var rect = btn.getBoundingClientRect();
        var size = Math.max(rect.width, rect.height);
        var ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        setTimeout(function() {
          ripple.remove();
        }, 500);
      });
    });
  </script>
</body>
 
</html>