<?php
include 'db_config.php';
include 'includes/header.php'; 

// 최신 개봉일 순서로 정렬
$sql = "SELECT movie_id, title, poster_url, release_date, genre, rating FROM movies WHERE is_showing = 1 ORDER BY release_date DESC";
$result = $conn->query($sql);

// 쿼리 실패 시 오류 처리
if ($result === FALSE) {
    echo "<section class='error-section'><h2 style='color: red;'>데이터베이스 오류 발생!</h2>";
    echo "<p>영화 목록을 불러오는데 실패했습니다. 오류 메시지: " . $conn->error . "</p></section>";
    include 'includes/footer.php';
    exit;
}

$total_movies = $result->num_rows;
?>

<section class="movie-list-section">
    <h2 class="section-title">🎬 현재 상영작 목록 (총 <?php echo $total_movies; ?>편)</h2>
    
    <div class="movie-list movie-list-full">
        <?php
        if ($total_movies > 0) {
            while($row = $result->fetch_assoc()) {
                // 포스터 URL 처리 (DB 값이 없거나 비어 있을 경우 기본 이미지 사용)
                $poster = !empty($row["poster_url"]) ? htmlspecialchars($row["poster_url"]) : 'img/default_poster.jpg';
        ?>
            <div class='movie-item movie-item-full'>
                <a href='movie_detail.php?id=<?php echo $row["movie_id"]; ?>'>
                    <img src='<?php echo $poster; ?>' alt='<?php echo htmlspecialchars($row["title"]); ?> 포스터'>
                </a>
                
                <div class='movie-info-box'>
                    <h4><a href='movie_detail.php?id=<?php echo $row["movie_id"]; ?>'><?php echo htmlspecialchars($row["title"]); ?></a></h4>
                    <p><strong>장르:</strong> <?php echo htmlspecialchars($row["genre"]); ?></p>
                    <p><strong>개봉:</strong> <?php echo $row["release_date"]; ?></p>
                    <p><strong>등급:</strong> <?php echo htmlspecialchars($row["rating"]); ?></p>
                    
                    <a href='booking.php?movie_id=<?php echo $row["movie_id"]; ?>' class='btn-book'>지금 예매하기</a>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<p>현재 상영 중인 영화가 없습니다. 관리자 페이지를 통해 영화를 등록해주세요.</p>";
        }
        ?>
    </div>
</section>

<?php
include 'includes/footer.php';
$conn->close();
?>