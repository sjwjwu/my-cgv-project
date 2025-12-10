<?php
session_start();
// 로그인 상태 확인
$is_logged_in = isset($_SESSION['user_id']);
// 관리자 플래그 확인
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true; 
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY CGV</title> 
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="<?= $is_admin ? 'admin-mode' : ''; ?>">
    <header>
        <div class="container">
            <h1 class="logo"><a href="index.php">MY CGV</a></h1>
            <nav>
                <ul>
                    <?php if ($is_admin): ?>
                        <li><a href="admin_board_manage.php">리뷰 관리</a></li> 
                        <li><a href="admin_user_list.php">회원 관리</a></li>
                        <li><a href="coupon_list.php">쿠폰함 관리</a></li>
                        <li><a href="logout.php">로그아웃 (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="index.php">홈</a></li>
                        <li><a href="movie_list.php">영화예매</a></li>
                        <li><a href="search.php">검색</a></li>
                        <li><a href="board_list.php">영화리뷰</a></li>
                        <?php if ($is_logged_in): ?>
                            <li><a href="coupon_mine.php">쿠폰함</a></li>
                            <li><a href="mypage.php">마이페이지</a></li>
                            <li><a href="logout.php">로그아웃 (<?php echo $_SESSION['username']; ?>)</a></li>
                        <?php else: ?>
                            <li><a href="login.php">로그인</a></li>
                            <li><a href="register.php">회원 가입</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">