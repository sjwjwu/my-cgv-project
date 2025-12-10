<?php
include 'db_config.php';
session_start();

// 관리자 체크 (필수)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: coupon_list.php");
    exit;
}

$userid = $_POST['userid'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($userid) || empty($title)) {
    header("Location: coupon_write.php?error=empty");
    exit;
}

// 만료일 계산: 현재 날짜 + 45일
$expiration_date = date('Y-m-d', strtotime('+45 days'));

// 쿼리 준비 및 바인딩
$stmt = $conn->prepare("INSERT INTO couponbox (userid, title, content, expiration_date) VALUES (?, ?, ?, ?)");

if ($stmt) {
    $stmt->bind_param("ssss", $userid, $title, $content, $expiration_date);
    $stmt->execute();
} else {
    error_log("Coupon SAVE failed: " . $conn->error);
}

header("Location: coupon_list.php");
exit;
?>