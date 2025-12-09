<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';
include 'koneksi.php';

$DB_MODE = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $DB_MODE = 'pdo';
    $db = $pdo;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} else {
    // Memberikan pesan error yang lebih jelas jika koneksi PDO tidak ada
    die('Koneksi database (PDO) tidak ditemukan. Pastikan koneksi.php mendefinisikan $pdo.');
}

// Tarif per kg
define('HARGA_PER_KG', 2500);

// Fungsi update status pekerja (FIXED LIKE)
function updateWorkerPaymentStatus($db, $id_pekerja)
{
    $sql_balance = "SELECT COALESCE(SUM(total_gaji), 0) FROM riwayat_gaji WHERE id_pekerja = ? AND (keterangan LIKE 'Belum Dibayar%' OR keterangan = 'Return')";
    $stmt_balance = $db->prepare($sql_balance);
    $stmt_balance->execute([$id_pekerja]);
    $total_unpaid_balance = $stmt_balance->fetchColumn();

    $new_status = ((float)$total_unpaid_balance > 0) ? 'Belum Dibayar' : 'Dibayar';
    
    $sql_update = "UPDATE pekerja_lepas SET status_pembayaran = ? WHERE id_pekerja = ?";
    $stmt_update = $db->prepare($sql_update);
    $stmt_update->execute([$new_status, $id_pekerja]);
}

