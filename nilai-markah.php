<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";
 
// Soalan 1 = Poster (jadual _poster), Soalan 2 = Projek (jadual biasa)
function jadualSoalan($soalan)
{
  if ($soalan === 1) {
    return ['penilaian' => 'penilaian_poster', 'markah' => 'markah_poster', 'rubrik' => 'rubrik_poster'];
  }
  return ['penilaian' => 'penilaian', 'markah' => 'markah', 'rubrik' => 'rubrik'];
}
 
$maxMarkahKategori = [
  'POSTER PROJEK' => 10,
  'KEASLIAN DAN KREATIVITI' => 25,
  'KEFUNGSIAN PROJEK/APLIKASI' => 20,
  'KUALITI DAN IMPAK' => 20,
  'POTENSI KOMERSIAL' => 5,
  'SLAID PERSEMBAHAN DAN PENYAMPAIAN' => 20
];
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $soalanPost = (isset($_POST['soalan']) && (int) $_POST['soalan'] === 2) ? 2 : 1;
  $tbl = jadualSoalan($soalanPost);
 
  if (isset($_POST['id_penilaian'], $_POST['markah']) && is_array($_POST['markah'])) {
    $id_penilaian = (int) $_POST['id_penilaian'];
    $id_penilai = (int) $_SESSION['id'];
    $markahData = $_POST['markah']; // array: id_rubrik => skala
 
    // Ambil tajuk booth, dan pastikan booth ni memang ditugaskan kepada hakim ini
    $tajuk = null;
    $stmtTajuk = $conn->prepare(
      "SELECT k.tajuk FROM kumpulan k
       INNER JOIN {$tbl['penilaian']} p ON p.id_kumpulan = k.id_kumpulan
       WHERE p.id_penilaian = ? AND p.id_penilai = ?"
    );
    $stmtTajuk->bind_param("ii", $id_penilaian, $id_penilai);
    $stmtTajuk->execute();
    $stmtTajuk->bind_result($tajuk);
    $stmtTajuk->fetch();
    $stmtTajuk->close();
 
    if ($tajuk === null) {
      $_SESSION['error'] = "Booth tidak sah untuk dinilai oleh anda.";
    } else {
      // Semak markah sedia ada dalam jadual markah soalan ini sahaja
      $count = 0;
      $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM {$tbl['markah']} WHERE id_penilaian = ?");
      $stmtCheck->bind_param("i", $id_penilaian);
      $stmtCheck->execute();
      $stmtCheck->bind_result($count);
      $stmtCheck->fetch();
      $stmtCheck->close();
 
      if ($count > 0) {
        $_SESSION['error'] = "Markah untuk penilaian '$tajuk' sudah diberi.";
      } else {
        $rubrikData = [];
        $kategoriCounts = [];
 
        $rubrikStmt = $conn->prepare("SELECT kategori FROM {$tbl['rubrik']} WHERE id_rubrik = ?");
        foreach ($markahData as $id_rubrik => $skala) {
          $kategori = null;
          $rubrikStmt->bind_param("i", $id_rubrik);
          $rubrikStmt->execute();
          $rubrikStmt->bind_result($kategori);
          $rubrikStmt->fetch();
          $rubrikStmt->free_result();
 
          $skala = (int) $skala;
          if ($kategori !== null && isset($maxMarkahKategori[$kategori]) && $skala >= 1 && $skala <= 5) {
            $rubrikData[] = ['id_rubrik' => (int) $id_rubrik, 'skala' => $skala, 'kategori' => $kategori];
            $kategoriCounts[$kategori] = ($kategoriCounts[$kategori] ?? 0) + 1;
          }
        }
        $rubrikStmt->close();
 
        $stmt = $conn->prepare("INSERT INTO {$tbl['markah']} (id_rubrik, id_penilaian, markah) VALUES (?, ?, ?)");
 
        if ($stmt && !empty($rubrikData)) {
          foreach ($rubrikData as $item) {
            $kategori = $item['kategori'];
            $maksimumSkala = $kategoriCounts[$kategori] * 5;
            $markah = round(($item['skala'] / $maksimumSkala) * $maxMarkahKategori[$kategori], 2);
 
            $stmt->bind_param("iid", $item['id_rubrik'], $id_penilaian, $markah);
            $stmt->execute();
          }
          $stmt->close();
          $_SESSION['success'] = "Markah berjaya dihantar untuk '$tajuk'";
        } else {
          $_SESSION['error'] = "Ralat: tiada markah sah untuk disimpan.";
        }
      }
    }
  } else {
    $_SESSION['error'] = "Sila lengkapkan semua maklumat sebelum hantar.";
  }
 
  header("Location: nilai-markah.php?soalan=" . $soalanPost);
  exit;
}
 
