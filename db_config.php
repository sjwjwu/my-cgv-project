<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 데이터베이스 연결 설정
$servername = "localhost";
$username = "swu25";       // 사용자 이름 설정
$password = "1234"; 
$dbname = "sample";    // DB 이름

// DB 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    // 연결 실패 시 치명적인 오류 메시지 출력
    die("❌ DB 연결 실패: " . $conn->connect_error . 
        "<br>db_config.php 파일의 \$password 설정을 확인하거나 MySQL 서버가 실행 중인지 확인하세요.");
}
?>