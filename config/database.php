<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->dessert_stock_manager;
} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
}
?> 