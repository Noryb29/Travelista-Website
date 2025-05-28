<?php
session_start();
require_once '../config/config.php';

function checkAdmin() {
    global $pdo;
    
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    $admin_id = $_SESSION['admin_id'];
    $query = "SELECT * FROM admin WHERE admin_id = :admin_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['admin_id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['admin_firstname'] . ' ' . $admin['admin_lastname'];
        return true;
    }

    return false;
}

// If this file is included directly, check admin status and redirect
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if (checkAdmin()) {
        header('Location: ../admin/admin.php');
        exit;
    }
}
?> 