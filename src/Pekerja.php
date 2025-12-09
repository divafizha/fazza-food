<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'auth.php';
include 'koneksi.php';

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
  header("Location: ../Index.php?page=pekerja"); exit;
}

/* ===================== DB ADAPTER (PDO / mysqli) ===================== */
$DB_MODE = null;
if (isset($pdo) && $pdo instanceof PDO) {
  $DB_MODE = 'pdo';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} elseif (isset($conn) && $conn instanceof mysqli) {
  $DB_MODE = 'mysqli';
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $conn->set_charset('utf8mb4');
} else {
  die('Koneksi database tidak ditemukan. Pastikan koneksi.php mendefinisikan $pdo (PDO) atau $conn (mysqli).');
}

function db_fetch_all($sql, $params = []) {
  global $DB_MODE, $pdo, $conn;
  if ($DB_MODE === 'pdo') {
    if (empty($params)) return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $st = $pdo->prepare($sql); $st->execute($params); return $st->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $st = $conn->prepare($sql);
    if (!empty($params)) {
      $types = str_repeat('s', count($params));
      $st->bind_param($types, ...array_values($params));
    }
    $st->execute(); $res = $st->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
  }
}
function db_fetch($sql, $params = []) { $r = db_fetch_all($sql, $params); return $r[0] ?? null; }
function db_exec($sql, $params = []) {
  global $DB_MODE, $pdo, $conn;
  if ($DB_MODE === 'pdo') {
    if (empty($params)) return $pdo->exec($sql);
    $st = $pdo->prepare($sql); $st->execute($params); return $st->rowCount();
  } else {
    $st = $conn->prepare($sql);
    if (!empty($params)) {
      $types = str_repeat('s', count($params));
      $st->bind_param($types, ...array_values($params));
    }
    $st->execute(); return $st->affected_rows;
  }
}
function db_begin(){ global $DB_MODE,$pdo,$conn; $DB_MODE==='pdo' ? $pdo->beginTransaction() : $conn->begin_transaction(); }
function db_commit(){ global $DB_MODE,$pdo,$conn; $DB_MODE==='pdo' ? $pdo->commit() : $conn->commit(); }
function db_rollback(){ global $DB_MODE,$pdo,$conn; $DB_MODE==='pdo' ? $pdo->rollBack() : $conn->rollback(); }

/* ===================== KONFIG ===================== */
const TARIF_PER_KG = 2500;
const MIN_AMBIL_KG = 20;

/* ===================== DATA STOK ===================== */
$sql_stok = "
  SELECT s.id_stok, s.jumlah_stok, s.status_stok, p.nama_produk, pr.tgl_produksi
  FROM stok s
  JOIN produk p ON s.id_produk = p.id_produk
  LEFT JOIN produksi pr ON s.id_produksi = pr.id_produksi
  WHERE s.status_stok IN ('Siap dikemas','Siap dipacking')
    AND s.jumlah_stok > 0
  ORDER BY COALESCE(pr.tgl_produksi,'0000-00-00') DESC, s.id_stok DESC
";
try {
  $stok_list = db_fetch_all($sql_stok);
} catch (Throwable $e) {
  $_SESSION['notif'] = ['pesan' => 'Error ambil stok: '.$e->getMessage(), 'tipe' => 'error'];
  $stok_list = [];
}

