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
            'from' => 'recipes',
            'localField' => 'recipe_id',
            'foreignField' => '_id',
            'as' => 'recipe'
        ]
    ],
    [
        '$unwind' => '$recipe'
    ],
    [
        '$sort' => [
            'created_at' => -1
        ]
    ]
];

$sales = $database->sales->aggregate($pipeline);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Penjualan - Dessert Stock Manager</title>
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
                        <button onclick="window.location.href='add_sale.php'" class="add-btn">
                            <i class="fas fa-plus"></i> Tambah Penjualan Baru
                        </button>
                    </div>
                </div>

                <h2><i class="fas fa-shopping-cart"></i> Daftar Penjualan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td>
                                <?php 
                                $date = $sale->created_at->toDateTime();
                                $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                setlocale(LC_TIME, 'id_ID');
                                $bulan = [
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                ];
                                echo $date->format('d') . ' ' . 
                                     $bulan[$date->format('m')] . ' ' . 
                                     $date->format('Y H:i') . ' WIB';
                                ?>
                            </td>
                            <td><?php echo $sale->recipe->product_name; ?></td>
                            <td><?php echo $sale->quantity; ?></td>
                            <td class="actions">
                                <button onclick="deleteSale('<?php echo $sale->_id; ?>')" class="delete-btn">
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
    function deleteSale(id) {
        if (confirm('Apakah Anda yakin ingin menghapus penjualan ini?')) {
            window.location.href = 'delete_sale.php?id=' + id;
        }
    }
    </script>
</body>
</html> 