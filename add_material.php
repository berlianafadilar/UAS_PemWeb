<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material = [
        'material_name' => $_POST['material_name'],
        'stock_quantity' => (int)$_POST['stock_quantity'],
        'unit' => $_POST['unit']
    ];

    $result = $database->materials->insertOne($material);

    if ($result->getInsertedCount() > 0) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Gagal menambahkan bahan";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Bahan - Dessert Stock Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Tambah Bahan Baru</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Bahan:</label>
                <input type="text" name="material_name" required>
            </div>
            <div class="form-group">
                <label>Jumlah Stok:</label>
                <input type="number" name="stock_quantity" required>
            </div>
            <div class="form-group">
                <label>Satuan:</label>
                <input type="text" name="unit" required>
            </div>
            <div class="button-group">
                <button type="submit">Simpan</button>
                <button type="button" onclick="window.location.href='dashboard.php'" class="cancel-btn">Batal</button>
            </div>
        </form>
    </div>
</body>
</html> 