<?php
include 'db_config.php';
session_start(); 

if (!isset($_SESSION['username'])) {
    // 로그인되지 않은 경우 처리
    header("Location: login.php?msg=login_required");
    exit;
}

$userid = $_SESSION['username']; 
// POST로 board_id를 받고, 정수형으로 강제 변환하여 보안을 강화합니다.
$board_id = isset($_POST['board_id']) ? (int)$_POST['board_id'] : 0; 

if ($board_id === 0) {
    // board_id가 유효하지 않은 경우
    header("Location: board_list.php");
    exit;
}

// 1. 좋아요 상태 확인 (PreparedStatement 사용으로 보안 강화)
$stmt_check = $conn->prepare("SELECT id FROM board_likes WHERE board_id = ? AND user_id = ?");
$stmt_check->bind_param("is", $board_id, $userid);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) { 
    // 좋아요 추가
    $stmt_insert = $conn->prepare("INSERT INTO board_likes (board_id, user_id) VALUES (?, ?)");
    $stmt_insert->bind_param("is", $board_id, $userid);
    $stmt_insert->execute();
    $stmt_insert->close();
} else { 
    // 좋아요 취소
    $stmt_delete = $conn->prepare("DELETE FROM board_likes WHERE board_id = ? AND user_id = ?");
    $stmt_delete->bind_param("is", $board_id, $userid);
    $stmt_delete->execute();
    $stmt_delete->close();
}

$stmt_check->close();
$conn->close();

// 4. 처리 후, 게시글 상세 보기 화면으로 리다이렉션 (캐시 문제 방지)
header("Location: board_view.php?id=" . $board_id);
exit;
?>