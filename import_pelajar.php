<?php
// Mula session kalau belum mula
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "includes/config.php"; // mesti bagi $conn (mysqli)

// Admin sahaja (sama macam pelajar.php)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}

// Semak fail upload
if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
  $_SESSION['error'] = "Import gagal: fail tidak berjaya dimuat naik.";
  header("Location: pelajar.php");
  exit;
}

$ext = strtolower(pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
  $_SESSION['error'] = "Import gagal: fail mesti berformat .csv";
  header("Location: pelajar.php");
  exit;
}

$fh = fopen($_FILES['csv']['tmp_name'], 'r');

// Buang BOM (CSV UTF-8 dari Excel)
$bom = fread($fh, 3);
if ($bom !== "\xEF\xBB\xBF") rewind($fh);

// Baca header + auto detect pemisah (, atau ;)
$first = fgets($fh);
$delim = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';

$cek = $conn->prepare("SELECT 1 FROM pelajar WHERE nama_pelajar = ? LIMIT 1");
$ins = $conn->prepare("INSERT INTO pelajar (nama_pelajar, bidang, kursus, ahli_kumpulan) VALUES (?, ?, ?, ?)");

$ok = 0;
$dup = 0;
$skip = 0;

while (($r = fgetcsv($fh, 0, $delim)) !== false) {
  // Langkau baris kosong / kolum tak cukup / baris contoh
  if (count($r) < 3 || trim($r[0]) === '' || stripos(trim($r[0]), 'CONTOH') === 0) {
    $skip++;
    continue;
  }

  $nama   = strtoupper(trim($r[0]));
  $bidang = strtoupper(trim($r[1]));
  $kursus = strtoupper(trim($r[2]));
  $ahli   = strtoupper(trim($r[3] ?? ''));

  if ($bidang === '' || $kursus === '') {
    $skip++;
    continue;
  }

  // Langkau kalau nama dah wujud
  $cek->bind_param("s", $nama);
  $cek->execute();
  $cek->store_result();
  if ($cek->num_rows > 0) {
    $dup++;
    continue;
  }

  $ins->bind_param("ssss", $nama, $bidang, $kursus, $ahli);
  if ($ins->execute()) {
    $ok++;
  } else {
    $skip++;
  }
}

fclose($fh);
$cek->close();
$ins->close();

$_SESSION['success'] = "Import siap: $ok pelajar ditambah, $dup sudah wujud (dilangkau), $skip baris tidak sah.";
header("Location: pelajar.php");
exit;