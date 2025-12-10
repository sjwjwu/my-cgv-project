<?php
include 'db_config.php';
include 'includes/header.php';

// 관리자 여부 확인 (관리자만 쿠폰 등록 버튼을 볼 수 있음)
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// 쿠폰 테이블에서 모든 쿠폰을 발급일 순서(최신순)로 가져옵니다.
$sql = "SELECT * FROM couponbox ORDER BY issued_date DESC";
$result = $conn->query($sql);
?>

<div class="container board-container">
    <h2 class="board-title">전체 쿠폰 목록</h2>
    
    <?php if ($is_admin): ?>
        <a href="coupon_write.php" class="write-btn">🎞️ 새 쿠폰 등록</a>
    <?php else: ?>
        <a href="coupon_mine.php" class="write-btn" style="background-color: #3498db;">내 쿠폰함 보기</a>
    <?php endif; ?>

    <table>
        <tr>
            <th>번호</th>
            <th>쿠폰명</th>
            <th>회원 ID</th>
            <th>좋아요</th>
            <th>발급일</th>
            <th>만료일</th>
            <th>상세</th>
        </tr>
        <?php 
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { 
                // 만료일 계산 (발급일 + 45일)
                $issued_date = $row['issued_date'];
                $expiration_date = date('Y-m-d', strtotime($issued_date . ' +45 days'));
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['userid']) ?></td>
            <td><?= $row['likes'] ?></td>
            <td><?= $issued_date ?></td>
            
            <td style="color: <?= (strtotime($expiration_date) < time() && $row['is_used'] == 0) ? 'red' : 'green'; ?>;">
                <?= $expiration_date ?>
            </td>
            
            <td><a href="coupon_view.php?id=<?= $row['id'] ?>">보기</a></td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='7'>등록된 쿠폰이 없습니다.</td></tr>";
        }
        ?>
    </table>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>