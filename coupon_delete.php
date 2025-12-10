<?php
include 'db_config.php';
session_start();

$id = $_GET['id'] ?? 0;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$logged_in_user = $_SESSION['username'] ?? null;

if ($id > 0 && ($is_admin || $logged_in_user)) {
    // 관리자이거나, 쿠폰의 소유자일 경우에만 삭제 가능
    
    $delete_condition = "id = ?";
    if (!$is_admin) {
        $delete_condition .= " AND userid = ?"; // 관리자가 아니면 본인 쿠폰만 삭제 가능
    }
    
    $stmt = $conn->prepare("DELETE FROM couponbox WHERE $delete_condition");
    
    if ($stmt) {
        if ($is_admin) {
            $stmt->bind_param("i", $id);
        } else {
            $stmt->bind_param("is", $id, $logged_in_user);
        }
        $stmt->execute();
    }
}

header("Location: coupon_list.php");
exit;
?>