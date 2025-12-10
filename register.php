<?php
include 'db_config.php';
include 'includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. POST 데이터 정리 및 보안 처리
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $name = $conn->real_escape_string($_POST['name'] ?? ''); // name 필드 추가
    $email = $conn->real_escape_string($_POST['email']);
    
    // 추가된 할인 관련 필드
    $student_status = $conn->real_escape_string($_POST['student_status']); // 대학생 여부 ('Y' 또는 'N')
    $university = $conn->real_escape_string($_POST['university'] ?? '');       // 학교명
    $discount_rate = (int)$_POST['discount_rate']; // JS에서 계산된 최종 할인율

    // 2. 필수 유효성 검사 (서버 측)
    if (empty($_POST['is_id_checked']) || $_POST['is_id_checked'] !== 'true') {
        $message = "ID 중복 확인을 완료해야 합니다.";
    } else {
        // ID 중복 확인 (최종 검증)
        $check_sql = "SELECT user_id FROM users WHERE username = '$username'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $message = "이미 존재하는 사용자 이름입니다.";
        } else {
            // 3. 회원 정보 DB 삽입 (모든 컬럼 포함)
            $insert_sql = "INSERT INTO users (username, password, name, email, student_status, university, discount_rate) 
                           VALUES ('$username', '$password', '$name', '$email', '$student_status', '$university', $discount_rate)";

            if ($conn->query($insert_sql) === TRUE) {
                $message = "🎉 회원 가입이 완료되었습니다. 로그인 페이지로 이동합니다.";
                echo "<script>alert('회원가입 완료! 로그인하세요.'); window.location.href='login.php';</script>";
            } else {
                // DB 오류가 발생하면 자세한 오류 메시지를 출력합니다.
                $message = "DB 오류: " . $conn->error; 
            }
        }
    }
}

// 사용자 정의 함수: 비밀번호 안전도 확인
function check_password_strength($password) {
    if (strlen($password) < 8) return 0;
    
    $score = 0;
    $has_lower = preg_match('/[a-z]/', $password);
    $has_upper = preg_match('/[A-Z]/', $password);
    $has_digit = preg_match('/\d/', $password);
    $has_special = preg_match('/[^A-Za-z0-9\s]/', $password); // 공백 문자 제외한 특수문자
    
    $char_type_count = $has_lower + $has_upper + $has_digit + $has_special;
    
    if ($char_type_count >= 3) {
        if (strlen($password) >= 12) {
            $score = 4; // 매우 강함
        } else {
            $score = 3; // 강함
        }
    } elseif ($char_type_count >= 2) {
        $score = 2; // 보통
    } else {
        $score = 1; // 약함
    }
    
    return $score;
}

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<section class="form-section register-form">
    <h2>📝 회원 가입</h2>
    <?php if ($message): ?>
        <p class="message" style="background-color: <?php echo strpos($message, '완료') !== false ? '#e9ffed' : '#f7e0e0'; ?>;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <form action="register.php" method="POST" id="registerForm">
        
        <label for="username">아이디 (ID):</label>
        <div class="input-group">
            <input type="text" id="username" name="username" required minlength="4">
            <button type="button" id="checkIdBtn">중복 확인</button>
        </div>
        <p id="idCheckResult" class="check-result"></p>
        <input type="hidden" name="is_id_checked" id="isIdChecked" value="">


        <label for="password">비밀번호:</label>
        <input type="password" id="password" name="password" required>
        
        <div class="strength-indicator-group" style="width: 100%; text-align: center; margin: 5px 0;">
            <div id="strength-indicator" class="indicator" 
                 style="
                     /* 바 길이 고정: 100% (max-width 안에서) */
                     width: 100%; 
                     
                     display: inline-block; 

                     /* 텍스트 세로 중앙 정렬을 위한 높이 설정 */
                     height: 25px; 
                     line-height: 25px; 
                     
                     border-radius: 5px; 
                     overflow: hidden; 
                     color: white; /* 텍스트 색상 */
                     font-weight: bold;
                     text-align: center; /* 텍스트 가로 중앙 정렬 */
                     background-color: #e0e0e0; /* 초기 배경색: 약한 회색 */
                 ">
                </div>
        </div>
        <p class="rule-hint">8자 이상, 영문/숫자/특수문자 중 3가지 이상 포함 (공백 불가)</p>

        <label for="password_confirm">비밀번호 확인:</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <p id="pwConfirmResult" class="check-result"></p>

        <label for="name">이름:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">이메일 주소:</label>
        <input type="email" id="email" name="email" required>
        
        <hr style="margin: 20px 0;">

        <label for="student_status">대학생/학생 여부:</label>
        <select id="student_status" name="student_status">
            <option value="N">일반 (할인 미적용)</option>
            <option value="Y">대학생/학생 (10% 할인 적용)</option>
        </select>

        <div id="university-group" style="display: none; margin-top: 15px;">
            <label for="university">학교명 (예: 서울여자대학교):</label>
            <input type="text" id="university" name="university">
            <p class="rule-hint">(*서울여자대학교 학생은 20% 할인 대상입니다.)</p>
            
            
            <p>적용 예상 할인율: <strong id="discountDisplay">0%</strong></p> 
            <input type="hidden" name="discount_rate" id="discountRateInput" value="0">
        </div>

        <button type="submit" id="submitBtn" disabled>가입하기</button>
    </form>
</section>

