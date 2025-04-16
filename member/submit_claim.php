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

$equipment_name = $_GET['equipment_name'];
$equipment_name = mysqli_real_escape_string($conn, $equipment_name); //ป้องกัน sql injection พวกเครื่องหมายต่างๆ


$sql = "SELECT `order_equipment`.order_equipment_id, `order_equipment`.member_id, `equipment`.equipment_name, `equipment`.equipment_image, `equipment`.equipment_id
        FROM `order_equipment` 
        JOIN `equipment` ON `order_equipment`.equipment_id = `equipment`.equipment_id 
        WHERE `equipment`.equipment_name = '$equipment_name'";

$result = mysqli_query($conn, $sql);


$row  = mysqli_fetch_assoc($result);
// print_r($row);

?>


<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_submit_claim.css">
    <script src="script/submit_claim_script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>Submit Claim</title>
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
                <a href="claim.php">เคลมสินค้า</a>
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
        <div><a href="index.php">หน้าแรก</a></div>
        <div><a href="reservation_car.html">จองคิวรถ</a></div>
        <a href="index.php">
            <img src="image/Logo.png" alt="Logo" class="nav-logo1">
        </a>
        <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
    </nav>
</div>
</head>

<body>

    <h1>รายละเอียดการเคลม/ต่ออายุการใช้งาน</h1>
    <form action="submit_claim_action.php" method="POST">
        <div class="product-details">
            <img src="image/<?php echo $row["equipment_image"]; ?>" alt="product-detail" width="400" height="400"> <br>

            <div class="info-group">
                <input type="hidden" name="equipment" value="<?php echo $row["equipment_id"]; ?>">
                <h2 id="product-name"><?php echo $row["equipment_name"]; ?></h2>
            </div>

            <div class="selected">
                <select id="action" name="action" required>
                    <option value="none" disabled selected>เคลม/ต่ออายุการใช้งาน</option>
                    <option value="เคลม">เคลม</option>
                    <option value="ซ่อม">ซ่อม</option>
                    <option value="ต่ออายุการใช้งาน">ต่ออายุการใช้งาน</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reason">เหตุผล</label>
                <textarea id="reason" name="reason" rows="5" cols="50"
                    placeholder="กรอกเหตุผลที่ต้องการเคลมหรือใช้งานต่อ"></textarea>
            </div>
            <button type="submit">ยืนยัน</button> <!-- เช็กว่าเคลมหรือต่ออายุ และส่งไปยัง executive approve -->
        </div>
    </form>

</body>