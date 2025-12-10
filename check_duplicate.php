<?php
// check_duplicate.php

include 'db_config.php'; 

if (isset($_POST['username'])) {
    $username = $conn->real_escape_string($_POST['username']);
    
    $sql = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "duplicate"; // 이미 사용 중
    } else {
        echo "available"; // 사용 가능
    }
}
$conn->close();
?>