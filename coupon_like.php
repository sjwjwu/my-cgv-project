<?php
include 'db_config.php';
session_start();

// 로그인 체크 (쿠폰 좋아요는 누구나 가능하도록 단순화)
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$id = $_GET['id'] ?? 0;

if ($id > 0) {
    // likes 카운트 1 증가
    $stmt = $conn->prepare("UPDATE couponbox SET likes = likes + 1 WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
header("Location: coupon_view.php?id=$id");
exit;
?>