// Soalan yang dipilih dari URL
$soalanPilihan = isset($_GET['soalan']) ? (int) $_GET['soalan'] : 1;
if ($soalanPilihan !== 1 && $soalanPilihan !== 2) {
  $soalanPilihan = 1;
}
$tbl = jadualSoalan($soalanPilihan);
 
$kategoriSoalan = [
  1 => ['POSTER PROJEK'],
  2 => [
    'KEASLIAN DAN KREATIVITI',
    'KEFUNGSIAN PROJEK/APLIKASI',
    'KUALITI DAN IMPAK',
    'POTENSI KOMERSIAL',
    'SLAID PERSEMBAHAN DAN PENYAMPAIAN'
  ]
];
$kategoriDipaparkan = $kategoriSoalan[$soalanPilihan];
 
// Booth untuk dropdown: yang ditugaskan kepada hakim ini dan belum dinilai untuk soalan ini
$id_penilai = (int) $_SESSION['id'];
$query = "
  SELECT p.id_kumpulan, kul.tajuk, p.id_penilaian
  FROM {$tbl['penilaian']} p
  JOIN kumpulan kul ON p.id_kumpulan = kul.id_kumpulan
  WHERE p.id_penilai = ?
    AND NOT EXISTS (SELECT 1 FROM {$tbl['markah']} m WHERE m.id_penilaian = p.id_penilaian)
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_penilai);
$stmt->execute();
$boothResult = $stmt->get_result();
?>
 
 
<style>
.table thead th.text-center,
.table tbody td.text-center {
    text-align: center !important;
    vertical-align: middle !important;
}
 
.table tbody td.text-center .custom-control.custom-radio {
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 0;
    margin: 0 auto;
    min-height: 24px;
}
 
.table tbody td.text-center .custom-control-input {
    position: absolute;
    opacity: 0;
}
 
.table tbody td.text-center .custom-control-label {
    padding-left: 0;
    width: 24px;
    height: 24px;
    display: block;
    position: relative;
    cursor: pointer;
}
 
.table tbody td.text-center .custom-control-label::before,
.table tbody td.text-center .custom-control-label::after {
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}
 
.btn-hantar {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
    transition: transform .15s ease, box-shadow .15s ease;
}
 
.btn-hantar:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.45);
    color: #ffffff;
}
 
.btn-hantar:active {
    transform: scale(.96);
}
 
