<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) { header("Location: login.php"); exit; }
$id = $_GET['id'] ?? 0;

if ($id > 0) {
    // is_used를 1로 변경하고, used_date를 오늘 날짜로 업데이트
    $stmt = $conn->prepare("UPDATE couponbox SET is_used = 1, used_date = CURDATE() WHERE id = ? AND is_used = 0 AND userid = ?");
    
    if ($stmt) {
        $stmt->bind_param("is", $id, $_SESSION['username']);
        $stmt->execute();
    }
}
header("Location: coupon_view.php?id=$id");
exit;
?>