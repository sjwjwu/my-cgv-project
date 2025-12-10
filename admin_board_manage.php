<?php
include 'db_config.php';
include 'includes/header.php'; 

// --- 관리자 권한 확인 (필수!) ---
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if (!$is_admin) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.location.href='index.php';</script>";
    exit;
}

// 1. 모든 리뷰 가져오기 (관리자 모드이므로 전체를 가져옴)
$sql = "SELECT id, userid, title, created_at, hits FROM board ORDER BY id DESC";
$result = $conn->query($sql);

$total_posts = $result ? $result->num_rows : 0;
?>

<div class="container board-container">
    <h2 class="board-title">리뷰 관리 (관리자 모드)</h2>
    <p>총 리뷰 개수: <?php echo $total_posts; ?>개. 모든 게시글에 대한 삭제 권한이 있습니다.</p>
    
    <table>
        <thead>
            <tr>
                <th>번호</th>
                <th>제목</th>
                <th>작성자 ID</th>
                <th>날짜</th>
                <th>조회수</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $current_number = $total_posts;

        if ($result && $total_posts > 0) {
            while($row = $result->fetch_assoc()) { 
                $display_title = htmlspecialchars($row['title']);
                if (empty($display_title)) { $display_title = "제목 없음"; }
        ?>
        <tr>
            <td><?= $current_number-- ?></td>
            <td><a href="board_view.php?id=<?= $row['id'] ?>"><?= $display_title ?></a></td>
            <td><?= htmlspecialchars($row['userid']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= $row['hits'] ?></td>
            <td>
                <a href='board_delete.php?id=<?= $row['id'] ?>' 
                   class="btn-delete" 
                   style="background-color: #c0392b; padding: 5px 10px; color: white; border-radius: 4px; text-decoration: none;"
                   onclick="return confirm('관리자 권한으로 삭제하시겠습니까?');">삭제</a>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='6'>현재 등록된 리뷰가 없습니다.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>

<style>
/* 관리자 테이블 버튼 스타일 추가 */
.btn-delete:hover {
    background-color: #e74c3c !important;
}
</style>