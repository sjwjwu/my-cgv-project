<?php 
include 'db_config.php';
include 'includes/header.php'; // session_start() 포함

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인 후 이용 가능합니다.'); window.location.href='login.php';</script>";
    exit;
}

// 1. URL에서 movie_id 가져오기
$movie_id = $_GET['movie_id'] ?? 0;
if ($movie_id === 0) {
    echo "<script>alert('리뷰할 영화 정보가 없습니다.'); window.location.href='movie_list.php';</script>";
    exit;
}



$movie_title = '알 수 없는 영화';

// 2. 영화 제목 가져오기 (리뷰 대상 명시)
$movie_stmt = $conn->prepare("SELECT title FROM movies WHERE movie_id = ?");
$movie_stmt->bind_param("i", $movie_id);
$movie_stmt->execute();
$movie_result = $movie_stmt->get_result();

if ($movie_result && $movie_result->num_rows > 0) {
    $movie_row = $movie_result->fetch_assoc();
    $movie_title = $movie_row['title'];
}
$movie_stmt->close();

$is_update = false; 
$current_rating = 0.0;
?>
<div class="container write-container">
    <h2>🖊️ <?= htmlspecialchars($movie_title) ?> 리뷰 작성</h2> 
    
    <form method="post" action="board_save.php"> 
        
        <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
        
        <label for="title">제목</label> 
        <input type="text" id="title" name="title" required placeholder="리뷰 제목을 입력하세요 (예: 한줄평)">
        
        <label for="rating">별점 선택 (1.0~5.0점):</label>
        <div class="rating-input">
            <select name="rating" id="rating" required>
                <option value="">별점 선택</option>
                <?php 
                // 5.0부터 1.0까지 0.5점 단위로 옵션 생성
                for ($i = 5.0; $i >= 1.0; $i -= 0.5): 
                    $value = number_format($i, 1);
                    $selected = ($current_rating == $value) ? 'selected' : '';
                ?>
                    <option value="<?php echo $value; ?>" <?= $selected ?>>
                        <?php echo $value; ?>점
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <label for="content">내용</label>
        <textarea id="content" name="content" rows="10" required placeholder="상세한 리뷰 내용을 작성해 주세요."></textarea>
    
        <input type="submit" value="저장" class="submit-btn"> 
    </form>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>