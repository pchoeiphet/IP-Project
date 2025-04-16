<?php
//-----------Session and Login-------------
session_start();
include 'username.php';

// ถ้าไม่ได้ล็อกอิน ให้ redirect กลับไปหน้า login
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// เรียก member_id จาก session มาใช้
$member_id = $_SESSION['user_id'];
//------------------------------------------

// ตรวจสอบว่ามีการกดปุ่มค้นหาหรือไม่
$search = isset($_POST['search']) ? trim($_POST['search']) : "";
$hasSearched = isset($_POST['search']); // เช็คว่ามีการค้นหาแล้วหรือยัง

// ค้นหาข้อมูลเฉพาะเมื่อมีการค้นหา
$result = null;
if ($hasSearched) {
    $sql = "SELECT *
            FROM order_emergency_case 
            WHERE order_emergency_case.order_emergency_case_patient_name LIKE ? 
               OR order_emergency_case.order_emergency_case_reason LIKE ?
               OR order_emergency_case.order_emergency_case_hospital_waypoint LIKE ?
               OR order_emergency_case.order_emergency_case_communicant LIKE ?";

    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_order_emergency.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>ค้นหาเคสฉุกเฉิน</title>
</head>

<body>
    <div class="top-navbar">
        <nav class="nav-links">
            <div><a href="order_emergency.php">ชำระเงินเคสฉุกเฉิน</a></div>
            <div><a href="contact.html">ติดต่อเรา</a></div>
            <div class="dropdown">
                <img src="image/user.png" alt="User" class="nav-logo">
                <div class="dropdown-menu">
                    <a href="profile.html">โปรไฟล์</a>
                    <a href="history.php">ประวัติคำสั่งซื้อ</a>
                    <a href="history_ambulance_booking.php">ประวัติการจองรถ</a>
                    <a href="claim.php">เคลมสินค้า</a>
                    <a href="../logout.php">ออกจากระบบ</a>
                </div>
            </div>
            <a href="index.php">
                <img src="image/united-states-of-america.png" alt="Language" class="nav-logo">
            </a>
        </nav>
    </div>

    <!-- Navbar ชั้นล่าง -->
    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
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

    <div class="search-section">
        <div class="search-container">
            <form method="POST" action="order_emergency.php">
                <input type="text" name="search" placeholder="ค้นหา..." class="search-input" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
                <!-- <button type="button" class="reset-btn" onclick="window.location.href='order_emergency.php'">Reset</button> -->
            </form>
        </div>
    </div>

    <?php if ($hasSearched): ?> <!-- แสดงตารางเฉพาะเมื่อมีการค้นหา -->
        <table class="styled-table">
            <tr>
                <th>รหัสรายการเคสฉุกเฉิน</th>
                <th>ชื่อผู้ประสบเหตุ</th>
                <th>ชื่อผู้ติดต่อ</th>
                <th>สาเหตุ/อาการป่วย</th>
                <th>สถานที่ปลายทาง</th>
                <th>วันที่รายงาน</th>
                <th>เวลาที่รายงาน</th>
                <th>ยอดชำระ</th>
                <th>จ่ายเงิน</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['order_emergency_case_id'] ?></td>
                    <td><?= $row['order_emergency_case_patient_name'] ?></td>
                    <td><?= $row['order_emergency_case_communicant'] ?></td>
                    <td><?= $row['order_emergency_case_reason'] ?></td>
                    <td><?= $row['order_emergency_case_hospital_waypoint'] ?></td>
                    <td><?= $row['order_emergency_case_date'] ?></td>
                    <td><?= $row['order_emergency_case_time'] ?></td>
                    <td><?= number_format($row['order_emergency_case_price'] * 1.07, 2) ?> บาท</td>
                    <td>
                        <?php if ($row['order_emergency_case_status'] === "ชำระเงินแล้ว"): ?>
                            <span style="color: gray;">✅ ชำระเงินแล้ว</span>
                        <?php else: ?>
                            <a href="QRpayment_emergency.php?order_id=<?= $row['order_emergency_case_id'] ?>" class="pay-button">
                                ชำระเงิน
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <?php if ($result->num_rows == 0): ?>
            <p>❌ ไม่พบข้อมูลที่ค้นหา</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    if ($hasSearched) {
        $stmt->close();
    }
    $conn->close();
    ?>
</body>

</html>