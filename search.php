<?php
include 'db_config.php';
include 'includes/header.php';

$search_query = '';
$search_results = [];

if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    $search_param = "%" . $search_query . "%";
    
    // 제목 검색
    $sql = "SELECT movie_id, title, poster_url, release_date 
        FROM movies 
        WHERE title LIKE ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
}
?>

<div class="container board-container"> 
    
    <section class="search-content">
        <h2>🔍 영화 검색</h2>
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="영화 제목, 감독, 배우를 검색하세요" value="<?php echo htmlspecialchars($search_query); ?>" required>
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </section>
    
    <section class="search-results">
        <?php 
        $result_count = count($search_results);

        if ($search_query && $result_count > 0) {
            // 검색 카운트
            echo "<h3>'" . htmlspecialchars($search_query) . "' 검색 결과 (" . $result_count . "건)</h3>";
            
            // 포스터 목록
            echo "<div class='movie-list movie-list-small'>";
            foreach($search_results as $movie) {
                echo "<div class='movie-item'>";
                echo "<a href='movie_detail.php?id=" . $movie["movie_id"] . "'>";
                $poster = !empty($movie["poster_url"]) ? htmlspecialchars($movie["poster_url"]) : 'img/default_poster.jpg';
                echo "<img src='" . $poster . "' alt='" . htmlspecialchars($movie["title"]) . " 포스터'>";
                echo "<h4>" . htmlspecialchars($movie["title"]) . "</h4>";
                echo "</a>";
                echo "</div>";
            }
            echo "</div>"; 

        } else if ($search_query && $result_count === 0) {
            // 검색 결과 없음
            echo "<p class='initial-message'>'". htmlspecialchars($search_query) . "'에 대한 검색 결과가 없습니다.</p>";
        } else {
            // 초기 메시지
            echo "<p class='initial-message'>검색어를 입력해 주세요.</p>";
        }
        ?>
    </section>
</div>

<?php
include 'includes/footer.php';
$conn->close();
?>