// Validasi ID pekerja
if (!isset($_GET['id_pekerja']) || !is_numeric($_GET['id_pekerja'])) {
    header("Location: Index.php?page=pekerja");
    exit;
}
$id_pekerja = $_GET['id_pekerja'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        $db->beginTransaction();

        if ($action === 'edit_gaji_riwayat') {
            $id_gaji_edit = (int)($_POST['id_gaji_edit'] ?? 0);
            $berat_kg = (float)($_POST['berat_barang_kg'] ?? 0);
            $total_gaji = round($berat_kg * HARGA_PER_KG);
            $keterangan_baru = trim($_POST['keterangan'] ?? '');
            
            // Variabel tersembunyi dari form untuk validasi stok
            $berat_asal = (float)($_POST['berat_asal'] ?? 0);
            $id_stok = (int)($_POST['id_stok_edit_hidden'] ?? 0);
            
            // 1. Cek Validasi Berat Minimum (Tidak boleh kurang)
            $selisih_berat = $berat_kg - $berat_asal;
            if ($selisih_berat < 0) {
                throw new Exception("Revisi berat tidak boleh mengurangi berat awal (".number_format($berat_asal, 2)." kg). Gunakan fitur 'Return'.");
            }
            
            // 2. Cek Stok dan Update (Hanya jika berat bertambah)
            if ($selisih_berat > 0) {
                if ($id_stok === 0) throw new Exception("ID Stok tidak ditemukan untuk riwayat ini. Tidak dapat menambah berat.");
                
                // Pengecekan stok saat ini di DB (SUMBER TERPERCAYA)
                $stok_db = $db->prepare("SELECT jumlah_stok FROM stok WHERE id_stok = ?");
                $stok_db->execute([$id_stok]);
                $sisa_stok_db = (float)($stok_db->fetchColumn() ?? 0);
                
                if ($selisih_berat > $sisa_stok_db) {
                    throw new Exception("Penambahan berat (".number_format($selisih_berat, 2)." kg) melebihi sisa stok yang tersedia ({$sisa_stok_db} kg).");
                }
                
                // Kurangi stok sebesar selisih penambahan
                $stmt_kurangi = $db->prepare("UPDATE stok SET jumlah_stok = jumlah_stok - ? WHERE id_stok = ?");
                $stmt_kurangi->execute([$selisih_berat, $id_stok]);
                
                // Tambahkan keterangan audit
                // Format selisih tanpa koma jika bilangan bulat
                $selisih_format = (fmod($selisih_berat, 1) !== 0.00) ? number_format($selisih_berat, 2) : number_format($selisih_berat, 0);

                $keterangan_baru = "{$keterangan_baru} (Tambah: +{$selisih_format} kg)";
            }
            
            // 3. Update Database
            $sql = "UPDATE riwayat_gaji 
                    SET tanggal = ?, berat_barang_kg = ?, total_gaji = ?, keterangan = ? 
                    WHERE id_gaji = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $_POST['tanggal'],
                $berat_kg,
                $total_gaji,
                $keterangan_baru, // Menggunakan keterangan baru (termasuk audit jika ada)
                $id_gaji_edit
            ]);
            
            updateWorkerPaymentStatus($db, $id_pekerja);
            $_SESSION['notif'] = ['pesan' => 'Riwayat gaji berhasil diperbarui!', 'tipe' => 'sukses'];
        }

        if ($action === 'hapus_gaji_riwayat') {
            $id_gaji_hapus = (int)($_POST['id_gaji_hapus'] ?? 0);
            $stmt_data = $db->prepare("SELECT id_stok, berat_barang_kg, keterangan FROM riwayat_gaji WHERE id_gaji = ?");
            $stmt_data->execute([$id_gaji_hapus]);
            $data = $stmt_data->fetch(PDO::FETCH_ASSOC);

            if ($data && $data['id_stok'] > 0) {
                $berat_terkait = (float)$data['berat_barang_kg'];
                $id_stok = (int)$data['id_stok'];
                $berat_untuk_stok = $berat_terkait * -1;
                
                $stmt_stok = $db->prepare("UPDATE stok SET jumlah_stok = jumlah_stok + ? WHERE id_stok = ?"); 
                $stmt_stok->execute([$berat_untuk_stok, $id_stok]);
            }

            $sql = "DELETE FROM riwayat_gaji WHERE id_gaji = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_gaji_hapus]);
            
            updateWorkerPaymentStatus($db, $id_pekerja);
            $_SESSION['notif'] = ['pesan' => 'Data riwayat gaji berhasil dihapus. Stok terkait disesuaikan.', 'tipe' => 'sukses'];
        }

        if ($action === 'return_gaji') {
            $id_gaji_asal = (int)($_POST['id_gaji_return'] ?? 0);
            $berat_return = (float)($_POST['berat_return'] ?? 0);
            
            $stmt_data = $db->prepare("SELECT id_stok, berat_barang_kg, keterangan FROM riwayat_gaji WHERE id_gaji = ?");
            $stmt_data->execute([$id_gaji_asal]);
            $data = $stmt_data->fetch(PDO::FETCH_ASSOC);

            if (!$data) throw new Exception("Data gaji asal tidak ditemukan.");
            if ($data['keterangan'] !== 'Belum Dibayar') throw new Exception("Hanya entri 'Belum Dibayar' yang dapat direturn.");

            $berat_asal = (float)$data['berat_barang_kg'];
            if ($berat_return <= 0 || $berat_return > $berat_asal) throw new Exception("Jumlah return (".number_format($berat_return, 2)." kg) tidak valid atau melebihi berat asal (".number_format($berat_asal, 2)." kg).");

            $id_stok = (int)$data['id_stok'];
            $total_return = round($berat_return * HARGA_PER_KG * -1);
            $tanggal_return = $_POST['tanggal_return'] . ' ' . $_POST['waktu_return'] . ':00';

            if ($id_stok > 0) {
                $stmt_stok = $db->prepare("UPDATE stok SET jumlah_stok = jumlah_stok + ? WHERE id_stok = ?");
                $stmt_stok->execute([$berat_return, $id_stok]);
            }

            $stmt_insert = $db->prepare("
                INSERT INTO riwayat_gaji (id_pekerja, tanggal, berat_barang_kg, id_stok, tarif_per_kg, total_gaji, keterangan) 
                VALUES (?, ?, ?, ?, ?, ?, 'Return')
            ");
            $stmt_insert->execute([
                $id_pekerja, 
                $tanggal_return, 
                $berat_return * -1, 
                $id_stok, 
                HARGA_PER_KG, 
                $total_return
            ]);

            updateWorkerPaymentStatus($db, $id_pekerja);
            
            $_SESSION['notif'] = ['pesan' => 'Return barang berhasil dicatat! Stok ditambahkan kembali sebesar '.number_format($berat_return, 2).' kg.', 'tipe' => 'sukses'];
        }

        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        $_SESSION['notif'] = ['pesan' => 'Kesalahan: ' . $e->getMessage(), 'tipe' => 'error'];
    }

    header("Location: Index.php?page=riwayat_gaji&id_pekerja=$id_pekerja");
    exit;
}