.btn-hantar .ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, .5);
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
              <h2 class="pageheader-title">
                <?= $soalanPilihan === 1 ? 'Soalan 1 - Poster Projek' : 'Soalan 2 - Penilaian Projek' ?>
              </h2>
 
              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Nilai Markah</li>
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
              <h5 class="card-header">
                Penilaian Markah oleh Panel Hakim -
                <?= $soalanPilihan === 1 ? 'Soalan 1: Poster Projek' : 'Soalan 2: Penilaian Projek' ?>
              </h5>
 
              <div class="card-body">
                <form action="" method="post" id="markahForm" onsubmit="return confirm('Adakah anda setuju dan berpuas hati dengan markah yang diberikan?')">
                  <input type="hidden" name="soalan" value="<?= $soalanPilihan ?>">
                  <label for="id_penilaian">Booth / Kumpulan yang perlu dinilai:</label>
                  <select class="form-control" name="id_penilaian" required>
                    <option value="" disabled selected>--Sila Pilih--</option>
                    <?php while ($row = $boothResult->fetch_assoc()): ?>
                      <option value="<?= $row['id_penilaian'] ?>">
                        <?= htmlspecialchars($row['tajuk']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <hr>
 
                  <div class="table-responsive">
                    <?php
                    $rubrikResult = $conn->query("SELECT * FROM {$tbl['rubrik']} ORDER BY kategori, id_rubrik");
 
                    $dataByKategori = [];
                    while ($row = $rubrikResult->fetch_assoc()) {
                      $dataByKategori[$row['kategori']][] = $row;
                    }
 
                    // Pembahagian 3 bahagian untuk POSTER PROJEK.
                    // Nama soalan perlu SAMA PERSIS dengan teks dalam rubrik_poster.
                    $posterGroups = [
                      'Poster mengikut format yang ditetapkan' => ['A3'],
                      'Kandungan poster lengkap mengikut garis panduan.' => [
                        'Latar Belakang/Pengenalan',
                        'Blok Diagram/Carta Alir/Objektif',
                        'Inovasi',
                        'Nilai Komersial/Pasaran/Kelebihan',
                      ],
                      'Susunan kandungan poster menarik, jelas, padat dan mudah difahami' => [
                        'Susunan yang kemas',
                        'Gambar menarik dan jelas',
                        'Tulisan yang sesuai dan jelas',
                        'Kreativiti',
                      ],
                    ];
 
                    foreach ($kategoriDipaparkan as $kategori):
                      if (!isset($dataByKategori[$kategori])) continue;
                      $soalanList = $dataByKategori[$kategori];
                    ?>
                      <table class="table table-bordered table-striped mb-4"
                        data-kategori="<?= htmlspecialchars($kategori) ?>">
                        <thead class="thead-dark">
                          <tr>
                            <th style="width: 50%;"><?= htmlspecialchars($kategori) ?></th>
                            <th class="text-center">1</th>
                            <th class="text-center">2</th>
                            <th class="text-center">3</th>
                            <th class="text-center">4</th>
                            <th class="text-center">5</th>
                          </tr>
                        </thead>
 
                        <tbody>
                          <?php if ($kategori === 'POSTER PROJEK'): ?>
                            <?php foreach ($posterGroups as $tajukKumpulan => $senaraiSoalan): ?>
                              <tr class="table-secondary">
                                <td colspan="6"><strong><?= htmlspecialchars($tajukKumpulan) ?></strong></td>
                              </tr>
                              <?php foreach ($soalanList as $row): ?>
                                <?php if (in_array($row['soalan'], $senaraiSoalan, true)): ?>
                                  <tr data-sequence="<?= $row['sequence'] ?>"
                                    data-kategori="<?= htmlspecialchars($kategori) ?>">
                                    <td><?= htmlspecialchars($row['soalan']) ?></td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                      <td class="text-center">
                                        <label class="custom-control custom-radio custom-control-inline">
                                          <input type="radio"
                                            name="markah[<?= $row['id_rubrik'] ?>]"
                                            value="<?= $i ?>"
                                            class="custom-control-input"
                                            onchange="calculateTotal()"
                                            required>
                                          <span class="custom-control-label"></span>
                                        </label>
                                      </td>
                                    <?php endfor; ?>
                                  </tr>
                                <?php endif; ?>
                              <?php endforeach; ?>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <?php foreach ($soalanList as $row): ?>
                              <tr data-sequence="<?= $row['sequence'] ?>"
                                data-kategori="<?= htmlspecialchars($kategori) ?>">
                                <td><?= htmlspecialchars($row['soalan']) ?></td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                  <td class="text-center">
                                    <label class="custom-control custom-radio custom-control-inline">
                                      <input type="radio"
                                        name="markah[<?= $row['id_rubrik'] ?>]"
                                        value="<?= $i ?>"
                                        class="custom-control-input"
                                        onchange="calculateTotal()"
                                        required>
                                      <span class="custom-control-label"></span>
                                    </label>
                                  </td>
                                <?php endfor; ?>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
 
                          <tr>
                            <th class="text-right">
                              Jumlah Markah (<?= htmlspecialchars($kategori) ?>):
                            </th>
                            <th class="text-center" colspan="5">
                              <input type="text"
                                id="totalMarkah_<?= htmlspecialchars($kategori) ?>"
                                class="form-control"
                                disabled>
                            </th>
                          </tr>
                        </tbody>
                      </table>
                    <?php endforeach; ?>
 
                  </div>
 
                  <div class="form-group">
                    <label>Jumlah Markah:</label>
                    <input type="text" id="totalMarkah" class="form-control" disabled>
                  </div>
 
                  <button type="submit" class="btn-hantar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 2 11 13"></path>
                      <path d="M22 2 15 22l-4-9-9-4 20-7z"></path>
                    </svg>
                    <span>Hantar Markah</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
 
  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>
 
  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/vendor/datatables/js/data-table.js"></script>
 
  <script>
    function calculateTotal() {
      const tables = document.querySelectorAll("table[data-kategori]");
      let grandTotal = 0;
 
      const maxMarkah = {
        "POSTER PROJEK": 10,
        "KEASLIAN DAN KREATIVITI": 25,
        "KEFUNGSIAN PROJEK/APLIKASI": 20,
        "KUALITI DAN IMPAK": 20,
        "POTENSI KOMERSIAL": 5,
        "SLAID PERSEMBAHAN DAN PENYAMPAIAN": 20
      };
 
      tables.forEach(table => {
        const kategori = table.getAttribute("data-kategori");
        const rows = table.querySelectorAll("tbody tr[data-sequence]");
        let jumlahSkala = 0;
        let bilanganSoalan = 0;
 
        rows.forEach(row => {
          const selected = row.querySelector("input[type='radio']:checked");
          if (selected) {
            jumlahSkala += parseInt(selected.value);
            bilanganSoalan++;
          }
        });
 
        let total = 0;
        if (bilanganSoalan > 0 && maxMarkah[kategori] !== undefined) {
          const maksimumSkala = bilanganSoalan * 5;
          total = (jumlahSkala / maksimumSkala) * maxMarkah[kategori];
        }
 
        const totalField = document.getElementById(`totalMarkah_${kategori}`);
        if (totalField) {
          totalField.value = total.toFixed(2);
        }
        grandTotal += total;
      });
 
      const grandField = document.getElementById("totalMarkah");
      if (grandField) {
        grandField.value = grandTotal.toFixed(2);
      }
    }
 
    document.querySelectorAll('.btn-hantar').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const ripple = document.createElement('span');
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