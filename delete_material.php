<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    
    $result = $database->materials->deleteOne(['_id' => $id]);
}

header('Location: dashboard.php');
exit;
?> 