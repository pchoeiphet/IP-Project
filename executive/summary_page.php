<?php
include('username.php');

// รับค่าจากฟอร์ม
date_default_timezone_set('Asia/Bangkok');
$selected_month1 = isset($_GET['month1']) ? $_GET['month1'] : date('Y-m');
$selected_month2 = isset($_GET['month2']) ? $_GET['month2'] : date('Y-m');
$selected_gender = isset($_GET['gender']) ? $_GET['gender'] : "ทั้งหมด";
$selected_types = isset($_GET['type']) ? (is_array($_GET['type']) ? $_GET['type'] : [$_GET['type']]) : [];
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;
$selected_province = isset($_GET['province']) ? $_GET['province'] : "ทั้งหมด";
$selected_regions = isset($_GET['region']) ? (is_array($_GET['region']) ? $_GET['region'] : [$_GET['region']]) : [];

// สร้าง WHERE Clause ตามฟิลเตอร์ที่เลือก
// ช่วงราคาสินค้า
$where_clause = "WHERE order_equipment_total BETWEEN $min_price AND $max_price";
// วันที่ซื้อสินค้า
if ($selected_month1 && $selected_month2) {
    $where_clause .= " AND DATE_FORMAT(order_equipment_date, '%Y-%m') BETWEEN '$selected_month1' AND '$selected_month2'";
}
// เพศ
if ($selected_gender !== "ทั้งหมด") {
    $where_clause .= " AND member_gender = '$selected_gender'";
}
// ประเภทสินค้า
if (!empty($selected_types)) {
    $types_str = "'" . implode("','", $selected_types) . "'";
    $where_clause .= " AND equipment_type IN ($types_str)";
}
// จังหวัดที่อยู่ลูกค้า
if ($selected_province !== "ทั้งหมด") {
    $where_clause .= " AND member_province = '$selected_province'";
}
// ภูมิภาคที่อยู่ลูกค้า
if (!empty($selected_regions)) {
    $regions_str = "'" . implode("','", $selected_regions) . "'";
    $where_clause .= " AND member_region IN ($regions_str)";
}

// ---------------------------------------------------------------------------

// Query for regions
$region_query = "SELECT DISTINCT member_region FROM member ORDER BY member_region";
$region_result = $conn->query($region_query);

$region_options = [];
if ($region_result->num_rows > 0) {
    while ($row = $region_result->fetch_assoc()) {
        if (!empty($row['member_region'])) {
            $region_options[] = $row['member_region'];
        }
    }
}

// กำหนดรายชื่อจังหวัดทั้งหมดในประเทศไทย
$province_options = array(
    'กระบี่',
    'กาญจนบุรี',
    'กาฬสินธุ์',
    'กำแพงเพชร',
    'ขอนแก่น',
    'จันทบุรี',
    'ฉะเชิงเทรา',
    'ชลบุรี',
    'ชัยนาท',
    'ชัยภูมิ',
    'ชุมพร',
    'เชียงราย',
    'เชียงใหม่',
    'ตรัง',
    'ตราด',
    'ตาก',
    'นครนายก',
    'นครปฐม',
    'นครพนม',
    'นครราชสีมา',
    'นครศรีธรรมราช',
    'นครสวรรค์',
    'นนทบุรี',
    'นราธิวาส',
    'น่าน',
    'บึงกาฬ',
    'บุรีรัมย์',
    'ปทุมธานี',
    'ประจวบคีรีขันธ์',
    'ปราจีนบุรี',
    'ปัตตานี',
    'พระนครศรีอยุธยา',
    'พะเยา',
    'พังงา',
    'พัทลุง',
    'พิจิตร',
    'พิษณุโลก',
    'เพชรบุรี',
    'เพชรบูรณ์',
    'แพร่',
    'ภูเก็ต',
    'มหาสารคาม',
    'มุกดาหาร',
    'แม่ฮ่องสอน',
    'ยโสธร',
    'ยะลา',
    'ร้อยเอ็ด',
    'ระนอง',
    'ระยอง',
    'ราชบุรี',
    'ลพบุรี',
    'ลำปาง',
    'ลำพูน',
    'เลย',
    'ศรีสะเกษ',
    'สกลนคร',
    'สงขลา',
    'สตูล',
    'สมุทรปราการ',
    'สมุทรสงคราม',
    'สมุทรสาคร',
    'สระแก้ว',
    'สระบุรี',
    'สิงห์บุรี',
    'สุโขทัย',
    'สุพรรณบุรี',
    'สุราษฎร์ธานี',
    'สุรินทร์',
    'หนองคาย',
    'หนองบัวลำภู',
    'อ่างทอง',
    'อำนาจเจริญ',
    'อุดรธานี',
    'อุตรดิตถ์',
    'อุทัยธานี',
    'อุบลราชธานี',
    'กรุงเทพมหานคร'
);

