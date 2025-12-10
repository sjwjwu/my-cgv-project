<?php
include 'db_config.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); window.location.href='login.php';</script>";
    exit;
}
$userid = $_SESSION['username'];

$sql = "SELECT * FROM couponbox WHERE userid = ? ORDER BY issued_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container board-container">
    <h2 class="board-title">📩 내 쿠폰함 (<?= htmlspecialchars($userid) ?>)</h2>
    
    
    <table>
        <tr><th>쿠폰명</th><th>내용</th><th>발급일</th><th>상태</th><th>상세</th></tr>
        <?php 
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { 
                $status = $row['is_used'] ? "<span style='color: red;'>사용됨</span>" : "미사용";
        ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['content']) ?></td>
            <td><?= $row['issued_date'] ?></td>
            <td><?= $status ?></td>
            <td><a href="coupon_view.php?id=<?= $row['id'] ?>">보기</a></td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='5'>아직 발급받은 쿠폰이 없습니다.</td></tr>";
        }
        ?>
    </table>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>