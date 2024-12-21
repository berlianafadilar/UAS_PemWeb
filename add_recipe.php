<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$materials = $database->materials->find();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe = [
        'product_name' => $_POST['product_name'],
        'ingredients' => [],
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    foreach ($_POST['ingredient'] as $key => $material_id) {
        if (!empty($material_id) && !empty($_POST['amount'][$key])) {
            $recipe['ingredients'][] = [
                'material_id' => new MongoDB\BSON\ObjectId($material_id),
                'amount' => (int)$_POST['amount'][$key]
            ];
        }
    }

    $result = $database->recipes->insertOne($recipe);

    if ($result->getInsertedCount() > 0) {
        header('Location: recipes.php');
        exit;
    } else {
        $error = "Gagal menambahkan resep";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Menu - Dessert Stock Manager</title>
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
                <a href="recipes.php">Daftar Menu</a> |
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <main>
            <div class="form-container">
                <div class="card">
                    <h2><i class="fas fa-plus"></i> Tambah Menu Baru</h2>
                    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nama Menu:</label>
                            <input type="text" name="product_name" required>
                        </div>
                        
                        <div id="ingredients-container">
                            <h3>Bahan-bahan</h3>
                            <div class="ingredient-row">
                                <select name="ingredient[]" required>
                                    <option value="">Pilih Bahan</option>
                                    <?php 
                                    $materials = $database->materials->find();
                                    foreach ($materials as $material): 
                                    ?>
                                    <option value="<?php echo $material->_id ?>"><?php echo $material->material_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="amount[]" placeholder="Jumlah" required>
                                <button type="button" class="add-btn" onclick="addIngredient()">+</button>
                            </div>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="add-btn">Simpan Menu</button>
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
        const materials = <?php 
            $materials = $database->materials->find()->toArray();
            echo json_encode(array_map(function($material) {
                return [
                    'id' => (string)$material->_id,
                    'name' => $material->material_name
                ];
            }, $materials));
        ?>;

        const row = document.createElement('div');
        row.className = 'ingredient-row';
        
        let optionsHtml = '<option value="">Pilih Bahan</option>';
        materials.forEach(material => {
            optionsHtml += `<option value="${material.id}">${material.name}</option>`;
        });

        row.innerHTML = `
            <select name="ingredient[]" required>
                ${optionsHtml}
            </select>
            <input type="number" name="amount[]" placeholder="Jumlah" required>
            <button type="button" class="delete-btn" onclick="this.parentElement.remove()">-</button>
        `;
        
        container.appendChild(row);
    }
    </script>
</body>
</html> 