<?php
include 'db_config.php';
include 'includes/header.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); window.location.href='coupon_list.php';</script>";
    exit;
}

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$logged_in_user = $_SESSION['username'] ?? null;

// 1. 쿠폰 정보 가져오기
$stmt = $conn->prepare("SELECT * FROM couponbox WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$coupon = $result->fetch_assoc();

if (!$coupon) {
    echo "<script>alert('존재하지 않는 쿠폰입니다.'); window.location.href='coupon_list.php';</script>";
    exit;
}

// 만료일 계산
$expiration_date = date('Y-m-d', strtotime($coupon['issued_date'] . ' +45 days'));
$is_expired = (strtotime($expiration_date) < time());
$can_use = !$coupon['is_used'] && !$is_expired && ($coupon['userid'] === $logged_in_user);
?>

<div class="container view-container">
    <h2 class="board-title">쿠폰 상세</h2>
    <h3><?= htmlspecialchars($coupon['title']) ?></h3>
    
    <table>
        <tr><th>회원</th><td><?= htmlspecialchars($coupon['userid']) ?></td></tr>
        <tr><th>발급일</th><td><?= $coupon['issued_date'] ?></td></tr>
        <tr><th>만료일</th><td style="color: <?= $is_expired ? 'red' : 'green'; ?>;"><?= $expiration_date ?></td></tr>
        <tr><th>내용</th><td><?= nl2br(htmlspecialchars($coupon['content'])) ?></td></tr>
        <tr><th>상태</th><td>
            <?= $coupon['is_used'] ? "<span class='used' style='color: red;'>사용됨 (".$coupon['used_date'].")</span>" : "미사용" ?>
            <?= $is_expired && !$coupon['is_used'] ? " / <span style='color: red;'>만료됨</span>" : "" ?>
        </td></tr>
    </table>
    
    <div class="button-group" style="text-align: center;">
        
        <?php if ($can_use): ?>
            <a href="coupon_use.php?id=<?= $id ?>" class="write-btn" style="background-color: #2ecc71;">쿠폰 사용하기</a>
        <?php endif; ?>
        
        <a href="coupon_like.php?id=<?= $id ?>" class="write-btn" style="background-color: #3498db;">좋아요 (<?= $coupon['likes'] ?>)</a>
        
        <?php if ($is_admin || $coupon['userid'] === $logged_in_user): ?>
            <a href="coupon_delete.php?id=<?= $id ?>" class="write-btn" style="background-color: #e74c3c;" onclick="return confirm('삭제하시겠습니까?');">삭제</a>
        <?php endif; ?>

        <a href="coupon_list.php" class="write-btn" style="background-color: #555;">목록으로</a>
    </div>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>