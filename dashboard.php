<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$materials = $database->materials->find();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Dessert Stock Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Dessert Stock Manager</h1>
            <div class="user-info">
                Selamat Datang, <?php echo $_SESSION['user']; ?>!
                <a href="recipes.php">Daftar Resep</a> 
                <a href="sales.php">Penjualan</a> 
                <a href="stock_prediction.php">Prediksi Stok</a> 
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <main>
            <div class="card">
                    <div class="action-buttons">
                        <button onclick="window.location.href='add_material.php'" class="add-btn">
                            <i class="fas fa-plus"></i> Tambah Bahan Baru
                        </button>
                    </div>

                <h2>Daftar Bahan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo $material->material_name; ?></td>
                            <td><?php echo $material->stock_quantity; ?></td>
                            <td><?php echo $material->unit; ?></td>
                            <td class="actions">
                                <button onclick="window.location.href='edit_material.php?id=<?php echo $material->_id; ?>'" class="edit-btn">Edit</button>
                                <button onclick="deleteMaterial('<?php echo $material->_id; ?>')" class="delete-btn">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    function deleteMaterial(id) {
        if (confirm('Apakah Anda yakin ingin menghapus bahan ini?')) {
            window.location.href = 'delete_material.php?id=' + id;
        }
    }
    </script>
</body>
</html> 