<?php

//-----------Session and Login-------------
session_start();
include 'username.php';

// ถ้าไม่ได้ล็อกอิน ให้ redirect กลับไปหน้า login
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// เรียก member_id จาก session มาใช้ :
// $_SESSION['user_id'];
//------------------------------------------

?>

<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_form_ambulance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>หน้าแรก</title>
    <script src="javascrip_member/index.js" defer></script>
</head>
<div class="top-navbar">
    <nav class="nav-links">

        <div><a href="order_emergency.php">ชำระเงินเคสฉุกเฉิน</a></div>
        <div><a href="contact.html">ติดต่อเรา</a></div>
        <div class="dropdown">
            <img src="image/user.png" alt="Logo" class="nav-logo">
            <div class="dropdown-menu">
                <a href="profile.html">โปรไฟล์</a>
                <a href="history.php">ประวัติคำสั่งซื้อ</a>
                <a href="history_ambulance_booking.php">ประวัติการจองรถ</a>
                <a href="claim.php?user_id=<?php echo $user_id; ?>">เคลมสินค้า</a>
                <a href="../logout.php">ออกจากระบบ</a>
            </div>
        </div>
        <a href="index.php">
            <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
        </a>
    </nav>
</div>


<!-- Navbar ชั้นล่าง -->
<div class="main-navbar">
    <nav class="nav-links">
        <div><a href="index.php" style="color: #E88B71;">หน้าแรก</a></div>
        <div><a href="reservation_car.php">จองคิวรถ</a></div>
        <a href="index.php">
            <img src="image/Logo.png" alt="Logo" class="nav-logo1">
        </a>
        <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
    </nav>

    <div class="cart-icon">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
        </a>
    </div>
</div>

<body>
    <div style="text-align: center; margin-top: 20px;">
        <img src="image/อัตราค่าบริการรถ.png" alt="">
    </div>
</body>

</html>