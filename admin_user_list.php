<?php
include 'db_config.php';
include 'includes/header.php'; 

// --- 관리자 권한 확인 (매우 중요!) ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.location.href='index.php';</script>";
    include 'includes/footer.php';
    exit;
}

$users = []; // 사용자 데이터를 저장할 배열

// 사용자 목록 및 할인 정보 조회
$sql = "SELECT user_id, username, name, student_status, university, discount_rate, email, created_at 
        FROM users 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $row['display_discount'] = $row['discount_rate'] . '%'; // 표시용 할인율
        $users[] = $row;
    }
} else {
    echo "<p class='message error'>회원 정보를 불러오는데 실패했습니다: " . $conn->error . "</p>";
}

?>

<div class="container board-container"> 
    <h2 class="board-title">CGV 회원 목록 조회 (관리자용)</h2>
    <p>총 회원 수: <?php echo count($users); ?>명</p>

    <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>아이디</th>
                        <th>이름</th>
                        <th>대학생 여부</th>
                        <th>학교명</th>
                        <th>적용 할인율</th>
                        <th>이메일</th>
                        <th>가입일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo ($user['student_status'] == 'Y' ? '예' : '아니오'); ?></td>
                            <td><?php echo htmlspecialchars($user['university']); ?></td>
                            <td><?php echo htmlspecialchars($user['display_discount']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>등록된 회원이 없습니다.</p>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>