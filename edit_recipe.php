<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: recipes.php');
    exit;
}

$id = new MongoDB\BSON\ObjectId($_GET['id']);
$recipe = $database->recipes->findOne(['_id' => $id]);
$materials = $database->materials->find();

if (!$recipe) {
    header('Location: recipes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update = [
        'product_name' => $_POST['product_name'],
        'ingredients' => [],
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    foreach ($_POST['ingredient'] as $key => $material_id) {
        if (!empty($material_id) && !empty($_POST['amount'][$key])) {
            $update['ingredients'][] = [
                'material_id' => new MongoDB\BSON\ObjectId($material_id),
                'amount' => (int)$_POST['amount'][$key]
            ];
        }
    }

    $result = $database->recipes->updateOne(
        ['_id' => $id],
        ['$set' => $update]
    );

    if ($result->getModifiedCount() > 0) {
        header('Location: recipes.php');
        exit;
    } else {
        $error = "Gagal mengupdate resep";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Resep - Dessert Stock Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Dessert Stock Manager</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['user']; ?> |
                <a href="dashboard.php">Dashboard</a> |
                <a href="recipes.php">Daftar Resep</a> |
                <a href="add_sale.php">Penjualan</a> |
                <a href="stock_prediction.php">Prediksi Stok</a> |
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <main>
            <div class="form-container">
                <div class="card">
                    <h2><i class="fas fa-edit"></i> Edit Resep</h2>
                    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nama Produk:</label>
                            <input type="text" name="product_name" value="<?php echo $recipe->product_name; ?>" required>
                        </div>
                        
                        <div id="ingredients-container">
                            <h3>Bahan-bahan</h3>
                            <?php foreach ($recipe->ingredients as $index => $ingredient): ?>
                            <div class="ingredient-row">
                                <select name="ingredient[]" required>
                                    <option value="">Pilih Bahan</option>
                                    <?php foreach ($materials as $material): ?>
                                    <option value="<?php echo $material->_id ?>" 
                                        <?php echo $ingredient->material_id == $material->_id ? 'selected' : ''; ?>>
                                        <?php echo $material->material_name ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="amount[]" value="<?php echo $ingredient->amount; ?>" placeholder="Jumlah" required>
                                <?php if ($index === 0): ?>
                                    <button type="button" onclick="addIngredient()">+</button>
                                <?php else: ?>
                                    <button type="button" onclick="this.parentElement.remove()">-</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="button-group">
                            <button type="submit">Update</button>
                            <button type="button" onclick="window.location.href='recipes.php'" class="cancel-btn">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    function addIngredient() {
        const container = document.getElementById('ingredients-container');
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        row.innerHTML = `
            <select name="ingredient[]" required>
                <option value="">Pilih Bahan</option>
                <?php foreach ($materials as $material): ?>
                <option value="<?php echo $material->_id ?>"><?php echo $material->material_name ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="amount[]" placeholder="Jumlah" required>
            <button type="button" onclick="this.parentElement.remove()">-</button>
        `;
        container.appendChild(row);
    }
    </script>
</body>
</html> 