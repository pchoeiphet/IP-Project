<?php 
include_once 'username.php';

// รับค่าจาก GET (ถ้ามี)
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$order_quantity = $_GET['order_equipment_quantity'] ?? '';
$order_price = $_GET['order_equipment_price'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$equipment_type = $_GET['equipment_type'] ?? '';

$sql = "SELECT * FROM order_equipment a 
        inner join equipment e on a.equipment_id = e.equipment_id 
        inner join member m on a.member_id = m.member_id WHERE a.order_equipment_type = 'เช่า' AND (a.order_equipment_approve IS NULL OR a.order_equipment_approve = '') ";



// กรองตามวันที่
if ($start_date && $end_date) {
    $sql .= " AND order_equipment_date BETWEEN '$start_date' AND '$end_date'";
}

// กรองตามช่วงราคา
if ($min_price && $max_price) {
    $sql .= " AND order_equipment_price BETWEEN $min_price AND $max_price";
}

// กรองตามประเภทอุปกรณ์
if ($equipment_type) {
    $sql .= " AND equipment_type = '$equipment_type'";
}


// จัดเรียงตามจำนวน
$order_by = [];
if ($order_quantity) {
    $order_by[] = "order_equipment_quantity $order_quantity";
}



if ($order_price) {
    $order_by[] = "order_equipment_price $order_price";
}
if (!empty($order_by)) {
    $sql .= " ORDER BY " . implode(", ", $order_by);
}

?>




<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="css/approve_page.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

  




    <title>อนุมัติคำสั่งซื้อ/เช่า</title>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="img/logo.jpg" alt="" class="logo">
            <h1 href="ceo_home_page.html" style="font-family: Itim;">CEO - HOME</h1>
        </div>
        <nav class="nav" style="margin-left: 20%;">
            <a href="approve_page.php" class="nav-item active">อนุมัติคำสั่งซื้อ/เช่า</a>
            <a href="approve_claim_page.php" class="nav-item">อนุมัติเคลม</a>
            <a href="summary_page.php" class="nav-item">สถิติคำสั่งซื้อ/เช่าสินค้า</a>
            <a href="case_report_page.php" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>
    </header>
    <br>

    <div class="search-section">

        <!-- <div class="search-container">
            <input type="text" placeholder="ระบุชื่อสินค้า..." class="search-input">
            <button class="search-button">
                <i class="fa-solid fa-magnifying-glass"></i> 
            </button>
        </div> -->
        <div class="filter-icon">
            <i class="fa-solid fa-filter"></i> <!-- ไอคอน Filter -->
        </div>

        <div class="filter-sidebar" id="filterSidebar">
            <div class="sidebar-header">
                <h2>ตัวกรอง</h2>
                <button class="close-sidebar">&times;</button>
            </div>

            <div class="sidebar-content">

                
                <form id="filterForm" method="GET" action="" onsubmit="return false;">
                    <label for="">ช่วงวันที่สั่งสินค้า:</label>
                    <input class="month-selected" name="start_date" id="start_date" type="date" min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" placeholder="วัน/เดือน/ปี" value="<?php echo $start_date; ?>"> ถึง
                    <input class="month-selected" name="end_date"id="end_date" type="date" min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" placeholder="วัน/เดือน/ปี" value="<?php echo $end_date; ?>">

                    <!-- <label for="filter-quantity">จำนวน:</label>
                    <select name="order_quantity" id="filter-quantity-list" class="filter-select">
                        <option value="" selected>ทั้งหมด</option>
                        <option value="" selected hidden>เลือกการจัดเรียงจำนวน</option>
                        <option value="ASC" <?php echo (isset($_GET['order_equipment_quantity']) && $_GET['order_equipment_quantity'] == "ASC") ? "selected" : ""; ?>>น้อยสุด-มากสุด</option>
                        <option value="DESC" <?php echo (isset($_GET['order_equipment_quantity']) && $_GET['order_equipment_quantity'] == "DESC") ? "selected" : ""; ?>>มากสุด-น้อยสุด</option> -->

                    </select>
                    <label for="filter-price">เรียงลำดับราคา:</label>
                    <select name="order_price" id="filter-price-list" class="filter-select">
                        <option value="" selected hidden>เลือกการจัดเรียงราคา</option>
                        <option value="">ทั้งหมด</option>
                        <option value="ASC" <?php echo (isset($_GET['order_equipment_price']) && $_GET['order_equipment_price'] == "ASC") ? "selected" : ""; ?>>น้อยสุด-มากสุด</option>
                        <option value="DESC" <?php echo (isset($_GET['order_equipment_price']) && $_GET['order_equipment_price'] == "DESC") ? "selected" : ""; ?>>มากสุด-น้อยสุด</option>
                    </select>
                    <label for="">ช่วงราคาสินค้า:</label>
                    <div class="price-range">
                        <input type="number" id="minPrice" placeholder="ต่ำสุด" min="0" max="1000000" value="<?php echo $min_price; ?>">
                        <input type="range" id="minPriceRange" min="0" max="100000" step="1"  value="<?php echo $min_price; ?>"
                            oninput="updateMinPrice()">
                        <input type="range" id="maxPriceRange" min="0" max="1000000" step="10"  value="<?php echo $max_price; ?>"
                            oninput="updateMaxPrice()">
                        <input type="number" id="maxPrice" placeholder="สูงสุด" min="100000" max="1000000" value="<?php echo $max_price; ?>">
                    
                    </div>
                    <label for="equipment-filter">ประเภทอุปกรณ์:</label>
                    <select name="equipment_type" id="equipment-filter-list" class="filter-select">
                        <option value="" selected hidden>ประเภทอุปกรณ์</option>
                        <option value="" selected>ทั้งหมด</option>
                        <option value="อุปกรณ์วัดและตรวจสุขภาพ" <?php echo ($equipment_type == "อุปกรณ์วัดและตรวจสุขภาพ") ? "selected" : ""; ?>>อุปกรณ์วัดและตรวจสุขภาพ</option>
                        <option value="อุปกรณ์ช่วยการเคลื่อนไหว" <?php echo ($equipment_type == "อุปกรณ์ช่วยการเคลื่อนไหว") ? "selected" : ""; ?>>อุปกรณ์ช่วยการเคลื่อนไหว</option>
                        <option value="อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด" <?php echo ($equipment_type == "อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด") ? "selected" : ""; ?>>อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</option>
                        <option value="อุปกรณ์ดูแลสุขอนามัย" <?php echo ($equipment_type == "อุปกรณ์ดูแลสุขอนามัย") ? "selected" : ""; ?>>อุปกรณ์ดูแลสุขอนามัย</option>
                        <option value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ" <?php echo ($equipment_type == "อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ") ? "selected" : ""; ?>>อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                        <option value="อุปกรณ์ปฐมพยาบาล" <?php echo ($equipment_type == "อุปกรณ์ปฐมพยาบาล") ? "selected" : ""; ?>>อุปกรณ์ปฐมพยาบาล</option>

                    </select>
                    <button id="btnApplyFilter" class="filter-button" style="margin-top: 10px;">ใช้ตัวกรอง</button>
                    <button id="btnReset" class="filter-button" style="margin-top: 10px;">รีเซ็ต</button>

                    

                </form>
               

                
            </div>
        </div>

    </div>
    <select class="select" name="option" style="margin-left: 2%;" onchange="location = this.value;">
        <option value="approve_page.php" >อนุมัติคำสั่งซื้อ</option>
        <option value="approve_hire_page.php"selected>อนุมัติการเช่า</option>
    </select>

    <main class="main-content">

        <!-- **จำ** เมื่อกดปุ่มยืนยันหน้ายังคงอยู่หน้าเดิม -->
        <form action="save_order.php" method="post" id="approveForm">
            <div class="button-group-2">
                <button onclick="submitApproval(); return false;" class="btn btn-approve" >ยืนยัน</button>
            </div>
            

        </form>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>ชื่ออุปกรณ์</th>
                    <th>ชื่อผู้เช่า</th>
                    <th>จำนวน</th>
                    <th>ระยะเวลาเช่า (เดือน)</th>
                    <th>ราคา (บาท)</th>
                    <th>ราคารวม (บาท)</th>
                    <!-- ปุ่มอนุมัติ/ไม่อนุมัติทั้งหมด -->
                    <th>
                        <input type="radio" onclick="toggleSelectAll('approve')" name="approval_all" class="item-radio" value="approve"> อนุมัติทั้งหมด
                    </th>
                    <th>
                        <input type="radio" onclick="toggleSelectAll('reject')" name="approval_all" class="item-radio" value="reject"> ไม่อนุมัติทั้งหมด
                    </th>
                </tr>
            </thead>
            <tbody>
            <tbody>
    
            </tbody>

                <?php 

                 $result = $conn->query($sql);
                 
                 if ($result && $result->num_rows > 0):  
                    while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr id="row_<?= $row['order_equipment_id'] ?>">
                            <td><img src="img/<?= $row['equipment_image'] ?>" alt="อุปกรณ์" width="100"></td>
                            <td><?= htmlspecialchars($row['equipment_name']) ?></td>
                            <td><?= $row['member_firstname'] . ' ' . $row['member_lastname'] ?></td>
                            <td><?= $row['order_equipment_quantity'] ?></td>
                            <td><?= $row['order_equipment_months'] ?></td>
                            <td><?= $row['order_equipment_price'] ?></td>
                            <td><?= $row['order_equipment_total'] ?></td>
                            <!-- สำหรับแต่ละรายการ -->
                            <td>
                                <input type="radio" name="approval_<?=$row['order_equipment_id']?>" class="item-radio" value="approve" onclick="checkTest(<?=$row['order_equipment_id']?>, 'approve')"> อนุมัติ
                            </td>
                            <td>
                                <input type="radio" name="approval_<?=$row['order_equipment_id']?>" class="item-radio" value="reject" onclick="checkTest(<?=$row['order_equipment_id']?>, 'reject')"> ไม่อนุมัติ
                            </td>     
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; font-weight: bold; color: red;">ไม่มีรายการอนุมัติ</td>
                    </tr>
                <?php endif; ?>

            <tbody>  
        </table>
                    
        

        <!-- **จำ** เมื่อกดปุ่มยืนยันหน้ายังคงอยู่หน้าเดิม -->
        <form action="save_order.php" method="post" id="approveForm">
            <div class="button-group-2">
                <button onclick="submitApproval(); return false;" class="btn btn-approve" >ยืนยัน</button>
            </div>
            

        </form>

    </main>

    

    <script src="approve_hire_page.js">
    </script>
    <script  lang="javascript">
    
    </script>
</body>

</html>