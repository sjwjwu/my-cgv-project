<!-- board_list.php -->

<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB 연결 및 세션 시작 (header.php에 session_start()가 있다고 가정)
include 'db_config.php';
include 'includes/header.php'; 

// -----------------------------------------------------
// PHP 로직 섹션
// -----------------------------------------------------


$movie_id = $_GET['movie_id'] ?? 0; 

$total = 0;
// 게시판 글 전체를 id 내림차순 (최근 글 순서)으로 불러옴
$sql = "SELECT id, userid, title, content, created_at, hits FROM board ORDER BY id DESC";

// SQL 쿼리 실행 및 오류 처리 강화
$result = $conn->query($sql);

if ($result === FALSE) {
    // 쿼리 실패 시 서버 오류 메시지 대신 명확한 에러 출력
    echo "<h1 style='color: red;'>SQL 쿼리 실행 오류 발생!</h1>";
    echo "<p>오류 메시지: " . $conn->error . "</p>";
    include 'includes/footer.php';
    exit;
}

if ($result) {
    $total = $result->num_rows; // 총 글 개수
}
?>

<div class="container board-container">
    <h2 class="board-title">✍🏻 나의 영화 한줄평</h2> 
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="board_write.php?movie_id=<?php echo $movie_id; ?>" class="write-btn">한줄평 남기기</a>    
        <?php else: ?>
        <p>로그인 후 한줄평을 남길 수 있습니다.</p>
    <?php endif; ?>
    <table>
        <tr>
            <th>번호</th>
            <th>한줄평</th>
            <th>작성자</th>
            <th>날짜</th>
            <th>조회수</th>
        </tr>
        <?php 
        $current_number = $total; // 총 글 개수부터 시작하여 거꾸로 번호를 부여

        if ($result && $total > 0) {
            while($row = $result->fetch_assoc()) { 
                $display_title = htmlspecialchars($row['title']);
                if (empty($display_title)) {
                    // 제목이 없을 경우 "제목 없음"으로 표시
                    $display_title = "제목 없음";
                }
        ?>
        <tr>
            <td><?php echo $current_number--; ?></td> 
            <td><a href="board_view.php?id=<?php echo $row['id']; ?>"><?php echo $display_title; ?></a></td> 
            <td><?php echo htmlspecialchars($row['userid']); ?></td> 
            <td><?php echo $row['created_at']; ?></td> 
            <td><?php echo $row['hits']; ?></td> 
        </tr>
        <?php 
            }
        } else {
            // 게시글이 없을 때 출력
            echo "<tr><td colspan='5'>작성된 한줄평이 없습니다.</td></tr>";
        }
        ?>
    </table>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>