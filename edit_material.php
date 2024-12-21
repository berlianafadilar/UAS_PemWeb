<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$material = $database->materials->findOne(['_id' => $id]);

if (!$material) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update = [
        '$set' => [
            'material_name' => $_POST['material_name'],
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'unit' => $_POST['unit']
        ]
    ];

    $result = $database->materials->updateOne(
        ['_id' => $id],
        $update
    );

    if ($result->getModifiedCount() > 0) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Gagal mengupdate bahan";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Bahan - Dessert Stock Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Edit Bahan</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Bahan:</label>
                <input type="text" name="material_name" value="<?php echo $material->material_name; ?>" required>
            </div>
            <div class="form-group">
                <label>Jumlah Stok:</label>
                <input type="number" name="stock_quantity" value="<?php echo $material->stock_quantity; ?>" required>
            </div>
            <div class="form-group">
                <label>Satuan:</label>
                <input type="text" name="unit" value="<?php echo $material->unit; ?>" required>
            </div>
            <div class="button-group">
                <button type="submit">Update</button>
                <button type="button" onclick="window.location.href='dashboard.php'" class="cancel-btn">Batal</button>
            </div>
        </form>
    </div>
</body>
</html> 