/* ===================== HELPER STATUS PEKERJA (FIXED) ===================== */
function updateStatusPekerjaGeneric($id_pekerja) {
  // Hitung total saldo gaji yang belum dibayar (termasuk Return dan Keterangan Tambahan)
  // Menggunakan LIKE 'Belum Dibayar%' agar variasi text terhitung
  $total_balance = db_fetch("
    SELECT SUM(total_gaji) as total 
    FROM riwayat_gaji 
    WHERE id_pekerja = ? 
    AND (keterangan LIKE 'Belum Dibayar%' OR keterangan = 'Return')
  ", [$id_pekerja]);
  
  $new_status = ((float)($total_balance['total'] ?? 0) > 0) ? 'Belum Dibayar' : 'Dibayar';
  db_exec("UPDATE pekerja_lepas SET status_pembayaran = ? WHERE id_pekerja = ?", [$new_status, $id_pekerja]);
}


/* ===================== PROSES POST ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    switch ($action) {
      case 'tambah_pekerja':
        db_exec(
          "INSERT INTO pekerja_lepas (nama_pekerja, kontak, alamat, status_pembayaran, id_admin)
           VALUES (?, ?, ?, 'Belum Dibayar', 1)",
          [trim($_POST['nama_pekerja'] ?? ''), trim($_POST['kontak'] ?? ''), trim($_POST['alamat'] ?? '')]
        );
        $_SESSION['notif'] = ['pesan' => 'Data pekerja berhasil ditambahkan!', 'tipe' => 'sukses'];
        break;

      case 'edit_pekerja':
        db_exec(
          "UPDATE pekerja_lepas SET nama_pekerja = ?, kontak = ?, alamat = ? WHERE id_pekerja = ?",
          [trim($_POST['nama_pekerja'] ?? ''), trim($_POST['kontak'] ?? ''), trim($_POST['alamat'] ?? ''), (int)($_POST['id_pekerja_edit'] ?? 0)]
        );
        $_SESSION['notif'] = ['pesan' => 'Data pekerja berhasil diperbarui!', 'tipe' => 'sukses'];
        break;

      case 'ambil_stok_pekerja':
        $id_pekerja = (int)($_POST['id_pekerja_ambil'] ?? 0);
        $id_stok    = (int)($_POST['id_stok_ambil'] ?? 0);
        $jumlah_kg  = (float)($_POST['jumlah_kg'] ?? 0);
        $tanggal    = $_POST['tanggal_ambil'] ?? date('Y-m-d'); 
        $waktu      = $_POST['waktu_ambil'] ?? date('H:i:s');
        
        $timestamp_lengkap = $tanggal . ' ' . $waktu . ':00'; 

        if ($id_pekerja <= 0 || $id_stok <= 0) throw new Exception("Data tidak lengkap.");
        if ($jumlah_kg < MIN_AMBIL_KG) throw new Exception("Minimal ambil ".MIN_AMBIL_KG." kg!");

        db_begin();

        $stok = db_fetch("SELECT * FROM stok WHERE id_stok = ? FOR UPDATE", [$id_stok]);
        if (!$stok) throw new Exception("Stok tidak ditemukan.");
        if ((float)$stok['jumlah_stok'] < $jumlah_kg) throw new Exception("Tidak cukup stok tersedia.");

        $gaji = $jumlah_kg * TARIF_PER_KG;

        // Catat pengambilan
        db_exec(
          "INSERT INTO pengambilan_stok_pekerja (id_pekerja, id_stok, tanggal_ambil, jumlah_kg, total_gaji, status)
           VALUES (?, ?, ?, ?, ?, 'Sedang dikerjakan')",
          [$id_pekerja, $id_stok, $timestamp_lengkap, $jumlah_kg, $gaji] 
        );

        // Catat riwayat gaji
        db_exec(
          "INSERT INTO riwayat_gaji (id_pekerja, tanggal, berat_barang_kg, id_stok, tarif_per_kg, total_gaji, keterangan)
           VALUES (?, ?, ?, ?, ?, ?, 'Belum Dibayar')",
          [$id_pekerja, $timestamp_lengkap, $jumlah_kg, $id_stok, TARIF_PER_KG, $gaji]
        );

        // Kurangi stok
        db_exec("UPDATE stok SET jumlah_stok = GREATEST(jumlah_stok - ?, 0) WHERE id_stok = ?", [$jumlah_kg, $id_stok]);

        updateStatusPekerjaGeneric($id_pekerja);
        db_commit();

        $_SESSION['notif'] = ['pesan' => 'Pengambilan stok & riwayat gaji tersimpan!', 'tipe' => 'sukses'];
        break;

      case 'hapus_pekerja':
        db_exec("DELETE FROM pekerja_lepas WHERE id_pekerja = ?", [(int)($_POST['id_pekerja_hapus'] ?? 0)]);
        $_SESSION['notif'] = ['pesan' => 'Data pekerja berhasil dihapus.', 'tipe' => 'sukses'];
        break;
    }
  } catch (Throwable $e) {
    try { db_rollback(); } catch (Throwable $x) {}
    $_SESSION['notif'] = ['pesan' => 'Error: '.$e->getMessage(), 'tipe' => 'error'];
  }
  $search_query = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
  header("Location: Index.php?page=pekerja" . $search_query);
  exit;
}

/* ===================== DATA UTAMA (FIXED QUERY) ===================== */
$search_term = $_GET['search'] ?? '';

// Perbaikan Query: Menggunakan LIKE 'Belum Dibayar%' agar keterangan tambahan terbaca
$sql_pekerja = "SELECT pl.*,
    (SELECT COALESCE(SUM(total_gaji),0) 
     FROM riwayat_gaji rg 
     WHERE rg.id_pekerja = pl.id_pekerja 
     AND rg.keterangan = 'Dibayar') as total_dibayar,
     
    (SELECT COALESCE(SUM(
        CASE 
            WHEN rg.keterangan LIKE 'Belum Dibayar%' THEN rg.total_gaji 
            WHEN rg.keterangan = 'Return' THEN rg.total_gaji 
            ELSE 0 
        END), 0) 
     FROM riwayat_gaji rg 
     WHERE rg.id_pekerja = pl.id_pekerja) as total_belum_dibayar
  FROM pekerja_lepas pl";

$params = [];
if (!empty($search_term)) { $sql_pekerja .= " WHERE pl.nama_pekerja LIKE ?"; $params[] = "%".$search_term."%"; }
$sql_pekerja .= " ORDER BY pl.nama_pekerja ASC";

try {
  $pekerja_list = db_fetch_all($sql_pekerja, $params);
  
  // Perhitungan Ringkasan Finansial
  $sum_rows = db_fetch_all("
    SELECT keterangan, SUM(total_gaji) AS total_per_keterangan 
    FROM riwayat_gaji 
    GROUP BY keterangan
  ");
  
  $summary_dibayar = 0;
  $summary_belum_dibayar = 0;

  foreach ($sum_rows as $r) {
      if ($r['keterangan'] === 'Dibayar') {
          $summary_dibayar += (float)$r['total_per_keterangan'];
      } elseif (strpos($r['keterangan'], 'Belum Dibayar') === 0 || $r['keterangan'] === 'Return') {
          // Gabungkan semua yang diawali 'Belum Dibayar' dan 'Return'
          $summary_belum_dibayar += (float)$r['total_per_keterangan'];
      }
  }
  
} catch (Throwable $e) {
  $_SESSION['notif'] = ['pesan' => 'Error ambil data pekerja: '.$e->getMessage(), 'tipe' => 'error'];
  $pekerja_list = []; $summary_dibayar = 0; $summary_belum_dibayar = 0;
}
?>

<main class="flex-1 bg-gray-100">
  <section class="p-6 overflow-x-auto">
    <?php if (isset($_SESSION['notif'])): ?>
      <div class="mb-4 p-4 rounded-md text-white font-bold <?php echo $_SESSION['notif']['tipe'] === 'sukses' ? 'bg-green-500' : 'bg-red-500'; ?>">
        <?php echo htmlspecialchars($_SESSION['notif']['pesan']); ?>
      </div>
    <?php unset($_SESSION['notif']); endif; ?>

    <div class="flex flex-col md:flex-row md:items-center md:space-x-4 mb-4">
      <button id="btnTambahPekerja" class="flex-shrink-0 inline-flex items-center gap-2 bg-yellow-300 text-yellow-900 text-sm font-bold px-4 py-2 rounded shadow-sm hover:shadow-md transition-shadow mb-2 md:mb-0" type="button"><i class="fas fa-plus"></i> Tambah Pekerja</button>
      <form action="Index.php" method="GET" class="flex flex-1 max-w-md">
        <input type="hidden" name="page" value="pekerja">
        <input type="text" name="search" placeholder="Cari nama pekerja..." class="flex-grow border border-gray-300 rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400" value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" class="bg-yellow-400 text-yellow-900 px-6 py-2 rounded-r shadow-sm hover:shadow-md transition-shadow">Cari</button>
      </form>
    </div>

    <table class="w-full border border-gray-300 text-sm bg-white text-left">
      <thead class="bg-yellow-200 text-xs text-gray-900">
        <tr>
          <th class="border border-gray-300 px-3 py-2 w-12">No.</th>
          <th class="border border-gray-300 px-3 py-2 w-40">Nama</th>
          <th class="border border-gray-300 px-3 py-2 w-32">Kontak</th>
          <th class="border border-gray-300 px-3 py-2 w-40">Total Dibayar</th>
          <th class="border border-gray-300 px-3 py-2 w-40">Total Belum Dibayar</th>
          <th class="border border-gray-300 px-3 py-2 w-40">Status Pekerja</th>
          <th class="border border-gray-300 px-3 py-2 w-52">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pekerja_list)): ?>
          <tr><td colspan="7" class="border border-gray-300 px-3 py-4 text-center text-gray-500">Data pekerja tidak ditemukan.</td></tr>
        <?php else: foreach ($pekerja_list as $i => $pekerja): ?>
          <tr>
            <td class="border border-gray-300 px-3 py-2"><?php echo $i + 1; ?>.</td>
            <td class="border border-gray-300 px-3 py-2"><?php echo htmlspecialchars($pekerja['nama_pekerja']); ?></td>
            <td class="border border-gray-300 px-3 py-2"><?php echo htmlspecialchars($pekerja['kontak']); ?></td>
            <td class="border border-gray-300 px-3 py-2 text-green-700">Rp. <?php echo number_format((float)($pekerja['total_dibayar'] ?? 0), 0, ',', '.'); ?></td>
            <td class="border border-gray-300 px-3 py-2 <?php echo (float)($pekerja['total_belum_dibayar'] ?? 0) > 0 ? 'text-red-700' : 'text-gray-500'; ?>">Rp. <?php echo number_format((float)($pekerja['total_belum_dibayar'] ?? 0), 0, ',', '.'); ?></td>
            <td class="border border-gray-300 px-3 py-2">
              <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo ($pekerja['status_pembayaran'] ?? '') === 'Dibayar' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                <?php echo htmlspecialchars($pekerja['status_pembayaran'] ?? 'Belum Dibayar'); ?>
              </span>
            </td>
            <td class="border border-gray-300 px-3 py-2 space-x-1 flex items-center justify-center ">
              <button class="btnAmbilStok bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded inline-flex items-center gap-1"
                data-id-pekerja="<?php echo (int)$pekerja['id_pekerja']; ?>"
                data-nama-pekerja="<?php echo htmlspecialchars($pekerja['nama_pekerja']); ?>">
                <i class="fas fa-box-open"></i> Ambil Stok
              </button>
              <a href="Index.php?page=riwayat_gaji&id_pekerja=<?php echo (int)$pekerja['id_pekerja']; ?>" class="btnHistory bg-gray-500 text-white text-xs px-3 py-1 rounded inline-block">Riwayat</a>
              <button class="btnEdit bg-yellow-400 text-yellow-900 text-xs px-3 py-1 rounded"
                data-id-pekerja="<?php echo (int)$pekerja['id_pekerja']; ?>"
                data-nama="<?php echo htmlspecialchars($pekerja['nama_pekerja']); ?>"
                data-kontak="<?php echo htmlspecialchars($pekerja['kontak']); ?>"
                data-alamat="<?php echo htmlspecialchars($pekerja['alamat']); ?>">
                Edit
              </button>
              <button class="btnHapus bg-red-700 text-white text-xs px-3 py-1 rounded" data-id-pekerja="<?php echo (int)$pekerja['id_pekerja']; ?>">Hapus</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

    <table class="w-full max-w-sm border border-gray-300 text-sm mt-4 bg-white">
      <thead class="bg-yellow-200 text-gray-900 text-xs">
        <tr><th class="border border-gray-300 px-3 py-1 text-center" colspan="2">Ringkasan Finansial</th></tr>
      </thead>
      <tbody>
        <tr><td class="border border-gray-300 px-3 py-1 font-medium">Total Gaji (Status: Dibayar)</td><td class="border border-gray-300 px-3 py-1">Rp. <?php echo number_format((float)$summary_dibayar, 0, ',', '.'); ?></td></tr>
        <tr><td class="border border-gray-300 px-3 py-1 font-medium">Total Gaji (Status: Belum Dibayar)</td><td class="border border-gray-300 px-3 py-1">Rp. <?php echo number_format((float)$summary_belum_dibayar, 0, ',', '.'); ?></td></tr>
        <tr class="bg-gray-50"><td class="border border-gray-300 px-3 py-1 font-bold">Total Pekerja</td><td class="border border-gray-300 px-3 py-1 font-bold"><?php echo count($pekerja_list); ?> Orang</td></tr>
      </tbody>
    </table>
  </section>

  <div id="modalAmbilStokPekerja" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <form action="" method="POST" class="bg-white p-6 shadow-md rounded w-80 relative">
      <button type="button" class="btnClose absolute top-2 right-2 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
      <h2 class="text-black font-semibold text-lg mb-1">Ambil Stok</h2>
      <div class="text-sm mb-3"><span id="namaPekerjaAmbil"></span></div>

      <input type="hidden" name="action" value="ambil_stok_pekerja">
      <input type="hidden" name="id_pekerja_ambil" id="id_pekerja_ambil">

      <div class="mb-3">
        <label for="id_stok_ambil" class="block text-sm font-medium text-gray-700 mb-1">Pilih Stok Siap</label>
        <select name="id_stok_ambil" id="id_stok_ambil" class="w-full px-3 py-2 border border-gray-300 rounded" required>
          <option value="" disabled selected>-- Pilih Stok --</option>
          <?php foreach ($stok_list as $stok):
            $tgl = (!empty($stok['tgl_produksi']) && $stok['tgl_produksi'] !== '0000-00-00') ? date('d-m-Y', strtotime($stok['tgl_produksi'])) : '-'; ?>
            <option value="<?php echo (int)$stok['id_stok']; ?>"
                      data-sisa="<?php echo (float)$stok['jumlah_stok']; ?>">
              (<?= $tgl ?>) <?= htmlspecialchars($stok['nama_produk']) ?> | Status: <?= htmlspecialchars($stok['status_stok']) ?> | Sisa: <?= (float)$stok['jumlah_stok'] ?> kg
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex space-x-2 mb-3">
        <div class="w-1/2">
          <label for="tanggal_ambil" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Ambil</label>
          <input type="date" name="tanggal_ambil" id="tanggal_ambil" class="w-full px-3 py-2 border border-gray-300 rounded" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="w-1/2">
          <label for="waktu_ambil" class="block text-sm font-medium text-gray-700 mb-1">Waktu Ambil</label>
          <input type="time" name="waktu_ambil" id="waktu_ambil" class="w-full px-3 py-2 border border-gray-300 rounded" value="<?= date('H:i') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label>Jumlah diambil (kg)</label>
        <input type="number" step="0.01" min="<?php echo MIN_AMBIL_KG; ?>" name="jumlah_kg" id="jumlah_kg_ambil" class="w-full px-3 py-2 border border-gray-300 rounded" required>
        <div id="ambilStokInfo" class="text-xs text-gray-500"></div>
        <div id="ambilStokError" class="text-xs text-red-600 mt-1 hidden"></div>
      </div>

      <div class="mb-3 text-sm">Tarif: <span class="font-mono">Rp. <?= number_format(TARIF_PER_KG, 0, ',', '.'); ?> / kg</span></div>
      <div class="mb-3 text-sm">Total Gaji: <span id="ambilStokTotalGaji" class="font-mono font-bold text-yellow-800">Rp. 0</span></div>

      <button type="submit" class="w-full bg-yellow-400 text-yellow-900 hover:bg-yellow-300 py-2 rounded">Ambil & Catat Gaji</button>
    </form>
  </div>
  <div id="modalTambah" class="fixed inset-0 bg-black bg-opacity-50 flex hidden items-center justify-center z-50">
    <form action="" method="POST" class="bg-white p-6 shadow-md rounded w-80 relative">
      <button type="button" class="btnClose absolute top-2 right-2 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
      <h2 class="text-black font-semibold text-lg mb-4">Tambah Pekerja Baru</h2>
      <input type="hidden" name="action" value="tambah_pekerja">
      <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label><input type="text" name="nama_pekerja" class="w-full px-3 py-2 border border-gray-300 rounded" required /></div>
      <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Kontak</label><input type="text" name="kontak" class="w-full px-3 py-2 border border-gray-300 rounded" required /></div>
      <div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label><textarea name="alamat" class="w-full px-3 py-2 border border-gray-300 rounded" required></textarea></div>
      <button type="submit" class="w-full bg-yellow-400 text-yellow-900 hover:bg-yellow-300 py-2 rounded">Simpan</button>
    </form>
  </div>

  <div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-50 flex hidden items-center justify-center z-50">
    <form action="" method="POST" class="bg-white p-6 shadow-md rounded w-80 relative">
      <button type="button" class="btnClose absolute top-2 right-2 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
      <h2 class="text-black font-semibold text-lg mb-4">Edit Data Pekerja</h2>
      <input type="hidden" name="action" value="edit_pekerja">
      <input type="hidden" name="id_pekerja_edit" id="id_pekerja_edit">
      <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label><input type="text" name="nama_pekerja" id="nama_pekerja_edit" class="w-full px-3 py-2 border border-gray-300 rounded" required /></div>
      <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Kontak</label><input type="text" name="kontak" id="kontak_edit" class="w-full px-3 py-2 border border-gray-300 rounded" required /></div>
      <div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label><textarea name="alamat" id="alamat_edit" class="w-full px-3 py-2 border border-gray-300 rounded" required></textarea></div>
      <button type="submit" class="w-full bg-yellow-400 text-yellow-900 hover:bg-yellow-300 py-2 rounded">Simpan Perubahan</button>
    </form>
  </div>

  <div id="modalHapus" class="fixed inset-0 bg-black bg-opacity-50 flex hidden items-center justify-center z-50">
    <div class="w-[320px] border p-6 bg-white rounded-md relative">
      <button type="button" class="btnClose absolute top-2 right-2 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
      <h2 class="font-semibold text-black mb-3 text-lg">Konfirmasi Hapus</h2>
      <p class="text-gray-700 mb-5 text-sm">Data pekerja dan seluruh riwayat gajinya akan dihapus permanen.</p>
      <form action="" method="POST" class="flex justify-end space-x-3">
        <input type="hidden" name="action" value="hapus_pekerja">
        <input type="hidden" name="id_pekerja_hapus" id="id_pekerja_hapus">
        <button type="button" class="btnCancelHapus border border-gray-400 text-black text-sm font-medium rounded px-4 py-2 hover:bg-gray-100">Batal</button>
        <button type="submit" class="bg-red-600 text-white text-sm font-medium rounded px-4 py-2 hover:bg-red-700">Ya, Hapus</button>
      </form>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modals = {
    tambah: document.getElementById('modalTambah'),
    edit: document.getElementById('modalEdit'),
    hapus: document.getElementById('modalHapus'),
    ambilStok: document.getElementById('modalAmbilStokPekerja'),
  };
  const openModal = (m) => { if (m) m.classList.remove('hidden'); };
  const closeModal = (m) => { if (m) m.classList.add('hidden'); };

  document.getElementById('btnTambahPekerja')?.addEventListener('click', () => openModal(modals.tambah));
  modals.hapus?.querySelector('.btnCancelHapus')?.addEventListener('click', () => closeModal(modals.hapus));
  Object.values(modals).forEach(m => {
    if (!m) return;
    m.querySelector('.btnClose')?.addEventListener('click', () => closeModal(m));
    m.addEventListener('click', e => { if (e.target === m) closeModal(m); });
  });

  // Ambil Stok
  document.querySelectorAll('.btnAmbilStok').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.idPekerja;
      const nama = btn.dataset.namaPekerja;
      document.getElementById('id_pekerja_ambil').value = id;
      document.getElementById('namaPekerjaAmbil').textContent = 'Pekerja: ' + nama;

      // Set tanggal dan waktu saat ini
      const now = new Date();
      const dateString = now.toISOString().slice(0,10); // YYYY-MM-DD
      const timeString = now.toTimeString().slice(0,5); // HH:MM

      document.getElementById('tanggal_ambil').value = dateString;
      document.getElementById('waktu_ambil').value = timeString;

      const jumlahInput = document.getElementById('jumlah_kg_ambil');
      jumlahInput.value = '';
      document.getElementById('ambilStokTotalGaji').textContent = 'Rp. 0';
      document.getElementById('ambilStokError').classList.add('hidden');
      document.getElementById('id_stok_ambil').selectedIndex = 0; // Reset stok pilihan
      document.getElementById('ambilStokInfo').textContent = ''; // Kosongkan info sisa stok awal

      openModal(modals.ambilStok);
    });
  });

  const stokSelect = document.getElementById('id_stok_ambil');
  const jumlahInput = document.getElementById('jumlah_kg_ambil');
  const info = document.getElementById('ambilStokInfo');
  const error = document.getElementById('ambilStokError');
  const totalGaji = document.getElementById('ambilStokTotalGaji');
  const MIN_AMBIL = <?php echo (float)MIN_AMBIL_KG; ?>;
  const TARIF = <?php echo (int)TARIF_PER_KG; ?>;

  stokSelect?.addEventListener('change', () => {
    const sisa = parseFloat(stokSelect.selectedOptions[0]?.dataset.sisa || '0');
    info.textContent = 'Sisa stok: ' + sisa.toFixed(2) + ' kg (Minimal ambil ' + MIN_AMBIL + ' kg)';
    jumlahInput.max = sisa;
    jumlahInput.value = '';
    jumlahInput.dispatchEvent(new Event('input')); 
  });

  jumlahInput?.addEventListener('input', () => {
    const max = parseFloat(jumlahInput.max || '0');
    const val = parseFloat(jumlahInput.value || '0');
    
    // Reset error
    error.classList.add('hidden');

    if (val > max) {
      error.textContent = 'Tidak boleh melebihi stok tersedia (' + max.toFixed(2) + ' kg)!';
      error.classList.remove('hidden');
    } else if (val > 0 && val < MIN_AMBIL) {
      error.textContent = 'Minimal ambil ' + MIN_AMBIL + ' kg.';
      error.classList.remove('hidden');
    }
    
    // Hitung gaji
    const gaji = val * TARIF;
    totalGaji.textContent = 'Rp. ' + (val > 0 ? Math.round(gaji).toLocaleString('id-ID') : '0');
  });

  // Edit & Hapus
  document.body.addEventListener('click', function(e) {
    const target = e.target.closest('button, a');
    if (!target) return;
    const data = target.dataset;
    if (target.classList.contains('btnEdit')) {
      e.preventDefault();
      document.getElementById('id_pekerja_edit').value = data.idPekerja || '';
      document.getElementById('nama_pekerja_edit').value = data.nama || '';
      document.getElementById('kontak_edit').value = data.kontak || '';
      document.getElementById('alamat_edit').value = data.alamat || '';
      openModal(modals.edit);
    } else if (target.classList.contains('btnHapus')) {
      e.preventDefault();
      document.getElementById('id_pekerja_hapus').value = data.idPekerja || '';
      openModal(modals.hapus);
    }
  });
});
</script>