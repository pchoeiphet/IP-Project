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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_product_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>Document</title> <!-- ชื่อเรื่องที่แสดงในแท็บของเบราว์เซอร์ -->
    <script src="javascrip_member/product_details.js" defer></script>
    <?php include 'username.php'; ?>
</head>

<body>
    <!-- แถบเมนูด้านบน -->
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
                    <a href="claim.php">เคลมสินค้า</a>
                    <a href="../logout.php">ออกจากระบบ</a>
                </div>
            </div>
            <a href="index.php">
                <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
            </a>
        </nav>
    </div>

    <!-- แถบเมนูหลักด้านล่าง -->
    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
            <div><a href="reservation_car.php">จองคิวรถ</a></div>
            <a href="index.php">
                <img src="image/Logo.png" alt="Logo" class="nav-logo1">
            </a>
            <div><a href="shopping.php" style="color: #E88B71;">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
        </nav>

        <div class="cart-icon">
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>

    <!-- ส่วนของหน้ารายละเอียดสินค้า -->
    <section class="order">
        <?php
        $ids = $_GET['id'];
        $sql = "SELECT * 
                FROM equipment
                WHERE equipment_id = '$ids'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        ?>
 
        <!-- แสดงภาพสินค้าจากฐานข้อมูล -->
        <div>
            <img src="image/<?= $row['equipment_image'] ?>" alt="">
        </div>

        <div class="quantity">
            <div class="product-container">
                <div class="product-info">
                    <div class="product-details">
                        <h2><?= $row['equipment_name'] ?></h2> <!-- แสดงชื่อสินค้า -->
                        <br>
                        <p class="price"><?= number_format($row['equipment_price_per_unit'], 2) ?> บาท</p> <!-- แสดงราคา -->
                        <br>
                        <p class="detail"><?= nl2br($row['equipment_detail']) ?></p> <!-- แสดงรายละเอียดสินค้า -->
                    </div>
                </div>
            </div>
        </div>

        <div class="quantity-controls">
            <p>จำนวนที่มี: <?= $row['equipment_quantity'] ?> ชิ้น</p> <!-- แสดงจำนวนสินค้าในสต็อก -->
            <p id="product-quantity"></p> <!-- แสดงจำนวนสินค้าที่ผู้ใช้เลือก -->
        </div>

        <!-- ปุ่มที่ให้ผู้ใช้เพิ่มสินค้าลงในตะกร้า หรือยืนยันการสั่งซื้อ -->
        <div class="order-buttons">
            <a class="add-to-cart" href="order.php?id=<?= $row['equipment_id'] ?>">เพิ่มไปยังตะกร้า</a> <!-- ปุ่มเพิ่มสินค้าลงในตะกร้า -->
            <a href="payment.php?id=<?= $row['equipment_id'] ?>&member_id=<?= $_SESSION['user_id'] ?>">ยืนยันการสั่งซื้อ</a> <!-- ปุ่มยืนยันการสั่งซื้อ -->
            <!-- <a href="paymenttest.php?id=<?= $row['equipment_id'] ?>&member_id=1">ยืนยันการสั่งซื้อ</a> -->
        </div>
    </section>
</body>

</html>