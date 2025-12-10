<?php
include 'db_config.php';
include 'includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // 사용자 조회
    $sql = "SELECT user_id, username, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // 비밀번호 확인 (해시 검증)
        if (password_verify($password, $user['password'])) {
            // 로그인 성공
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            // 관리자 권한 부여 로직 
            if ($user['username'] === 'admin' || $user['username'] === 'your_admin_id') {
                $_SESSION['is_admin'] = true;
            } else {
                $_SESSION['is_admin'] = false; // 일반 사용자는 false로 설정
            }
            //
            
            // DB 연결을 닫고, 바로 리디렉션 후 종료
            $conn->close(); 
            header("Location: index.php"); // 홈으로 리디렉션
            exit; // 스크립트 즉시 종료
        } else {
            $message = "비밀번호가 일치하지 않습니다.";
        }
    } else {
        $message = "존재하지 않는 사용자 이름입니다.";
    }
}
?>

<section class="form-section">
    <h2>🔑 로그인</h2>
    <?php if ($message): ?>
        <p class="message" style="background-color: #f7e0e0; color: #c0392b;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="username">아이디:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">로그인</button>
        <p style="margin-top: 20px;">계정이 없으신가요? <a href="register.php">회원 가입</a></p>
    </form>
</section>

<?php
include 'includes/footer.php';
$conn->close();
?>