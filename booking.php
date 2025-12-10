<?php
include 'db_config.php';
include 'includes/header.php';

date_default_timezone_set('Asia/Seoul');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('영화 예매를 하려면 로그인해야 합니다.'); window.location.href='login.php';</script>";
    exit;
}

$user_id = (int)$_SESSION['user_id']; // 사용자 ID를 정수형으로 강제 변환
$movie_id = (int)($_POST['movie_id'] ?? $_GET['movie_id'] ?? 0);
$movie_title = '영화 선택 필요';
$user_discount_rate = 0; // 초기화

if ($movie_id > 0) {
    // 영화 제목 조회
    $movie_sql = "SELECT title FROM movies WHERE movie_id = ?";
    $movie_stmt = $conn->prepare($movie_sql);
    if ($movie_stmt) {
        $movie_stmt->bind_param("i", $movie_id);
        
        if (!$movie_stmt->execute()) { 
             echo "<script>alert('DB 오류: 영화 정보 조회 실패.'); window.location.href='movie_list.php';</script>"; 
             exit;
        }
        
        $movie_result = $movie_stmt->get_result();
        if ($movie_result->num_rows > 0) {
            $movie_title = $movie_result->fetch_assoc()['title'];
        }
        $movie_stmt->close(); // 스테이트먼트 닫기
    } else {
        echo "<script>alert('DB 영화 조회 준비 오류: " . $conn->error . "');</script>";
    }

    // 사용자 할인율 가져오기
    $discount_sql = "SELECT discount_rate FROM users WHERE user_id = ?";
    $discount_stmt = $conn->prepare($discount_sql);
    if ($discount_stmt) {
        $discount_stmt->bind_param("i", $user_id);
        
        if (!$discount_stmt->execute()) { 
             echo "<script>alert('DB 오류: 할인율 조회 실패.'); window.location.href='movie_list.php';</script>"; 
             exit;
        }
        
        $discount_result = $discount_stmt->get_result();
        $user_discount_rate = $discount_result->fetch_assoc()['discount_rate'] ?? 0;
        $discount_stmt->close(); // 스테이트먼트 닫기
    } else {
        echo "<script>alert('DB 할인율 조회 준비 오류: " . $conn->error . "');</script>";
    }
} else {
    // movie_id가 없을 경우 목록 페이지로 유도 (GET 요청 시 발생)
    echo "<script>alert('예매할 영화를 먼저 선택해 주세요.'); window.location.href='movie_list.php';</script>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movie_id_post = $movie_id; // 이미 상단에서 결정된 $movie_id 사용
    $showing_id_post = $conn->real_escape_string($_POST['showing_id'] ?? ''); // name="showing_id"로 수신
    $selected_seats = $conn->real_escape_string($_POST['selected_seats'] ?? ''); 
    
    // 좌석 개수 계산
    $seats_array = array_filter(explode(',', $selected_seats));
    $num_tickets = count($seats_array);

    $ticket_price = 12000;
    
    // 최종 금액 계산 (할인율 재적용)
    $discount_factor = 1 - ($user_discount_rate / 100); 
    $total_price_before_discount = $num_tickets * $ticket_price;
    $total_price = round($total_price_before_discount * $discount_factor);
    $discount_amount = $total_price_before_discount - $total_price;

    // 최종 유효성 검사
    if ($num_tickets > 0 && $movie_id_post > 0 && !empty($showing_id_post)) {
        // DB에 showing_id 저장
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, showing_id, seats, num_tickets, total_price, booking_date) 
                                VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt) {
            // ⭐ 바인드 파라미터 수정: showing_id는 INT (i)로 바인딩합니다. ⭐
            $stmt->bind_param("iisisd", $user_id, $movie_id_post, $showing_id_post, $selected_seats, $num_tickets, $total_price);
            
            if ($stmt->execute()) {
                
                // ⭐ 추가: 상영 시간표의 예약 좌석 수 업데이트 ⭐
                $update_stmt = $conn->prepare("UPDATE showings SET booked_seats = booked_seats + ? WHERE showing_id = ?");
                $update_stmt->bind_param("ii", $num_tickets, $showing_id_post);
                $update_stmt->execute();
                $update_stmt->close();
                
                echo "<script>alert('🎉 예매 완료! 할인액: " . number_format($discount_amount) . "원, 최종 금액: " . number_format($total_price) . "원'); window.location.href='mypage.php';</script>";
            } else {
                echo "<script>alert('예매 DB 저장 오류: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('DB 쿼리 준비 오류: " . $conn->error . "');</script>";
        }
    } else {
        // 필수 조건 미충족 시 디버그 팝업 출력
        $debug_msg = "영화 선택: " . ($movie_id_post > 0 ? 'O' : 'X') . ", ";
        $debug_msg .= "시간 선택: " . (!empty($showing_id_post) ? 'O' : 'X') . ", "; 
        $debug_msg .= "좌석 선택: " . ($num_tickets > 0 ? 'O' : 'X');
        
        echo "<script>alert('예매 필수 조건 미충족.\\n(디버그: $debug_msg)');</script>"; 
    }
}