// เรียงลำดับจังหวัดตามตัวอักษร
sort($province_options);

// เริ่มต้นคำสั่ง SQL
$sqrt = "SELECT
        equipment_type,
        SUM(CASE WHEN member_gender = 'ชาย' THEN 1 ELSE 0 END) AS male_count,
        SUM(CASE WHEN member_gender = 'หญิง' THEN 1 ELSE 0 END) AS female_count
        FROM `order_equipment`
        JOIN `equipment` ON `order_equipment`.equipment_id = `equipment`.equipment_id
        JOIN `member` ON `order_equipment`.member_id = `member`.member_id
        $where_clause
        GROUP BY equipment_type";  // ใช้ WHERE 1=1 เพื่อง่ายต่อการต่อคำสั่งเพิ่มเติม


$result = mysqli_query($conn, $sqrt);

// ---------------------------------------------------------------------------------
// เตรียมข้อมูลสำหรับกราฟ
$labels = [];
$maleData = [];
$femaleData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['equipment_type'];
        $maleData[] = $row['male_count'];
        $femaleData[] = $row['female_count'];
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $labels,
        'maleData' => $maleData,
        'femaleData' => $femaleData
    ]);
    exit;
}

// Query ดึงข้อมูลจังหวัด
$province_query = "SELECT DISTINCT member_province FROM member";
$province_result = $conn->query($province_query);

// $province_options = [];
// if ($province_result->num_rows > 0) {
//     while ($row = $province_result->fetch_assoc()) {
//         $province_options[] = $row['member_province'];
//     }
// }

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();



