<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    
    // Ambil data penjualan sebelum dihapus
    $sale = $database->sales->findOne(['_id' => $id]);
    
    if ($sale) {
        // Ambil resep untuk mendapatkan bahan-bahan
        $recipe = $database->recipes->findOne(['_id' => $sale->recipe_id]);
        
        if ($recipe) {
            // Kembalikan stok bahan yang digunakan
            foreach ($recipe->ingredients as $ingredient) {
                $amount_to_restore = $ingredient->amount * $sale->quantity;
                
                $database->materials->updateOne(
                    ['_id' => $ingredient->material_id],
                    ['$inc' => ['stock_quantity' => $amount_to_restore]]
                );
            }
        }
        
        // Hapus data penjualan
        $database->sales->deleteOne(['_id' => $id]);
    }
}

header('Location: sales.php');
exit; 