// =========================================================
// == DATA UTAMA
// =========================================================

// Ambil data pekerja (FIXED: Untuk menghilangkan Undefined variable $pekerja)
$stmt_pekerja = $db->prepare("SELECT nama_pekerja FROM pekerja_lepas WHERE id_pekerja = ?");
$stmt_pekerja->execute([$id_pekerja]);
$pekerja = $stmt_pekerja->fetch(PDO::FETCH_ASSOC);

if (!$pekerja) {
    $_SESSION['notif'] = ['pesan' => 'Pekerja tidak ditemukan.', 'tipe' => 'error'];
    header("Location: Index.php?page=pekerja");
    exit;
}

// Ambil data riwayat gaji (dengan ID stok dan stok saat ini)
$stmt_riwayat = $db->prepare("
    SELECT rg.*, 
           COALESCE(s.jumlah_stok, 0) as stok_saat_ini 
    FROM riwayat_gaji rg 
    LEFT JOIN stok s ON rg.id_stok = s.id_stok 
    WHERE rg.id_pekerja = ? 
    ORDER BY rg.tanggal DESC
");
$stmt_riwayat->execute([$id_pekerja]);
$riwayat_list = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-gray-100">
<section class="p-6 overflow-x-auto">
<?php if (isset($_SESSION['notif'])): ?>
    <div class="mb-4 p-4 rounded-md text-white font-bold <?php echo $_SESSION['notif']['tipe'] === 'sukses' ? 'bg-green-500' : 'bg-red-500'; ?>">
        <?= htmlspecialchars($_SESSION['notif']['pesan']); ?>
    </div>
<?php unset($_SESSION['notif']); endif; ?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Riwayat Gaji</h2>
        <p class="text-gray-600">Untuk: <?= htmlspecialchars($pekerja['nama_pekerja']); ?></p>
    </div>
    <a href="Index.php?page=pekerja" class="bg-yellow-400 text-yellow-900 text-sm px-4 py-2 rounded hover:bg-yellow-300 transition-colors">&larr; Kembali</a>
</div>

<table class="w-full border border-gray-300 text-sm bg-white text-left">
<thead class="bg-yellow-200">
<tr>
    <th class="border border-gray-300 px-3 py-2 w-48">Tanggal & Waktu</th>
    <th class="border border-gray-300 px-3 py-2 w-40">Berat (Kg)</th>
    <th class="border border-gray-300 px-3 py-2 w-40">Total Gaji</th>
    <th class="border border-gray-300 px-3 py-2 w-64">Keterangan</th> <th class="border border-gray-300 px-3 py-2 w-28">Aksi</th>
</tr>
</thead>
<tbody>
<?php if (empty($riwayat_list)): ?>
<tr>
    <td colspan="5" class="p-4 text-center text-gray-500">Belum ada riwayat gaji untuk pekerja ini.</td>
</tr>
<?php else: ?>
<?php foreach ($riwayat_list as $item): 
    $berat_mutlak = abs((float)$item['berat_barang_kg']);
    $gaji_mutlak = abs((float)$item['total_gaji']);
    $timestamp_display = date('d-m-Y H:i', strtotime($item['tanggal']));

    // Menghilangkan ,00 jika bilangan bulat
    $berat_display = (fmod($berat_mutlak, 1) !== 0.00) ? number_format($berat_mutlak, 2, ',', '.') : number_format($berat_mutlak, 0, ',', '.');
    
    // Tentukan warna baris
    $row_class = '';
    if (strpos($item['keterangan'], 'Return') !== false) { // Cek jika mengandung kata 'Return'
        $row_class = 'bg-red-50 text-red-700';
    } elseif ($item['keterangan'] === 'Dibayar') {
        $row_class = 'bg-green-50 text-green-700';
    }
?>
<tr class="border-b <?= $row_class ?>">
    <td class="border border-gray-300 px-3 py-2"><?= $timestamp_display; ?></td>
    <td class="border border-gray-300 px-3 py-2">
        <?= $berat_display; ?> Kg 
        <?php if (strpos($item['keterangan'], 'Return') !== false): ?>
            <span class="text-xs font-semibold">(Minus)</span>
        <?php endif; ?>
    </td>
    <td class="border border-gray-300 px-3 py-2">
        Rp. <?= number_format($gaji_mutlak,0,',','.'); ?> 
        <?php if (strpos($item['keterangan'], 'Return') !== false): ?>
            <span class="text-xs font-semibold">(Potongan)</span>
        <?php endif; ?>
    </td>
    <td class="border border-gray-300 px-3 py-2">
        <span class="font-semibold px-2 py-1 rounded-full text-xs 
            <?php 
                if ($item['keterangan'] === 'Dibayar') echo 'bg-green-200 text-green-900';
                elseif (strpos($item['keterangan'], 'Return') !== false) echo 'bg-red-200 text-red-900';
                else echo 'bg-yellow-200 text-yellow-900';
            ?>">
            <?= htmlspecialchars($item['keterangan']); ?>
        </span>
    </td>
    <td class="border border-gray-300 px-3 py-2 space-x-1">
        <button class="btn-edit bg-yellow-400 hover:bg-yellow-600 text-yellow-900 text-xs px-3 py-1 rounded"
            data-id="<?= $item['id_gaji']; ?>"
            data-tanggal="<?= htmlspecialchars($item['tanggal']); ?>" 
            data-berat="<?= (float)$item['berat_barang_kg']; ?>"
            data-total="<?= (float)$item['total_gaji']; ?>"
            data-status="<?= $item['keterangan']; ?>"
            data-id-stok="<?= (int)$item['id_stok']; ?>"
            data-stok-saat-ini="<?= (float)$item['stok_saat_ini']; ?>">Edit</button>
        <?php 
        // Logic Tombol Return: Muncul jika keterangan mengandung 'Belum Dibayar'
        if (strpos($item['keterangan'], 'Belum Dibayar') !== false): ?>
        <button class="btn-return bg-blue-400 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded"
            data-id="<?= $item['id_gaji']; ?>"
            data-berat="<?= (float)$item['berat_barang_kg']; ?>">Return</button>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</section>
</main>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
<div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
<h3 class="text-lg font-semibold mb-4 text-gray-800">Edit Riwayat Gaji</h3>
<form id="editForm" method="POST" action="Index.php?page=riwayat_gaji&id_pekerja=<?= $id_pekerja; ?>" class="space-y-3">
<input type="hidden" name="action" value="edit_gaji_riwayat">
<input type="hidden" id="id_gaji_edit" name="id_gaji_edit">
<input type="hidden" id="berat_asal" name="berat_asal" value=""> 
<input type="hidden" id="stok_saat_ini" name="stok_saat_ini" value=""> 
<input type="hidden" id="id_stok_edit_hidden" name="id_stok_edit_hidden" value=""> 

<label class="block text-sm text-gray-600">Tanggal & Waktu:</label>
<input type="datetime-local" id="tanggal_edit" name="tanggal" class="w-full border rounded px-3 py-1" required>

<label class="block text-sm text-gray-600">Berat (Kg):</label>
<input type="number" step="0.01" id="berat_barang_kg_edit" name="berat_barang_kg" class="w-full border rounded px-3 py-1" required>
<div id="editError" class="text-xs text-red-600 mt-1 hidden"></div>
<div id="info_stok_revisi" class="text-xs text-gray-500 mt-1 hidden"></div>

<label class="block text-sm text-gray-600">Total Gaji:</label>
<input type="number" id="total_gaji_edit" name="total_gaji_display" class="w-full border rounded px-3 py-1" readonly>
<input type="hidden" id="total_gaji_hidden" name="total_gaji">

<label class="block text-sm text-gray-600">Status:</label>
<select id="keterangan_edit" name="keterangan" class="w-full border rounded px-3 py-1">
<option value="Dibayar">Dibayar</option>
<option value="Belum Dibayar">Belum Dibayar</option>
</select>

<div class="flex justify-end space-x-2 pt-3">
<button type="button" onclick="deleteEntry()" class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded">Hapus</button>
<button type="button" onclick="closeModal()" class="bg-gray-400 hover:bg-gray-500 text-white text-sm px-3 py-1 rounded">Batal</button>
<button type="submit" id="btnSubmitEdit" class="bg-yellow-400 hover:bg-yellow-300 text-yellow-900 text-sm px-3 py-1 rounded">Simpan</button>
</div>
</form>
</div>
</div>

<div id="returnModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
<div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
<h3 class="text-lg font-semibold mb-4 text-gray-800">Return Barang</h3>
<form id="returnForm" method="POST" action="Index.php?page=riwayat_gaji&id_pekerja=<?= $id_pekerja; ?>" class="space-y-3">
<input type="hidden" name="action" value="return_gaji">
<input type="hidden" id="id_gaji_return" name="id_gaji_return">
<div class="text-sm mb-3">Asal Pengambilan: <span id="berat_asal_info" class="font-bold"></span></div>

<div class="flex space-x-2 mb-3">
    <div class="w-1/2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Return</label>
        <input type="date" name="tanggal_return" id="tanggal_return" class="w-full px-3 py-2 border border-gray-300 rounded" value="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="w-1/2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Return</label>
        <input type="time" name="waktu_return" id="waktu_return" class="w-full px-3 py-2 border border-gray-300 rounded" value="<?= date('H:i') ?>" required>
    </div>
</div>

<label class="block text-sm text-gray-600">Jumlah dikembalikan (Kg):</label>
<input type="number" step="0.01" min="0.01" id="berat_return" name="berat_return" class="w-full border rounded px-3 py-1" required>
<div id="returnError" class="text-xs text-red-600 mt-1 hidden"></div>

<div class="flex justify-end space-x-2 pt-3">
<button type="button" onclick="closeReturnModal()" class="bg-gray-400 hover:bg-gray-500 text-white text-sm px-3 py-1 rounded">Batal</button>
<button type="submit" id="btnSubmitReturn" class="bg-blue-400 hover:bg-blue-300 text-white text-sm px-3 py-1 rounded">Return</button>
</div>
</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hargaPerKg = <?= HARGA_PER_KG; ?>;
    const beratInputEdit = document.getElementById('berat_barang_kg_edit');
    const totalGajiDisplay = document.getElementById('total_gaji_edit');
    const totalGajiHidden = document.getElementById('total_gaji_hidden');
    const beratAsalHidden = document.getElementById('berat_asal');
    const stokSaatIniHidden = document.getElementById('stok_saat_ini');
    const idStokHidden = document.getElementById('id_stok_edit_hidden');
    const editErrorDiv = document.getElementById('editError');
    const btnSubmitEdit = document.getElementById('btnSubmitEdit');
    const infoStokRevisiDiv = document.getElementById('info_stok_revisi');

    // --- Logika Edit ---
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editModal').classList.remove('hidden');
            
            const beratDB = parseFloat(this.dataset.berat);
            const status = this.dataset.status;
            const stokSaatIni = parseFloat(this.dataset.stokSaatIni);
            const idStok = parseInt(this.dataset.idStok);
            
            // 1. Set Berat Asal (Nilai Mutlak) untuk validasi minimum
            const beratMutlak = Math.abs(beratDB);
            beratAsalHidden.value = beratMutlak;
            stokSaatIniHidden.value = stokSaatIni;
            idStokHidden.value = idStok;
            
            // 2. Isi data modal
            document.getElementById('id_gaji_edit').value = this.dataset.id;
            const dbTimestamp = this.dataset.tanggal;
            document.getElementById('tanggal_edit').value = dbTimestamp.substring(0, 16).replace(' ', 'T'); 
            document.getElementById('keterangan_edit').value = status.includes('(') ? status.substring(0, status.indexOf('(')).trim() : status; // Hapus (Tambah: +X kg)
            
            // 3. Setup input berat & validasi
            beratInputEdit.value = beratMutlak.toFixed(2);
            
            // 4. Update Gaji & Tampilan Catatan
            updateGajiAndValidation(beratMutlak);

            // 5. Nonaktifkan edit untuk entry 'Return'
            if (status.includes('Return')) {
                beratInputEdit.readOnly = true;
                document.getElementById('keterangan_edit').disabled = true;
                infoStokRevisiDiv.classList.add('hidden');
                alert('Entri Return dikunci. Hanya bisa Dihapus.');
            } else {
                 beratInputEdit.readOnly = false;
                 document.getElementById('keterangan_edit').disabled = false;
            }
        });
    });

    // Validasi & Hitung Otomatis saat input berat diubah
    beratInputEdit.addEventListener('input', function() {
        const val = parseFloat(beratInputEdit.value) || 0;
        updateGajiAndValidation(val);
    });
    
    function updateGajiAndValidation(val) {
        const minBerat = parseFloat(beratAsalHidden.value || 0);
        const stokMax = parseFloat(stokSaatIniHidden.value || 0);
        const selisih = val - minBerat;
        
        editErrorDiv.classList.add('hidden');
        infoStokRevisiDiv.classList.add('hidden');
        btnSubmitEdit.disabled = false;
        
        const calculatedGaji = Math.round(val * hargaPerKg);
        totalGajiDisplay.value = calculatedGaji;
        totalGajiHidden.value = calculatedGaji;
        
        if (val < minBerat) {
            editErrorDiv.textContent = 'Berat tidak boleh berkurang (Min: ' + minBerat.toFixed(2) + ' Kg). Gunakan tombol Return.';
            editErrorDiv.classList.remove('hidden');
            btnSubmitEdit.disabled = true;
            return;
        }

        if (selisih > 0) {
            // Jika berat bertambah, cek stok
            infoStokRevisiDiv.classList.remove('hidden');
            
            if (stokMax === 0 && selisih > 0) {
                 editErrorDiv.textContent = 'Stok terkait sudah habis. Tidak dapat menambah berat.';
                 editErrorDiv.classList.remove('hidden');
                 btnSubmitEdit.disabled = true;
                 return;
            } else if (selisih > stokMax) {
                editErrorDiv.textContent = 'Penambahan berat (' + selisih.toFixed(2) + ' kg) melebihi sisa stok yang tersedia (' + stokMax.toFixed(2) + ' kg).';
                editErrorDiv.classList.remove('hidden');
                btnSubmitEdit.disabled = true;
                return;
            } else {
                // Format penambahan tanpa koma jika bilangan bulat
                const selisihFormat = (selisih % 1 !== 0) ? selisih.toFixed(2) : selisih.toFixed(0);
                 infoStokRevisiDiv.textContent = 'Penambahan '+selisihFormat+' kg akan mengurangi stok tersedia.';
            }
        }
    }


    // --- Logika Return (Tidak diubah) ---
    let maxBeratReturn = 0;
    
    document.querySelectorAll('.btn-return').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('returnModal').classList.remove('hidden');
            document.getElementById('id_gaji_return').value = this.dataset.id;
            
            maxBeratReturn = Math.abs(parseFloat(this.dataset.berat));
            document.getElementById('berat_return').value = maxBeratReturn.toFixed(2);
            document.getElementById('berat_asal_info').textContent = maxBeratReturn.toFixed(2) + ' Kg';
            document.getElementById('returnError').classList.add('hidden');
            
            const now = new Date();
            document.getElementById('tanggal_return').value = now.toISOString().slice(0,10);
            document.getElementById('waktu_return').value = now.toTimeString().slice(0,5);
        });
    });
    
    // Validasi input berat return (di modal return)
    const beratReturnInput = document.getElementById('berat_return');
    const returnErrorDiv = document.getElementById('returnError');
    const btnSubmitReturn = document.getElementById('btnSubmitReturn');

    beratReturnInput?.addEventListener('input', () => {
        const val = parseFloat(beratReturnInput.value || '0');
        returnErrorDiv.classList.add('hidden');
        btnSubmitReturn.disabled = false;

        if (val <= 0) {
            returnErrorDiv.textContent = 'Jumlah return harus lebih dari 0.';
            returnErrorDiv.classList.remove('hidden');
            btnSubmitReturn.disabled = true;
        } else if (val > maxBeratReturn) {
            returnErrorDiv.textContent = 'Tidak boleh melebihi berat pengambilan awal (' + maxBeratReturn.toFixed(2) + ' kg).';
            returnErrorDiv.classList.remove('hidden');
            btnSubmitReturn.disabled = true;
        }
    });
});

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
}

function deleteEntry() {
    if (confirm('Yakin ingin menghapus data ini? Jika ini adalah data Pengambilan/Return, stok akan disesuaikan!')) {
        const id = document.getElementById('id_gaji_edit').value;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'Index.php?page=riwayat_gaji&id_pekerja=<?= $id_pekerja; ?>';
        form.innerHTML = `
            <input type="hidden" name="action" value="hapus_gaji_riwayat">
            <input type="hidden" name="id_gaji_hapus" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>