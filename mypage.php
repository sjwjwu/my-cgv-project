<?php
// mypage.php

include 'db_config.php';
include 'includes/header.php';

// 로그인 필수 체크
if (!isset($_SESSION['user_id'])) {
    
    echo "<script>alert('마이 페이지를 이용하려면 로그인해야 합니다.'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 1. 사용자 정보 (할인율 포함) 가져오기
$user_sql = "SELECT discount_rate FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();
$user_discount = $user_info['discount_rate'] ?? 0; // 데이터가 없을 경우 0%

// 2. 예매 내역 가져오기
$booking_sql = "
    SELECT 
        b.booking_id, 
        s.show_time,    
        b.seats, 
        b.num_tickets, 
        b.total_price, 
        b.booking_date,
        m.title AS movie_title, 
        m.movie_id
    FROM bookings b
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN showings s ON b.showing_id = s.showing_id 
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->bind_param("i", $user_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result(); 


// 3. 좋아요 누른 영화 목록 가져오기
$like_sql = "
    SELECT 
        l.liked_at, m.title, m.movie_id, m.poster_url, m.release_date
    FROM likes l
    JOIN movies m ON l.movie_id = m.movie_id
    WHERE l.user_id = ?
    ORDER BY l.liked_at DESC
";
$like_stmt = $conn->prepare($like_sql);
$like_stmt->bind_param("i", $user_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result(); 

?>

<section class="mypage-header">
    <h2>👋 안녕하세요, <?php echo htmlspecialchars($username); ?> 님</h2>
    <p>적용 중인 할인율: <strong style="color: #c0392b;"><?php echo $user_discount; ?>%</strong></p>
</section>

<section class="mypage-section">
    <h3>🎫 나의 예매 내역</h3>
    <div class="booking-history">
        <?php if ($booking_result && $booking_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>예매일</th>
                        <th>영화 제목</th>
                        <th>상영 시간</th>
                        <th>좌석</th>
                        <th>매수</th>
                        <th>최종 금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $booking_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></td>
                            <td><a href="movie_detail.php?id=<?php echo $booking['movie_id']; ?>"><?php echo htmlspecialchars($booking['movie_title']); ?></a></td>
                            <td><?php echo htmlspecialchars($booking['show_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                            <td><?php echo $booking['num_tickets']; ?></td>
                            <td><?php echo number_format($booking['total_price']); ?>원</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>아직 예매하신 내역이 없습니다. <a href="movie_list.php">지금 예매하기</a></p>
        <?php endif; ?>
    </div>
</section>

<section class="mypage-section"> 
    <h3>❤️ 내가 찜한 영화</h3>
    <div class="movie-list movie-list-small">
        <?php if ($like_result && $like_result->num_rows > 0): ?>
             <?php while($movie = $like_result->fetch_assoc()): ?>
                 <?php 
                     // 포스터 URL이 없으면 기본 이미지 사용
                     $poster = !empty($movie["poster_url"]) ? htmlspecialchars($movie["poster_url"]) : 'img/default_poster.jpg';
                 ?>
                 <div class='movie-item'>
                     <a href='movie_detail.php?id=<?php echo $movie["movie_id"]; ?>'>
                         <img src='<?php echo $poster; ?>' alt='<?php echo htmlspecialchars($movie["title"]); ?> 포스터'>
                         <h4><?php echo htmlspecialchars($movie["title"]); ?></h4>
                     </a>
                 </div>
             <?php endwhile; ?>
        <?php else: ?>
            <p>좋아요를 누른 영화가 없습니다. <a href="index.php">홈에서 영화를 탐색해보세요.</a></p>
        <?php endif; ?>
    </div>
</section>

<?php
include 'includes/footer.php';
$conn->close();
?>