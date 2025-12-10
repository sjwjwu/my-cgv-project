<?php
include 'db_config.php';
session_start();

// 1. 로그인 확인
if (!isset($_SESSION['username'])) {
    header("Location: login.php?msg=login_required");
    exit;
}

// 2. 전송된 데이터 받기
$userid = $_SESSION['username'];
$title = $_POST['title'] ?? '제목 없음';
$content = $_POST['content'] ?? '';
$rating = $_POST['rating'] ?? 0.0; // DECIMAL (d)
$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0; // INT (i)

// $id가 POST로 넘어왔다면 (리뷰 수정 모드)
$id = $_POST['id'] ?? null; 

if ($id) {
    // 3. 수정 (UPDATE) 처리
    
    // SQL Injection 방지를 위해 Prepared Statement 사용
    // 타입 순서: title(s), content(s), rating(d), movie_id(i), id(i), userid(s)
    $stmt = $conn->prepare("UPDATE board SET title=?, content=?, rating=?, movie_id=?, created_at=NOW() WHERE id=? AND userid=?");
    $stmt->bind_param("sdsiis", $title, $content, $rating, $movie_id, $id, $userid);
    
    if ($stmt->execute()) {
        header("Location: board_view.php?id=" . $id);
        exit;
    } else {
        echo "<script>alert('리뷰 수정에 실패했습니다. DB 오류: " . $conn->error . "'); window.location.href='board_view.php?id=" . $id . "';</script>";
        exit;
    }

} else {
    // 4. 새 글 작성 (INSERT) 처리
    
    if ($movie_id === 0) {
        // 영화 ID가 없을 경우 오류 처리 (board_write.php에서 이중 체크)
        echo "<script>alert('리뷰할 영화 정보가 누락되었습니다. (ID: 0 수신)'); window.location.href='movie_list.php';</script>";
        exit;
    }
    
    // 타입 순서: userid(s), title(s), content(s), rating(d), movie_id(i)
    $stmt = $conn->prepare("INSERT INTO board (userid, title, content, rating, movie_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssid", $userid, $title, $content, $rating, $movie_id); 
    
    if ($stmt->execute()) {
        // 성공 시 게시글 목록으로 이동
        header("Location: board_list.php");
        exit;
    } else {
        echo "<script>alert('리뷰 작성에 실패했습니다. DB 오류: " . $conn->error . "'); window.location.href='board_write.php?movie_id=" . $movie_id . "';</script>";
        exit;
    }
}

$conn->close();
?>