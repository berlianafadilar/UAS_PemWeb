<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Menggunakan aggregation pipeline dengan $lookup
$pipeline = [
    [
        '$lookup' => [
            'from' => 'materials',
            'localField' => 'ingredients.material_id',
            'foreignField' => '_id',
            'as' => 'material_details'
        ]
    ],
    [
        '$project' => [
            'product_name' => 1,
            'ingredients' => 1,
            'material_details' => 1,
            'created_at' => 1
        ]
    ]
];

$recipes = $database->recipes->aggregate($pipeline);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Menu - Dessert Stock Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Dessert Stock Manager</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['user']; ?> 
            </div>
        </header>

        <main>
            <div class="card">
                <div class="page-header">
                    <a href="dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <div class="action-buttons">
                        <button onclick="window.location.href='add_recipe.php'" class="add-btn">
                        <i class="fas fa-plus"></i> Tambah Menu Baru
                        </button>
                    </div>
                </div>

                <h2><i class="fas fa-book"></i> Daftar Menu</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Bahan-bahan</th>
                            <th>Status Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <span class="product-name"><?php echo $recipe->product_name; ?></span>
                                </div>
                            </td>
                            <td>
                                <ul class="ingredients-list">
                                <?php 
                                foreach ($recipe->ingredients as $ingredient) {
                                    // Cari detail material yang sesuai
                                    $material = null;
                                    foreach ($recipe->material_details as $detail) {
                                        if ($detail->_id == $ingredient->material_id) {
                                            $material = $detail;
                                            break;
                                        }
                                    }
                                    
                                    if ($material) {
                                        $stockStatus = '';
                                        if ($material->stock_quantity < $ingredient->amount) {
                                            $stockStatus = '<span class="status critical">Stok Kurang</span>';
                                        } elseif ($material->stock_quantity < ($ingredient->amount * 2)) {
                                            $stockStatus = '<span class="status warning">Stok Menipis</span>';
                                        }
                                        
                                        echo "<li>{$material->material_name}: {$ingredient->amount} {$material->unit} $stockStatus</li>";
                                    }
                                }
                                ?>
                                </ul>
                            </td>
                            <td>
                                <?php
                                $canMake = true;
                                $minPossible = PHP_INT_MAX;
                                foreach ($recipe->ingredients as $ingredient) {
                                    foreach ($recipe->material_details as $material) {
                                        if ($material->_id == $ingredient->material_id) {
                                            $possible = floor($material->stock_quantity / $ingredient->amount);
                                            $minPossible = min($minPossible, $possible);
                                            if ($material->stock_quantity < $ingredient->amount) {
                                                $canMake = false;
                                            }
                                            break;
                                        }
                                    }
                                }
                                if ($canMake) {
                                    echo "<span class='status good'>Bisa membuat $minPossible produk</span>";
                                } else {
                                    echo "<span class='status critical'>Stok Tidak Cukup</span>";
                                }
                                ?>
                            </td>
                            <td class="actions">
                                <button onclick="window.location.href='edit_recipe.php?id=<?php echo $recipe->_id; ?>'" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="deleteRecipe('<?php echo $recipe->_id; ?>')" class="delete-btn">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    function deleteRecipe(id) {
        if (confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            window.location.href = 'delete_recipe.php?id=' + id;
        }
    }
    </script>
</body>
</html> 