// echo "<div style='background-color:#ffebeb; padding:10px; border:1px solid #c0392b; margin-bottom:15px;'>";
// echo "<h4>[DB 쿼리 디버그 정보]</h4>";
// echo "현재 영화 ID (\$movie_id): <strong>" . $movie_id . "</strong><br>";
// echo "현재 서버 시각 (NOW() 조건 기준): <strong>" . date('Y-m-d H:i:s') . "</strong>";
// echo "</div>";

// DB에서 상영 시간표를 가져오는 쿼리
$showings_sql = "SELECT 
                    showing_id, 
                    show_time, 
                    (total_seats - booked_seats) AS remaining_seats,
                    theater_id
                FROM 
                    showings 
                WHERE 
                    movie_id = ? AND show_time >= NOW() 
                ORDER BY 
                    show_time ASC";
                    
// ⭐⭐ 수정 1: 상영 시간표 쿼리 실행 및 결과 저장 ⭐⭐
$showings_stmt = $conn->prepare($showings_sql);
if ($showings_stmt) {
    $showings_stmt->bind_param("i", $movie_id);
    $showings_stmt->execute();
    $showings_result = $showings_stmt->get_result();
} else {
    // 쿼리 준비 실패 시 빈 결과로 설정
    $showings_result = null; 
}


// 이제 $showings_result를 닫는 코드를 HTML 출력 후로 이동합니다.
// $showings_stmt->close(); // 여기서 닫으면 안 됩니다.
?>


