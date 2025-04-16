<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intpro";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// คิวรีข้อมูล claim + ชื่อสมาชิก,
$sql = "SELECT claim.*, 
       CONCAT(member.member_firstname, ' ', member.member_lastname) AS member_fullname, 
       equipment.equipment_name,
       equipment.equipment_image,
       equipment.equipment_quantity,
       equipment.equipment_type,
       equipment.equipment_purchase_price
FROM claim
INNER JOIN member ON claim.member_id = member.member_id
INNER JOIN equipment ON claim.equipment_id = equipment.equipment_id
WHERE claim.claim_approve = 'รออนุมัติ'";
$result = $conn->query($sql);
//แสดงข้อมูลทั้งหมด ในรูปแบบ json แสดงเป็น utf-8  
//echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_UNESCAPED_UNICODE);
// ตรวจสอบข้อผิดพลาดของ Query
if (!$result) {
    die("Error in SQL query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="styletable.css"> -->
    <link rel="stylesheet" href="approve_claim_page.css">
    <link rel="stylesheet" href="filter-sidebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <title>อนุมัติเคลม</title>
</head>

<body>
    <header class="header">

        <div class="logo-section">
            <img src="img/logo.jpg" alt="" class="logo">
            <h1 href="ceo_home_page.html" style="font-family: Itim;">CEO - HOME</h1>
        </div>
        <nav class="nav" style="margin-left: 20%;">
            <a href="approve_page.php" class="nav-item">อนุมัติคำสั่งซื้อ/เช่า</a>
            <a href="approve_claim_page.php" class="nav-item active">อนุมัติเคลม</a>
            <a href="summary_page.php" class="nav-item">สถิติคำสั่งซื้อ/เช่าสินค้า</a>
            <a href="case_report_page.php" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>

    </header>
    <h1 style="text-align: center;">อนุมัติเคลม</h1>

    <div class="search-section">
        <!-- <div class="search-container">
            <input type="text" placeholder="ค้นหา..." class="search-input">
            <button class="search-button">
                <i class="fa-solid fa-magnifying-glass"></i> ไอคอนแว่นขยาย
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
                <!-- <input type="checkbox" id="" checked> เคลมสินค้า
                <br>
                <input type="checkbox" id="" checked> ต่ออายุการใช้งาน -->

                <!-- <label for="filter-quantity-list">จำนวน:</label>
                <select id="filter-quantity-list" class="filter-select">
                    <option value="all" selected>ทั้งหมด</option>
                    <option value="asc">น้อยสุด-มากสุด</option>
                    <option value="desc">มากสุด-น้อยสุด</option>
                </select> -->


                <label for="filter-request-type">ประเภทคำขอ:</label>
                <select id="filter-request-type" class="filter-select">
                    <option value="all" selected>ทั้งหมด</option>
                    <option value="เคลม">เคลม</option>
                    <option value="ซ่อม">ซ่อม</option>
                </select>

                <label for="equipment-filter">ประเภทอุปกรณ์:</label>
                <select name="equipment_type" id="equipment-filter-list" class="filter-select">
                    <option value="">ทั้งหมด</option>
                    <option value="อุปกรณ์วัดและตรวจสุขภาพ">อุปกรณ์วัดและตรวจสุขภาพ</option>
                    <option value="อุปกรณ์ช่วยการเคลื่อนไหว">อุปกรณ์ช่วยการเคลื่อนไหว</option>
                    <option value="อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด">อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</option>
                    <option value="อุปกรณ์ดูแลสุขอนามัย">อุปกรณ์ดูแลสุขอนามัย</option>
                    <option value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ">อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</option>
                    <option value="อุปกรณ์ปฐมพยาบาล">อุปกรณ์ปฐมพยาบาล</option>
                </select>



                <label for="">ช่วงเวลา</label>
                <input class="calendar-selected" id="date1" type="date" placeholder="เลือกวันที่" value=""> ถึง
                <input class="calendar-selected" id="date2" type="date" placeholder="เลือกวันที่" value="">
                
                <label for="sort-date">เรียงลำดับวันที่ส่งคำขอ</label>
                <select id="sort-date" class="filter-select">
                <option value="oldest">เก่าสุด-ล่าสุด</option>
                <option value="latest" selected>ล่าสุด-เก่าสุด</option>
                </select>

                <!-- <label for="claim-status">สถานะการเคลม:</label>
                <select id="claim-status" class="filter-select">
                    <option value="pending">รอการอนุมัติ</option>
                    <option value="approved">อนุมัติแล้ว</option>
                    <option value="rejected">ถูกปฏิเสธ</option>
                </select> -->
            </div>
        </div>
    </div>

    <form action="update_claim.php" method="POST">
    <main class="main-content">
        <table>
            <thead>
                <tr>
                    <th>รูปภาพสินค้า</th>
                    <th>ชื่อสมาชิก</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคาสินค้า(บาท)</th>
                    <th>วันที่ส่งคำขอ</th>
                    <th>ประเภทคำขอ</th>
                    <th>สาเหตุ</th>
                    <!-- <th>จำนวน</th> -->
                    <th style='display: none;'>ประเภทอุปกรณ์</th>
                    <th>
                        อนุมัติทั้งหมด 
                        <input type="radio" name="select-all" id="approve-all" onclick="selectAll('อนุมัติแล้ว')">
                    </th>
                    <th>
                        ปฏิเสธทั้งหมด 
                        <input type="radio" name="select-all" id="reject-all" onclick="selectAll('ถูกปฏิเสธ')">
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td><img src='img/". $row['equipment_image'] . "' alt='อุปกรณ์' width='100'></td>
                            <td>{$row['member_fullname']}</td>
                            <td>{$row['equipment_name']}</td>
                            <td>{$row['equipment_purchase_price']}</td>
                            <td>{$row['claim_date']}</td>
                            <td>{$row['claim_type']}</td>
                            <td>{$row['claim_detail']}</td>
                           <td style='display: none;'>{$row['equipment_type']}</td>

                            <td>

                                <input type='radio' class='approve-radio' name='claim_approve[{$row['claim_id']}]' value='อนุมัติแล้ว'> อนุมัติ
                            </td>
                            <td>
                                <input type='radio' class='reject-radio' name='claim_approve[{$row['claim_id']}]' value='ถูกปฏิเสธ'> ปฏิเสธ
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align: center;'>ไม่มีรายการรออนุมัติ</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="button-group-2">
            <button type="submit" name="confirm_update" class="btn btn-approve" onclick="return confirmUpdate()">ยืนยัน</button>
        </div>
    </main>
</form>
<style>
</style>
<script src="approve_claim_page.js?v=<?php echo time(); ?>"></script>
</script>
</body>
</html>