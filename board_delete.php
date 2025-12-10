<?php
include 'db_config.php';
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: login.php?msg=login_required");
    exit;
}

$id = $_GET['id']; // GET으로 id 받기 [cite: 635]

// 1. 게시글 삭제 쿼리 실행 [cite: 639]
$conn->query("DELETE FROM board WHERE id = $id");

// 2. 리스트 화면으로 이동 [cite: 641]
header("Location: board_list.php");
exit;
?>