?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="summary_page.js" defer></script>
    <title>สถิติคำสั่งซื้อ/เช่าสินค้า</title>
    <style>
        canvas {
            width: 80% !important;
            height: 60% !important;
            max-width: 800px;
            max-height: 600px;
            margin: auto;
            display: block;
        }

        .filter-container {
            text-align: center;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .age-input {
            width: 60px;
            padding: 8px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 10px 0;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox-item input[type="checkbox"] {
            margin: 0;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="img/logo.jpg" alt="" class="logo">
            <h1 href="ceo_home_page.html" style="font-family: Itim;">CEO - HOME</h1>
        </div>
        <nav class="nav" style="margin-left: 20%;">
            <a href="approve_page.php" class="nav-item">อนุมัติคำสั่งซื้อ/เช่า</a>
            <a href="approve_claim_page.php" class="nav-item">อนุมัติเคลม</a>
            <a href="summary_page.php" class="nav-item active">สถิติคำสั่งซื้อ/เช่าสินค้า</a>
            <a href="case_report_page.php" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>
    </header>

    <main class="main-content">

        <div id="chart-labels" style="display: none;"><?php echo json_encode($labels); ?></div>
        <div id="chart-maleData" style="display: none;"><?php echo json_encode($maleData); ?></div>
        <div id="chart-femaleData" style="display: none;"><?php echo json_encode($femaleData); ?></div>


        <h1 style="text-align: center;">สถิติคำสั่งซื้อ/เช่าสินค้า</h1>
        <div class="search-section">

            <div class="filter-icon">
                <i class="fa-solid fa-filter"></i> <!--ไอคอน Filter-->
            </div>



            <div class="filter-sidebar" id="filterSidebar">
                <div class="sidebar-header">
                    <h2>ตัวกรอง</h2>
                    <button class="close-sidebar">&times;</button>
                </div>

                <div class="sidebar-content">
                    <!-- ใส่ Filter ตรงนี้ -->


                    <label for="calendarSelect">ปี/เดือน - ปี/เดือน:</label>
                    <input class="calendar-selected" id="calendarSelect1" type="text" placeholder="เลือกเดือน" value="<?php echo $selected_month1; ?>">
                    <input class="calendar-selected" id="calendarSelect2" type="text" placeholder="เลือกเดือน" value="<?php echo $selected_month2; ?>">
                    <br>

                    <label for="filter-gender">เพศ:</label>
                    <select id="filter-gender-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_gender == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="ชาย" <?php if ($selected_gender == "ชาย") echo "selected"; ?>>ชาย</option>
                        <option value="หญิง" <?php if ($selected_gender == "หญิง") echo "selected"; ?>>หญิง</option>
                    </select>


                    <label for="filter-type">ประเภทสินค้า:</label>
                    <div class="checkbox-group" id="filter-type-list">
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-1" name="type[]" value="อุปกรณ์วัดและตรวจสุขภาพ" checked
                                <?php if (in_array("อุปกรณ์วัดและตรวจสุขภาพ", $selected_types)) echo "checked"; ?>>
                            <label for="type-1">อุปกรณ์วัดและตรวจสุขภาพ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-2" name="type[]" value="อุปกรณ์ช่วยการเคลื่อนไหว" checked
                                <?php if (in_array("อุปกรณ์ช่วยการเคลื่อนไหว", $selected_types)) echo "checked"; ?>>
                            <label for="type-2">อุปกรณ์ช่วยการเคลื่อนไหว</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-3" name="type[]" value="อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด" checked
                                <?php if (in_array("อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด", $selected_types)) echo "checked"; ?>>
                            <label for="type-3">อุปกรณ์สำหรับการฟื้นฟูและกายภาพบำบัด</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-4" name="type[]" value="อุปกรณ์ดูแลสุขอนามัย" checked
                                <?php if (in_array("อุปกรณ์ดูแลสุขอนามัย", $selected_types)) echo "checked"; ?>>
                            <label for="type-4">อุปกรณ์ดูแลสุขอนามัย</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-5" name="type[]" value="อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ" checked
                                <?php if (in_array("อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ", $selected_types)) echo "checked"; ?>>
                            <label for="type-5">อุปกรณ์ช่วยหายใจและระบบทางเดินหายใจ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="type-6" name="type[]" value="อุปกรณ์ปฐมพยาบาล" checked
                                <?php if (in_array("อุปกรณ์ปฐมพยาบาล", $selected_types)) echo "checked"; ?>>
                            <label for="type-6">อุปกรณ์ปฐมพยาบาล</label>
                        </div>
                    </div>

                    <label for="price">ช่วงราคา :</label>
                    <label for="min_price">ราคา (ต่ำสุด):</label>

                    <input type="number" id="minPrice" class="price-input" name="min_price" value="<?php echo $min_price; ?>" min="0" max="1000000">

                    <label for="max_price">ราคา (สูงสุด):</label>
                    <input type="number" id="maxPrice" class="price-input" name="max_price" value="<?php echo $max_price; ?>" min="0" max="1000000">


                    <label for="filter-province-list">จังหวัด:</label>
                    <select id="filter-province-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_province === "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <?php foreach ($province_options as $province) : ?>
                            <option value="<?php echo htmlspecialchars($province); ?>"
                                <?php if ($selected_province === $province) echo "selected"; ?>>
                                <?php echo htmlspecialchars($province); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="filter-region">ภูมิภาค:</label>
                    <div class="checkbox-group" id="filter-region-list">
                        <div class="checkbox-item">
                            <input type="checkbox" id="region-1" name="region[]" value="ภาคเหนือ" checked
                                <?php if (in_array("ภาคเหนือ", $selected_regions)) echo "checked"; ?>>
                            <label for="region-1">ภาคเหนือ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="region-2" name="region[]" value="ภาคกลาง" checked
                                <?php if (in_array("ภาคกลาง", $selected_regions)) echo "checked"; ?>>
                            <label for="region-2">ภาคกลาง</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="region-3" name="region[]" value="ภาคตะวันออกเฉียงเหนือ" checked
                                <?php if (in_array("ภาคตะวันออกเฉียงเหนือ", $selected_regions)) echo "checked"; ?>>
                            <label for="region-3">ภาคตะวันออกเฉียงเหนือ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="region-4" name="region[]" value="ภาคใต้" checked
                                <?php if (in_array("ภาคใต้", $selected_regions)) echo "checked"; ?>>
                            <label for="region-4">ภาคใต้</label>
                        </div>
                    </div>
                </div>
                <a href="summary_page.php" style="margin-left: 5%; margin-bottom:10%;" class="reset-button" id="reset-button">Reset</a>
            </div>
    </main>

    <canvas id="summary"></canvas>
    <!-- graph -->
    <script>
        // รับข้อมูลจาก PHP เพื่อใช้ในกราฟ
        const labels = <?php echo json_encode($labels); ?>;
        const maleData = <?php echo json_encode($maleData); ?>;
        const femaleData = <?php echo json_encode($femaleData); ?>;

        Chart.defaults.elements.bar.borderRadius = 5;

        // สร้างกราฟด้วย Chart.js
        const mychart = new Chart(document.getElementById("summary"), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ชาย',
                    data: maleData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }, {
                    label: 'หญิง',
                    data: femaleData,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'ประเภทสินค้า'
                        }

                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'จำนวนยอดสินค้า'
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'สรุปยอดขายสินค้า',
                        font: {
                            size: 18
                        }
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                }
            }
        });

        // สคริปต์สำหรับเปิด-ปิด Sidebar
        document.addEventListener("DOMContentLoaded", () => {
            const filterIcon = document.querySelector(".filter-icon");
            const sidebar = document.getElementById("filterSidebar");
            const closeSidebar = document.querySelector(".close-sidebar");

            // เปิด Sidebar
            filterIcon.addEventListener("click", () => {
                sidebar.classList.add("open");
            });

            // ปิด Sidebar
            closeSidebar.addEventListener("click", () => {
                sidebar.classList.remove("open");
            });

            // ปิด Sidebar เมื่อคลิกนอก Sidebar
            document.addEventListener("click", (e) => {
                if (!sidebar.contains(e.target) && !filterIcon.contains(e.target)) {
                    sidebar.classList.remove("open");
                }
            });

            // Add event listeners for region checkboxes
            const regionCheckboxes = document.querySelectorAll('#filter-region-list input[type="checkbox"]');
            regionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Reset province selection when region changes
                    document.getElementById("filter-province-list").value = "ทั้งหมด";
                    updateFilters();
                });
            });
        });

        // ตั้งค่า Flatpickr สำหรับ calendarSelect1
        let calendar1Instance = flatpickr("#calendarSelect1", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            defaultDate: "<?php echo $selected_month1; ?>",
            onChange: function(selectedDates, dateStr, instance) {
                // อัพเดทค่า minDate ของ calendarSelect2 ด้วยวันที่เลือกจาก calendar1
                calendar2Instance.set('minDate', new Date(dateStr));
                document.getElementById("calendarSelect1").value = dateStr;
                updateFilters();
            }
        });

        // สร้างตัวแปรเก็บ instance ของ calendarSelect2
        let calendar2Instance = flatpickr("#calendarSelect2", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            defaultDate: "<?php echo $selected_month2; ?>",
            maxDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById("calendarSelect2").value = dateStr;
                updateFilters();
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            const params = new URLSearchParams(window.location.search);

            // ดึงค่าฟิลเตอร์จาก URL
            if (params.has("month")) document.getElementById("calendarSelect").value = params.get("month");
            if (params.has("gender")) document.getElementById("filter-gender-list").value = params.get("gender");
            if (params.has("type")) document.getElementById("filter-type-list").value = params.get("type");
            if (params.has("min_price")) document.getElementById("minPrice").value = params.get("min_price");
            if (params.has("max_price")) document.getElementById("maxPrice").value = params.get("max_price");
            if (params.has("province")) document.getElementById("filter-province-list").value = params.get("province");

            // โหลดข้อมูลใหม่ทันทีเมื่อเปิดหน้า
            updateFilters();

            // ตั้งค่า event listener ให้ฟิลเตอร์ทั้งหมด
            document.getElementById("calendarSelect1").addEventListener("change", updateFilters);
            document.getElementById("calendarSelect2").addEventListener("change", updateFilters);
            document.getElementById("filter-gender-list").addEventListener("change", updateFilters);
            document.getElementById("filter-type-list").addEventListener("change", updateFilters);
            document.getElementById("minPrice").addEventListener("input", updateFilters);
            document.getElementById("maxPrice").addEventListener("input", updateFilters);
            document.getElementById("filter-province-list").addEventListener("change", updateFilters);
        });
        document.addEventListener("DOMContentLoaded", function() {
            let today = new Date();
            let year = today.getFullYear();
            let month = (today.getMonth() + 1).toString().padStart(2, "0"); // บวก 1 เพราะ getMonth() เริ่มจาก 0
            document.getElementById("calendarSelect").value = `${year}-${month}`;
        });

        // ฟังก์ชันสำหรับอัปเดตฟิลเตอร์และโหลดข้อมูลใหม่
        function updateFilters() {
            const month1 = document.getElementById("calendarSelect1").value;
            const month2 = document.getElementById("calendarSelect2").value;
            const gender = document.getElementById("filter-gender-list").value;
            const minPrice = document.getElementById("minPrice").value;
            const maxPrice = document.getElementById("maxPrice").value;
            const province = document.getElementById("filter-province-list").value;

            // Get checked types
            const typeCheckboxes = document.querySelectorAll('#filter-type-list input[type="checkbox"]:checked');
            const selectedTypes = Array.from(typeCheckboxes).map(cb => cb.value);

            // Get checked regions
            const regionCheckboxes = document.querySelectorAll('#filter-region-list input[type="checkbox"]:checked');
            const selectedRegions = Array.from(regionCheckboxes).map(cb => cb.value);

            // Create URL parameters
            const params = new URLSearchParams();
            params.append("month1", month1);
            params.append("month2", month2);
            params.append("gender", gender);
            params.append("min_price", minPrice);
            params.append("max_price", maxPrice);
            params.append("province", province);

            // Add selected types
            selectedTypes.forEach(type => params.append("type[]", type));

            // Add selected regions
            selectedRegions.forEach(region => params.append("region[]", region));

            const newUrl = window.location.pathname + "?" + params.toString();
            window.history.replaceState({}, "", newUrl);

            // Load new data via AJAX
            fetch(newUrl + "&ajax=1")
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        mychart.data.labels = data.labels;
                        mychart.data.datasets[0].data = data.maleData;
                        mychart.data.datasets[1].data = data.femaleData;
                        mychart.update();
                    }
                })
                .catch(error => console.error('Error fetching updated data:', error));
        }

        function loadFiltersFromURL() {
            const params = new URLSearchParams(window.location.search);

            if (params.has("month1")) document.getElementById("calendarSelect1").value = params.get("month1");
            if (params.has("month2")) document.getElementById("calendarSelect2").value = params.get("month2");
            if (params.has("gender")) document.getElementById("filter-gender-list").value = params.get("gender");
            if (params.has("type")) {
                const types = params.get("type").split(",");
                const options = document.getElementById("filter-type-list").options;
                for (let option of options) {
                    if (types.includes(option.value)) {
                        option.selected = true;
                    }
                }
            }
            if (params.has("min_price")) document.getElementById("minPrice").value = params.get("min_price");
            if (params.has("max_price")) document.getElementById("maxPrice").value = params.get("max_price");
            if (params.has("province")) document.getElementById("filter-province-list").value = params.get("province");
            if (params.has("region")) {
                const regions = params.get("region").split(",");
                const checkboxes = document.querySelectorAll('#filter-region-list input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    if (regions.includes(checkbox.value)) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        // เพิ่มฟังก์ชันควบคุมการเลือกภูมิภาค
        function toggleRegionCheckboxes(provinceSelect) {
            const regionCheckboxes = document.querySelectorAll('#filter-region-list input[type="checkbox"]');
            const selectedProvince = provinceSelect.value;
            
            regionCheckboxes.forEach(checkbox => {
                if (selectedProvince === "ทั้งหมด") {
                    checkbox.disabled = false;
                    checkbox.parentElement.style.opacity = "1";
                } else {
                    checkbox.disabled = true;
                    checkbox.checked = true;
                    checkbox.parentElement.style.opacity = "0.5";
                }
            });
            
            // อัพเดทฟิลเตอร์เมื่อมีการเปลี่ยนแปลง
            updateFilters();
        }

        // เพิ่ม Event Listener สำหรับการเปลี่ยนแปลงจังหวัด
        document.addEventListener("DOMContentLoaded", () => {
            const provinceSelect = document.getElementById("filter-province-list");
            
            // ตั้งค่าเริ่มต้นเมื่อโหลดหน้า
            toggleRegionCheckboxes(provinceSelect);
            
            // เพิ่ม event listener สำหรับการเปลี่ยนแปลงจังหวัด
            provinceSelect.addEventListener("change", function() {
                toggleRegionCheckboxes(this);
            });
        });
    </script>

    </div>
</body>

</html>