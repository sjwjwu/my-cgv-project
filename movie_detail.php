<?php
include 'db_config.php';
include 'includes/header.php';

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// 현재 사용자의 user_id (정수형)
$user_id = $_SESSION['user_id'] ?? null; 

// 1. 영화 정보 가져오기
$movie_sql = "SELECT * FROM movies WHERE movie_id = $movie_id";
$movie_result = $conn->query($movie_sql);
$movie = $movie_result ? $movie_result->fetch_assoc() : null;

if (!$movie) {
    echo "<p>영화를 찾을 수 없습니다.</p>";
    include 'includes/footer.php';
    exit;
}

// ----------------------------------------------------------------------

// 2-1. 해당 영화의 평균 평점 및 리뷰 개수 계산
$rating_sql = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS review_count FROM board WHERE movie_id = ?";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $movie_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result()->fetch_assoc();

$avg_rating = number_format($rating_result['avg_rating'] ?? 0.0, 1);
$review_count = $rating_result['review_count'];
$rating_stmt->close();


// 2-2. 별점 시각화 함수 정의
function display_stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $output = '';
    
    // 채워진 별 (fas fa-star)
    $output .= str_repeat('<span class="fa fa-star checked"></span>', $full);
    // 반 별 (fas fa-star-half-alt)
    if ($half) {
        $output .= '<span class="fa fa-star-half-alt checked"></span>';
    }
    // 빈 별 (far fa-star)
    $output .= str_repeat('<span class="far fa-star unchecked"></span>', $empty);
    
    return $output;
}

// ----------------------------------------------------------------------
// 3. 좋아요 기능 처리 (POST 요청이 들어왔을 때 실행)
// ----------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'like' && $user_id) {
    

    // 쿼리 준비
    $stmt_check = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND movie_id = ?");
    $stmt_check->bind_param("ii", $user_id, $movie_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // 이미 좋아요, 취소 (DELETE)
        $stmt_delete = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND movie_id = ?");
        $stmt_delete->bind_param("ii", $user_id, $movie_id);
        $stmt_delete->execute();
    } else {
        // 좋아요 추가 (INSERT)
        $stmt_insert = $conn->prepare("INSERT INTO likes (user_id, movie_id) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $user_id, $movie_id);
        $stmt_insert->execute();
    }
    
    
    header("Location: movie_detail.php?id=" . $movie_id);
    exit;
}

// 4. 좋아요 개수 및 사용자 좋아요 상태 확인 (새로고침 시 업데이트된 상태를 보여줌)
$like_count_sql = "SELECT COUNT(*) as total_likes FROM likes WHERE movie_id = $movie_id";
$like_count_result = $conn->query($like_count_sql);
$total_likes = $like_count_result->fetch_assoc()['total_likes'] ?? 0;

$is_liked = false;
if ($user_id) {
    $user_like_sql = "SELECT * FROM likes WHERE user_id = $user_id AND movie_id = $movie_id";
    $user_like_result = $conn->query($user_like_sql);
    $is_liked = ($user_like_result && $user_like_result->num_rows > 0);
}

// 포스터 URL 처리 (없을 경우 기본 이미지)
$poster_url = !empty($movie['poster_url']) ? htmlspecialchars($movie['poster_url']) : 'img/default_poster.jpg';
?>

<section class="movie-detail">
    <div class="movie-info-header">
        <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> 포스터" class="detail-poster">
        <div class="info-text">
            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
            <p><strong>개봉일:</strong> <?php echo $movie['release_date']; ?></p>
            <p><strong>장르:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
            <p><strong>상영 시간:</strong> <?php echo $movie['duration']; ?>분</p>
            
            
            <div class="movie-rating-summary" style="margin: 15px 0; border-top: 1px solid #eee; padding-top: 10px;">
                <h3>⭐ 평균 평점</h3>
                <div class="rating-display">
                    <div class="stars" style="font-size: 1.3em; color: #f39c12; display: inline-block; margin-right: 5px;">
                        <?php echo display_stars($avg_rating); ?>
                    </div>
                    <span class="score" style="font-weight: bold;"><?php echo $avg_rating; ?> / 5.0</span>
                    <span class="count" style="font-size: 0.9em; color: #777;">(총 <?php echo $review_count; ?>개 리뷰)</span>
                </div>
            </div>
            <form method="POST" action="movie_detail.php?id=<?php echo $movie_id; ?>" class="like-form">
                <input type="hidden" name="action" value="like">
                <button type="submit" <?php echo $user_id ? '' : 'disabled title="로그인 후 이용 가능합니다."'; ?>>
                    <i class="<?php echo $is_liked ? 'fas' : 'far'; ?> fa-heart"></i> 
                    좋아요 (<?php echo $total_likes; ?>)
                </button>
            </form>
            
            <a href="booking.php?movie_id=<?php echo $movie_id; ?>" class="btn-book">예매하기</a>
            
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="board_write.php?movie_id=<?php echo $movie_id; ?>" class="btn-book" style="background-color: #068b6eff; margin-top: 10px;">
                    한줄평 남기기
                </a>
            <?php else: ?>
                <p style="font-size: 0.9em; color: #777; margin-top: 10px;">한줄평은 로그인 후 남길 수 있습니다.</p>
            <?php endif; ?>
            </div>
    </div>
    
    <div class="movie-description">
        <h3>줄거리</h3>
        <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
    </div>
    
    </section>

<?php
include 'includes/footer.php';
$conn->close();
?>