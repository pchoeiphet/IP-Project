<?php
include 'username.php'; // เชื่อมต่อฐานข้อมูล

// // รับค่าจาก URL
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$minPrice = isset($_GET['minPrice']) ? (int)$_GET['minPrice'] : 0;
$maxPrice = isset($_GET['maxPrice']) ? (int)$_GET['maxPrice'] : 1000000;
$priceSort = isset($_GET['priceSort']) ? $_GET['priceSort'] : '';

// เริ่มคำสั่ง SQL
$sql = "SELECT * FROM equipment WHERE equipment_price_per_unit BETWEEN ? AND ? AND equipment_quantity > 0";
$params = [$minPrice, $maxPrice];
$types = "ii";

// เพิ่มเงื่อนไขค้นหาชื่อสินค้า (LIKE)
if (!empty($searchQuery)) {
    $sql .= " AND equipment_name LIKE CONCAT('%', ?, '%')";
    $params[] = $searchQuery;
    $types .= "s";
}

// เพิ่มเงื่อนไขประเภทสินค้า
if (!empty($categoryFilter)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}

// การเรียงลำดับราคา
if ($priceSort === 'asc') {
    $sql .= " ORDER BY equipment_price_per_unit ASC";
} elseif ($priceSort === 'desc') {
    $sql .= " ORDER BY equipment_price_per_unit DESC";
} else {
    $sql .= " ORDER BY equipment_id"; // เรียงตาม ID เป็นค่าเริ่มต้น
}

// เตรียมและรันคำสั่ง SQL
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_shopping.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="javascrip_member/shopping.js" defer></script>
    <title>Product Equipment</title>
</head>

<body>

    <!-- ✅ นาฟบาร์ด้านบน -->
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
                <img src="image/united-states-of-america.png" alt="EN" class="nav-logo">
            </a>
        </nav>
    </div>

    <!-- ✅ นาฟบาร์หลัก -->
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

    <!-- ✅ แถบค้นหา -->
    <div class="search-section">
        <div class="search-container">
            <form method="GET" action="shopping.php">
                <input type="text" name="q" placeholder="ค้นหา..." class="search-input" value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="filter-icon" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="filter-sidebar" id="filterSidebar">
        <div class="sidebar-header">
            <h2>ระบุความต้องการของคุณ</h2>
            <button class="close-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">

            <label for="category">ประเภทสินค้า:</label>
            <select id="category" class="filter-select">
                <option value="" selected hidden>ทั้งหมด</option>
                <option value="อุปกรณ์วัดและตรวจสุขภาพ">อุปกรณ์วัดและตรวจสุขภาพ</option>
                <option value="อุปกรณ์ช่วยการเคลื่อนไหว">อุปกรณ์ช่วยการเคลื่อนไหว</option>
                <option value="อุปกรณ์สำหรับฟื้นฟูและกายภาพบำบัด">อุปกรณ์สำหรับฟื้นฟูและกายภาพบำบัด</option>
                <option value="อุปกรณ์สุขอนามัย">อุปกรณ์สุขอนามัย</option>
                <option value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ">อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                <option value="อุปกรณ์ปฐมพยาบาล">อุปกรณ์ปฐมพยาบาล</option>
            </select>

            <label for="priceSort">ราคา:</label>
            <select id="priceSort" class="filter-select">
                <option value="" selected hidden>เรียงลำดับราคา</option>
                <option value="first">มากไปน้อย</option>
                <option value="basic">น้อยไปมาก</option>
            </select>

            <label for="">ช่วงราคาสินค้า:</label>
            <div class="price-range">
                <input type="number" id="minPrice" placeholder="ต่ำสุด" min="0" max="1000000" value="0">
                <input type="range" id="minPriceRange" min="0" max="1000000" step="100" value="0" oninput="updateMinPrice()">
                <input type="range" id="maxPriceRange" min="0" max="1000000" step="100" value="1000000" oninput="updateMaxPrice()">
                <input type="number" id="maxPrice" placeholder="สูงสุด" min="0" max="1000000" value="1000000">
            </div>
            <button class="filter-button" id="filterSidebar"onclick="applyFilters()">ใช้ตัวกรอง</button>

        </div>
    </div>


    <!-- ✅ แสดงข้อมูลสินค้า -->
    <div id="prodContian">
        <section class="product-container">
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <div class="product">
                    <a href="product_details.php?id=<?= $row['equipment_id'] ?>">
                        <img src="image/<?= htmlspecialchars($row['equipment_image']) ?>" alt="<?= htmlspecialchars($row['equipment_name']) ?>">
                        <br><br>
                        <p><?= htmlspecialchars($row['equipment_name']) ?></p>
                        <p class="cost">฿ <?= number_format($row['equipment_price_per_unit']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </section>
    </div>

</body>

</html>