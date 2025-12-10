<?php
include 'db_config.php';
include 'includes/header.php'; // session_start() 포함

$id = $_GET['id'] ?? 0; // GET으로 id 받기
if ($id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); window.location.href='board_list.php';</script>";
    exit;
}

// 1. 조회수 1 증가 (세션 기반 중복 증가 방지)
$is_hit = 'board_hit_' . $id;

if (!isset($_SESSION[$is_hit])) {
    $conn->query("UPDATE board SET hits = hits + 1 WHERE id = $id");
    $_SESSION[$is_hit] = true; // 세션에 기록하여 중복 방지
}


// 2. 해당 ID의 글 가져오기
$result = $conn->query("SELECT * FROM board WHERE id = $id");
$row = $result->fetch_assoc(); // 배열로 가져오기

if (!$row) {
    echo "<script>alert('존재하지 않는 게시글입니다.'); window.location.href='board_list.php';</script>";
    exit;
}

// 3. 좋아요 수 조회
$likes_result = $conn->query("SELECT COUNT(*) AS cnt FROM board_likes WHERE board_id = $id");
$like_count = $likes_result->fetch_assoc()['cnt']; // 좋아요 수 저장
?>

<div class="container view-container">
    <h2><?= htmlspecialchars($row['title']) ?></h2> <table>
        <tr><th>글쓴이</th><td><?= htmlspecialchars($row['userid']) ?></td></tr> 
        <tr><th>등록일</th><td><?= $row['created_at'] ?></td></tr> 
        <tr><th>조회수</th><td><?= $row['hits'] ?></td></tr> 
        <tr><th>내용</th><td><?= nl2br(htmlspecialchars($row['content'])) ?></td></tr> 
    </table>
    
    <form method="post" action="board_like.php"> 
        <input type="hidden" name="board_id" value="<?= $id ?>"> 
        <div style="text-align:center;">
            <button type="submit">공감하기 (<?= $like_count ?>)</button> 
        </div>
    </form>
    
    <div class="btn-group">
        <a href='board_list.php'>⬅︎ 목록으로</a> 
        <?php 
        // 현재 로그인 사용자 ID
        $logged_in_user = $_SESSION['username'] ?? null; 
        // 게시글 작성자 ID
        $post_author = $row['userid']; 
        // 관리자 권한 확인
        $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

        if ($is_admin || ($logged_in_user && $logged_in_user === $post_author)): 
        ?>
            <a href='board_delete.php?id=<?= $id ?>' class="write-btn" style="background-color: #e74c3c;" onclick="return confirm('이 리뷰를 삭제하시겠습니까?');">삭제</a>
        <?php endif; ?>
    </div>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>