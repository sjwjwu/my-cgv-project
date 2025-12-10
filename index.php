<?php
include 'db_config.php';
include 'includes/header.php';

// 1. 현재 상영작 10개 가져오기 
$sql = "SELECT movie_id, title, poster_url, release_date FROM movies WHERE is_showing = 1 ORDER BY release_date DESC LIMIT 10";
$result = $conn->query($sql);

$user_discount_rate = 0;
if (isset($_SESSION['user_id'])) {
    // 로그인 시 할인율을 가져오는 임시 로직 (header.php나 db_config.php에서 처리할 수도 있지만, 여기서는 독립적으로 처리)
    $discount_sql = "SELECT discount_rate FROM users WHERE user_id = ?";
    $discount_stmt = $conn->prepare($discount_sql);
    if ($discount_stmt) {
        $discount_stmt->bind_param("i", $_SESSION['user_id']);
        $discount_stmt->execute();
        $discount_result = $discount_stmt->get_result();
        $user_discount_rate = $discount_result->fetch_assoc()['discount_rate'] ?? 0;
    }
}
?>

<section class="hero-section">
    <h1 class="site-branding"> MY CGV </h1>
    <h2>✨ My Choice, My Cinema! ✨</h2>
    <p>지금 바로 상영 중인 영화를 확인하고 특별 할인(<?php echo $user_discount_rate ?? '0'; ?>%) 혜택을 놓치지 마세요!</p>
    <a href="movie_list.php" class="btn-primary-action">전체 상영작 보러가기</a>
</section>

<section class="movie-grid">
    <h3>🎬 현재 상영작</h3>
    <div class="movie-list">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='movie-item'>";
                echo "<a href='movie_detail.php?id=" . $row["movie_id"] . "'>";
                $poster = !empty($row["poster_url"]) ? htmlspecialchars($row["poster_url"]) : 'img/default_poster.jpg';
                echo "<img src='" . $poster . "' alt='" . htmlspecialchars($row["title"]) . " 포스터'>";
                echo "<h4 class='movie-title'>" . htmlspecialchars($row["title"]) . "</h4>";
                echo "<p class='movie-release'>개봉: " . $row["release_date"] . "</p>";
                echo "</a>";
                echo "</div>";
            }
        } else {
            echo "<p>현재 상영 중인 영화가 없습니다. 관리자에게 문의하세요.</p>";
        }
        ?>
    </div>
</section>

<section class="promo-section">
    <h3>⭐ MY CGV만의 특별 혜택!</h3>
    <div class="promo-grid">
        <div class="promo-card">
            <h4>[학생 할인] 최대 20% 적용!</h4>
            <p>서울여대 학생이라면 추가 할인! 지금 회원가입하고 혜택을 받으세요.</p>
        </div>
        <div class="promo-card">
            <h4>[나의 예매 내역] 한 눈에 확인!</h4>
            <p>마이페이지에서 예매부터 취소까지 모든 내역을 간편하게 관리하세요.</p>
        </div>
        <div class="promo-card">
            <h4>[좌석 선택] 미리 체험하기!</h4>
            <p>실제와 동일한 좌석 배치도를 통해 원하는 자리를 정확히 예매하세요.</p>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
$conn->close();
?>