<section class="booking-section">
    <h2>🎫 영화 예매: <?php echo htmlspecialchars($movie_title); ?></h2>
    
    <p style="text-align: right; font-weight: bold; color: #c0392b;">적용 할인율: <?php echo $user_discount_rate; ?>%</p>
    
    <form action="booking.php" method="POST" id="bookingForm">
        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" id="selected_seats_input" name="selected_seats">
        
        <h3>1. 상영 시간 선택</h3>
        <select name="showing_id" id="showtime-select" required>
            <option value="" disabled selected>시간을 선택하세요</option>
            <?php
            if ($showings_result && $showings_result->num_rows > 0) { // $showings_result가 null이 아닌지 확인
                while ($showing = $showings_result->fetch_assoc()) {
                    // YYYY-MM-DD HH:MM 형식으로 시간 포맷
                    $formatted_time = date('Y-m-d H:i', strtotime($showing['show_time']));
                    $seats_info = " ({$showing['remaining_seats']}석 남음, {$showing['theater_id']}관)";
            ?>
                <option value="<?php echo $showing['showing_id']; ?>">
                    <?php echo $formatted_time . $seats_info; ?>
                </option>
            <?php
                }
            } else {
                // 상영 시간이 없을 때 표시
                echo "<option disabled>예매 가능한 상영 시간이 없습니다.</option>";
            }
            if ($showings_stmt) $showings_stmt->close(); // ⭐ 쿼리 실행 완료 후 닫기 ⭐
        ?>
    </select>

        <h3>2. 좌석 선택 (최대 4좌석)</h3>
        <div class="screen-box">SCREEN</div>
        <div class="seat-map" id="seatMap">
            <div id="seat-container" class="seat-container">
                </div>
            <p>선택된 좌석: <span id="selected_seats_display">없음</span></p>
            <p>총 가격 (할인 적용 전): <span id="price_before_discount">0원</span></p>
            <p>최종 결제 금액 (<?php echo $user_discount_rate; ?>% 할인 적용): <span id="total_price_display" style="color: #c0392b; font-size: 1.2em;">0원</span></p>
        </div>
        
        <button type="submit" id="bookBtn" disabled>예매 완료 및 결제</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatContainer = document.getElementById('seat-container');
    const selectedSeatsInput = document.getElementById('selected_seats_input');
    const selectedSeatsDisplay = document.getElementById('selected_seats_display');
    const priceBeforeDiscount = document.getElementById('price_before_discount');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const bookBtn = document.getElementById('bookBtn');
    
    let selectedSeats = [];
    const MAX_SEATS = 4;
    const TICKET_PRICE = 12000;
    const DISCOUNT_RATE = <?php echo $user_discount_rate; ?>; // PHP 변수를 JS로 가져옴

    // 좌석 배열 생성 (A1~H10) - 이전 코드를 기반으로 단순화
    const rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    const cols = 10;
    const reservedSeats = ['A3', 'A4', 'C1', 'H10']; // 예시 예약 좌석

    rows.forEach(row => {
        const rowDiv = document.createElement('div');
        rowDiv.classList.add('seat-row');
        
        const rowLabel = document.createElement('span');
        rowLabel.classList.add('row-label');
        rowLabel.innerText = row;
        rowDiv.appendChild(rowLabel);

        for (let i = 1; i <= cols; i++) {
            const seatId = row + i;
            const seatBtn = document.createElement('button');
            seatBtn.type = 'button';
            seatBtn.classList.add('seat');
            seatBtn.innerText = i;
            seatBtn.dataset.seatId = seatId;
            
            if (reservedSeats.includes(seatId)) {
                seatBtn.classList.add('reserved');
                seatBtn.disabled = true;
            } else {
                seatBtn.addEventListener('click', () => toggleSeat(seatBtn, seatId));
            }

            rowDiv.appendChild(seatBtn);
        }
        seatContainer.appendChild(rowDiv);
    });
    
    // ⭐⭐ 핵심 수정: ID를 'showtime-select'로 변경 ⭐⭐
    const showtimeSelect = document.getElementById('showtime-select');

    function updateDisplay() {
        selectedSeats.sort(); // 정렬
        
        // 폼 필드와 디스플레이 업데이트
        selectedSeatsInput.value = selectedSeats.join(',');
        selectedSeatsDisplay.innerText = selectedSeats.length > 0 ? selectedSeats.join(', ') : '없음';
        
        const count = selectedSeats.length;
        const priceBefore = count * TICKET_PRICE;
        
        // 최종 금액 계산
        const discountFactor = 1 - (DISCOUNT_RATE / 100);
        const finalPrice = Math.round(priceBefore * discountFactor); // 반올림 적용

        priceBeforeDiscount.innerText = priceBefore.toLocaleString() + '원';
        totalPriceDisplay.innerText = finalPrice.toLocaleString() + '원';

        // 상영 시간과 좌석이 모두 선택되었을 때만 버튼 활성화
        const showtimeSelected = showtimeSelect.value !== ""; // ⭐ 수정된 ID 사용 ⭐
        bookBtn.disabled = !(selectedSeats.length > 0 && showtimeSelected);
    }

    function toggleSeat(button, seatId) {
        const index = selectedSeats.indexOf(seatId);

        if (index > -1) {
            selectedSeats.splice(index, 1);
            button.classList.remove('selected');
        } else {
            if (selectedSeats.length < MAX_SEATS) {
                selectedSeats.push(seatId);
                button.classList.add('selected');
            } else {
                alert(`좌석은 최대 ${MAX_SEATS}개까지 선택할 수 있습니다.`);
                return;
            }
        }
        updateDisplay();
    }
    
    // 상영 시간 변경 시에도 버튼 활성화 여부 업데이트
    showtimeSelect.addEventListener('change', updateDisplay); // ⭐ 수정된 ID 사용 ⭐

    updateDisplay(); // 초기 상태 업데이트
});
</script>

<?php
include 'includes/footer.php';
$conn->close();
?>