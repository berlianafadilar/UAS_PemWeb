<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$recipes = $database->recipes->find();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = new MongoDB\BSON\ObjectId($_POST['recipe_id']);
    $quantity = (int)$_POST['quantity'];

    // Ambil resep
    $recipe = $database->recipes->findOne(['_id' => $recipe_id]);
    
    // Cek stok bahan
    $can_process = true;
    $materials_to_update = [];
    
    foreach ($recipe->ingredients as $ingredient) {
        $material = $database->materials->findOne(['_id' => $ingredient->material_id]);
        $required_amount = $ingredient->amount * $quantity;
        
        if ($material->stock_quantity < $required_amount) {
            $can_process = false;
            $error = "Stok {$material->material_name} tidak mencukupi";
            break;
        }
        
        $materials_to_update[] = [
            'id' => $ingredient->material_id,
            'amount' => $required_amount
        ];
    }

    if ($can_process) {
        // Catat penjualan
        $sale = [
            'recipe_id' => $recipe_id,
            'quantity' => $quantity,
            'total_amount' => $quantity * ($recipe->price ?? 0),
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $database->sales->insertOne($sale);

        // Update stok
        foreach ($materials_to_update as $material) {
            $database->materials->updateOne(
                ['_id' => $material['id']],
                ['$inc' => ['stock_quantity' => -$material['amount']]]
            );
        }

        header('Location: sales.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Penjualan - Dessert Stock Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-shopping-cart"></i> Catat Penjualan Baru</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Produk:</label>
                <select name="recipe_id" required>
                    <option value="">Pilih Produk</option>
                    <?php foreach ($recipes as $recipe): ?>
                    <option value="<?php echo $recipe->_id ?>"><?php echo $recipe->product_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="quantity" required min="1">
            </div>
            <div class="button-group">
                <button type="submit">Simpan</button>
                <button type="button" onclick="window.location.href='sales.php'" class="cancel-btn">Batal</button>
            </div>
        </form>
    </div>
</body>
</html> 