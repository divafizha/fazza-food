<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';
include 'koneksi.php';

// Fungsi update status pekerja berdasarkan riwayat
function updateWorkerPaymentStatus($pdo, $id_pekerja)
{
    $sql_check = "SELECT COUNT(*) FROM riwayat_gaji WHERE id_pekerja = ? AND keterangan = 'Belum Dibayar'";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_pekerja]);
    $unpaid_count = $stmt_check->fetchColumn();

    $new_status = ($unpaid_count == 0) ? 'Dibayar' : 'Belum Dibayar';
    $sql_update = "UPDATE pekerja_lepas SET status_pembayaran = ? WHERE id_pekerja = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_status, $id_pekerja]);
}

// Validasi ID
if (!isset($_GET['id_pekerja']) || !is_numeric($_GET['id_pekerja'])) {
    header("Location: Index.php?page=pekerja");
    exit;
}
$id_pekerja = $_GET['id_pekerja'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'edit_gaji_riwayat') {
            $sql = "UPDATE riwayat_gaji 
                    SET tanggal = ?, berat_barang_kg = ?, total_gaji = ?, keterangan = ? 
                    WHERE id_gaji = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['tanggal'],
                $_POST['berat_barang_kg'],
                $_POST['total_gaji'],
                $_POST['keterangan'],
                $_POST['id_gaji_edit']
            ]);
            updateWorkerPaymentStatus($pdo, $id_pekerja);
            $_SESSION['notif'] = ['pesan' => 'Riwayat gaji berhasil diperbarui!', 'tipe' => 'sukses'];
        }

        if ($action === 'hapus_gaji_riwayat') {
            $sql = "DELETE FROM riwayat_gaji WHERE id_gaji = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id_gaji_hapus']]);
            updateWorkerPaymentStatus($pdo, $id_pekerja);
            $_SESSION['notif'] = ['pesan' => 'Data riwayat gaji berhasil dihapus.', 'tipe' => 'sukses'];
        }
    } catch (PDOException $e) {
        $_SESSION['notif'] = ['pesan' => 'Kesalahan database: ' . $e->getMessage(), 'tipe' => 'error'];
    }

    header("Location: Index.php?page=riwayat_gaji&id_pekerja=$id_pekerja");
    exit;
}

// Data pekerja
$stmt_pekerja = $pdo->prepare("SELECT nama_pekerja FROM pekerja_lepas WHERE id_pekerja = ?");
$stmt_pekerja->execute([$id_pekerja]);
$pekerja = $stmt_pekerja->fetch(PDO::FETCH_ASSOC);

if (!$pekerja) {
    $_SESSION['notif'] = ['pesan' => 'Pekerja tidak ditemukan.', 'tipe' => 'error'];
    header("Location: Index.php?page=pekerja");
    exit;
}

// Data riwayat gaji
$stmt_riwayat = $pdo->prepare("SELECT * FROM riwayat_gaji WHERE id_pekerja = ? ORDER BY tanggal DESC");
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
                    <th class="border border-gray-300 px-3 py-2 w-40">Tanggal</th>
                    <th class="border border-gray-300 px-3 py-2 w-40">Berat (Kg)</th>
                    <th class="border border-gray-300 px-3 py-2 w-40">Total Gaji</th>
                    <th class="border border-gray-300 px-3 py-2 w-40">Keterangan</th>
                    <th class="border border-gray-300 px-3 py-2 w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat_list)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">Belum ada riwayat gaji untuk pekerja ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayat_list as $item): ?>
                        <tr class="border-b">
                            <td class="border border-gray-300 px-3 py-2 w-"><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal']))); ?></td>
                            <td class="border border-gray-300 px-3 py-2 w-40"><?= htmlspecialchars($item['berat_barang_kg']); ?> Kg</td>
                            <td class="border border-gray-300 px-3 py-2 w-40">Rp. <?= number_format($item['total_gaji'], 0, ',', '.'); ?></td>
                            <td class="border border-gray-300 px-3 py-2 w-40"><?= htmlspecialchars($item['keterangan']); ?></td>
                            <td class="border border-gray-300 px-3 py-2 w-40">
                                <button 
                                    onclick="openModal(<?= $item['id_gaji']; ?>, '<?= $item['tanggal']; ?>', '<?= $item['berat_barang_kg']; ?>', '<?= $item['total_gaji']; ?>', '<?= $item['keterangan']; ?>')" 
                                    class="bg-yellow-400 hover:bg-yellow-600 text-yellow-900 text-xs px-3 py-1 rounded">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<!-- Modal Popup -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Edit Riwayat Gaji</h3>

        <form id="editForm" method="POST" action="Index.php?page=riwayat_gaji&id_pekerja=<?= $id_pekerja; ?>" class="space-y-3">
            <input type="hidden" name="action" value="edit_gaji_riwayat">
            <input type="hidden" id="id_gaji_edit" name="id_gaji_edit">

            <label class="block text-sm text-gray-600">Tanggal:</label>
            <input type="date" id="tanggal" name="tanggal" class="w-full border rounded px-3 py-1">

            <label class="block text-sm text-gray-600">Berat (Kg):</label>
            <input type="number" step="0.01" id="berat_barang_kg" name="berat_barang_kg" class="w-full border rounded px-3 py-1">

            <label class="block text-sm text-gray-600">Total Gaji:</label>
            <input type="number" id="total_gaji" name="total_gaji" class="w-full border rounded px-3 py-1">

            <label class="block text-sm text-gray-600">Status:</label>
            <select id="keterangan" name="keterangan" class="w-full border rounded px-3 py-1">
                <option value="Dibayar">Dibayar</option>
                <option value="Belum Dibayar">Belum Dibayar</option>
            </select>

            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="deleteEntry()" class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded">Hapus</button>
                <button type="button" onclick="closeModal()" class="bg-gray-400 hover:bg-gray-500 text-white text-sm px-3 py-1 rounded">Batal</button>
                <button type="submit" class="bg-yellow-400 hover:bg-yellow-300 text-yellow-900 text-sm px-3 py-1 rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id, tanggal, berat, total, status) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('id_gaji_edit').value = id;
    document.getElementById('tanggal').value = tanggal;
    document.getElementById('berat_barang_kg').value = berat;
    document.getElementById('total_gaji').value = total;
    document.getElementById('keterangan').value = status;
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function deleteEntry() {
    if (confirm('Yakin ingin menghapus data ini?')) {
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

<script>
const hargaPerKg = 2500; // Tarif terbaru

document.getElementById('berat_barang_kg').addEventListener('input', function () {
    let berat = parseFloat(this.value) || 0;

    // Hitung total gaji = berat × tarif
    let total = berat * hargaPerKg;

    // Masukkan ke input total_gaji
    document.getElementById('total_gaji').value = Math.floor(total);
});
</script>
