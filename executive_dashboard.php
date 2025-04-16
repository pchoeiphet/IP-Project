<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>ยินดีต้อนรับ <?= $_SESSION['user_email']; ?></h2>
    <h2>ยินดีต้อนรับ <?= $_SESSION['user_id']; ?></h2>
    <a href="logout.php">ออกจากระบบ</a>
</body>
</html>