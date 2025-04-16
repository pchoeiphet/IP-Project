<?php
session_start();
include 'username.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบว่าเป็น Member หรือ Executive
    $stmt = $conn->prepare("
        SELECT 'member' AS user_type, member_id AS id, member_email AS email, member_password AS password 
        FROM member WHERE member_email = ?
        UNION
        SELECT 'executive' AS user_type, executive_id AS id, executive_email AS email, executive_password AS password 
        FROM executive WHERE executive_email = ?
        UNION
        SELECT 'repair_staff' AS user_type, repair_staff_id  AS id, repair_staff_email AS email, repair_staff_password AS password 
        FROM repair_staff WHERE repair_staff_email = ?
        UNION
        SELECT 'emergency_staff' AS user_type, emergency_staff_id AS id, emergency_staff_email AS email, emergency_staff_password AS password 
        FROM emergency_staff WHERE emergency_staff_email = ?
        UNION
        SELECT 'callcenter_staff' AS user_type, callcenter_staff_id AS id, callcenter_staff_email AS email, callcenter_staff_password AS password 
        FROM callcenter_staff WHERE callcenter_staff_email = ?
    ");
    $stmt->bind_param("sssss", $email, $email, $email, $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect ไปยังหน้าที่เหมาะสม
            if ($user['user_type'] == 'executive') {
                header("Location: executive\history_fixed_page.php");
            } elseif ($user['user_type'] == 'member') {
                header("Location: member/index.php");
            } elseif ($user['user_type'] == 'repair_staff') {
                header("Location: staff/mechanic/car_report/car_report.php");
            } elseif ($user['user_type'] == 'emergency_staff') {
                header("Location: staff/emergency/Timetable/time_table.php");
            } else {
                header("Location: staff/call_center/emergency_report.php");
            }
            exit();
        } else {
            $error = "❌ รหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        $error = "⚠️ ไม่พบบัญชีนี้ในระบบ!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="style_login.css">

</head>

<body>
    <div class="login-container">
        <h1>เข้าสู่ระบบ</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>อีเมล:</label>
            <input type="email" name="email" required>
            <label>รหัสผ่าน:</label>
            <input type="password" name="password" required>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
    </div>

</body>

</html>