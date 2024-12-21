<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data material
$materials = $database->materials->find();
$predictions = [];

foreach ($materials as $material) {
    // Ambil data penjualan 30 hari terakhir
    $thirtyDaysAgo = new MongoDB\BSON\UTCDateTime(time() * 1000 - (30 * 24 * 60 * 60 * 1000));
    
    $pipeline = [
        [
            '$match' => [
                'created_at' => ['$gte' => $thirtyDaysAgo]
            ]
        ],
        [
            '$lookup' => [
                'from' => 'recipes',
                'localField' => 'recipe_id',
                'foreignField' => '_id',
                'as' => 'recipe'
            ]
        ],
        ['$unwind' => '$recipe'],
        ['$unwind' => '$recipe.ingredients'],
        [
            '$match' => [
                'recipe.ingredients.material_id' => $material->_id
            ]
        ],
        [
            '$group' => [
                '_id' => null,
                'total_usage' => [
                    '$sum' => [
                        '$multiply' => ['$quantity', '$recipe.ingredients.amount']
                    ]
                ]
            ]
        ]
    ];

    $result = $database->sales->aggregate($pipeline)->toArray();
    
    $total_usage = $result[0]->total_usage ?? 0;
    $daily_average = $total_usage / 30;
    
    $days_remaining = $material->stock_quantity / ($daily_average ?: 1);
    
    $predictions[] = [
        'material_name' => $material->material_name,
        'current_stock' => $material->stock_quantity,
        'daily_average' => round($daily_average, 2),
        'days_remaining' => round($days_remaining)
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prediksi Stok - Dessert Stock Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Prediksi Stok</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['user']; ?> 
                <a href="dashboard.php">Dashboard</a> 
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <main>
            <table>
                <thead>
                    <tr>
                        <th>Nama Bahan</th>
                        <th>Stok Saat Ini</th>
                        <th>Rata-rata Penggunaan Harian</th>
                        <th>Perkiraan Habis Dalam (Hari)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($predictions as $prediction): ?>
                    <tr class="<?php echo $prediction['days_remaining'] < 7 ? 'warning' : ''; ?>">
                        <td><?php echo $prediction['material_name']; ?></td>
                        <td><?php echo $prediction['current_stock']; ?></td>
                        <td><?php echo $prediction['daily_average']; ?></td>
                        <td><?php echo $prediction['days_remaining']; ?></td>
                        <td>
                            <?php
                            if ($prediction['days_remaining'] < 3) {
                                echo '<span class="status critical">Kritis</span>';
                            } elseif ($prediction['days_remaining'] < 7) {
                                echo '<span class="status warning">Perlu Restock</span>';
                            } else {
                                echo '<span class="status good">Aman</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html> 