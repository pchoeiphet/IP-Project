<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] != 'repair_staff') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Member Dashboard</title>
</head>
<body>
    <h2>ยินดีต้อนรับstaff : <?= $_SESSION['user_email']; ?></h2>
    <h2>ยินดีต้อนรับ <?= $_SESSION['id']; ?></h2>
    <a href="logout.php">ออกจากระบบ</a>
</body>
</html>