<?php 
include 'db_config.php';
include 'includes/header.php';

// 관리자 체크 (필수) 
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<script>alert('관리자만 쿠폰을 발급할 수 있습니다.'); window.location.href='coupon_list.php';</script>";
    exit;
}

// 사용자 목록을 가져와 콤보박스에 표시
$user_result = $conn->query("SELECT user_id, username FROM users ORDER BY username ASC");
?>
<div class="container write-container">
    <h2 class="board-title">쿠폰 발급</h2>
    <form method="post" action="coupon_save.php">
        
        <label for="userid">회원 ID:</label>
        <select id="userid" name="userid" required>
            <option value="">회원 ID를 선택하세요</option>
            <?php while($user = $user_result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($user['username']) ?>"><?= htmlspecialchars($user['username']) ?> (ID: <?= $user['user_id'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="title">쿠폰명:</label>
        <input type="text" id="title" name="title" required>
        
        <label for="content">내용: (예: 영화 5,000원 할인)</label>
        <textarea id="content" name="content" rows="5" required></textarea>
        
        <button type="submit" class="submit-btn">발급하기</button>
    </form>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>