<script>
$(document).ready(function() {
    let isIdAvailable = false;
    let isPasswordStrong = false;
    let isPasswordConfirmed = false;

    // --- 1. ID 중복 확인 (Ajax) ---
    $('#checkIdBtn').click(function() {
        const username = $('#username').val();
        if (username.length < 4) {
            $('#idCheckResult').text('아이디는 4자 이상이어야 합니다.').css('color', 'red');
            isIdAvailable = false;
            updateSubmitButton();
            return;
        }

        $.ajax({
            url: 'check_duplicate.php',
            type: 'POST',
            data: { username: username },
            success: function(response) {
                if (response === 'available') {
                    $('#idCheckResult').text('사용 가능한 아이디입니다.').css('color', 'green');
                    $('#isIdChecked').val('true');
                    isIdAvailable = true;
                } else if (response === 'duplicate') {
                    $('#idCheckResult').text('이미 사용 중인 아이디입니다.').css('color', 'red');
                    $('#isIdChecked').val('');
                    isIdAvailable = false;
                }
                updateSubmitButton();
            }
        });
    });
    
    // 아이디 수정 시 중복 확인 상태 초기화
    $('#username').on('input', function() {
        $('#idCheckResult').text('').css('color', 'black');
        $('#isIdChecked').val('');
        isIdAvailable = false;
        updateSubmitButton();
    });

    // --- 2. 비밀번호 규칙 및 안전도 검사 ---
    function checkPasswordStrength(password) {
        if (password.length < 8) return 0;
        if (/\s/.test(password)) return 0;

        let score = 0;
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasDigit = /\d/.test(password);
        const hasSpecial = /[^A-Za-z0-9\s]/.test(password);

        let charTypeCount = hasLower + hasUpper + hasDigit + hasSpecial;

        if (charTypeCount >= 3) {
            if (password.length >= 12) {
                score = 4;
            } else {
                score = 3;
            }
        } else if (charTypeCount >= 2) {
            score = 2;
        } else {
            score = 1;
        }
        return score;
    }

    $('#password').on('input', function() {
        const password = $(this).val();
        const score = checkPasswordStrength(password);
        const strengthIndicator = $('#strength-indicator');
        
        isPasswordStrong = (score >= 3);

        let color = '';
        let width = 0; // 이 변수는 이제 사용되지 않으나, 기존 코드 유지 위해 존재
        let text = '';

        if (score === 0) {
            color = 'grey'; width = '25%'; text = '규칙 미준수 (공백 또는 8자 미만)';
            isPasswordStrong = false;
        } else if (score === 1) {
            color = 'red'; width = '25%'; text = '약함';
            isPasswordStrong = false;
        } else if (score === 2) {
            color = 'orange'; width = '50%'; text = '보통';
            isPasswordStrong = false;
        } else if (score === 3) {
            color = 'yellowgreen'; width = '75%'; text = '강함';
            isPasswordStrong = true;
        } else if (score === 4) {
            color = 'green'; width = '100%'; text = '매우 강함';
            isPasswordStrong = true;
        }

        // JS 수정: width 속성은 적용하지 않고, background-color와 text만 적용
        strengthIndicator.css({ 
            'background-color': color,
            'width': '100%' // width를 100%로 강제 고정하여 바 길이가 일정하도록 함
        }).text(text);
        
        checkPasswordConfirm();
        updateSubmitButton();
    });

    // --- 3. 비밀번호 확인 일치 검사 ---
    function checkPasswordConfirm() {
        const pw = $('#password').val();
        const pwConfirm = $('#password_confirm').val();

        if (pwConfirm === '') {
            $('#pwConfirmResult').text('').css('color', 'black');
            isPasswordConfirmed = false;
        } else if (pw === pwConfirm) {
            $('#pwConfirmResult').text('비밀번호가 일치합니다.').css('color', 'green');
            isPasswordConfirmed = true;
        } else {
            $('#pwConfirmResult').text('비밀번호가 일치하지 않습니다.').css('color', 'red');
            isPasswordConfirmed = false;
        }
        updateSubmitButton();
    }
    
    $('#password_confirm').on('input', checkPasswordConfirm);

    // --- 4. 대학생 여부 및 할인율 계산 ---
    function updateDiscount() {
        let rate = 0;
        const status = $('#student_status').val();
        // 소문자로 변환하여 비교 (사용자 입력 오류 방지)
        const universityName = $('#university').val().trim().toLowerCase(); 
        
        if (status === 'Y') {
            rate = 10; // 기본 대학생 할인 10%

            // 서울여대 관련 키워드 검사
            if (universityName.includes('서울여자대학교') || universityName.includes('서울여대') || universityName.includes('seoul women')) {
                rate = 20; // 서울여자대학교는 20% 할인
            }
        }
        
        $('#discountDisplay').text(rate + '%');
        $('#discountRateInput').val(rate); // 서버 전송을 위해 hidden input에 저장
    }

    // 대학생 여부 변경 시
    $('#student_status').change(function() {
        if ($(this).val() === 'Y') {
            $('#university-group').slideDown();
        } else {
            $('#university-group').slideUp();
        }
        updateDiscount(); // 할인율 업데이트
    });

    // 학교명 입력 시
    $('#university').on('input', updateDiscount);

    updateDiscount(); // 초기 상태 업데이트

    // --- 최종 제출 버튼 상태 업데이트 ---
    function updateSubmitButton() {
        if (isIdAvailable && isPasswordStrong && isPasswordConfirmed) {
            $('#submitBtn').prop('disabled', false);
        } else {
            $('#submitBtn').prop('disabled', true);
        }
    }
});
</script>

<?php
include 'includes/footer.php';
$conn->close();
?>