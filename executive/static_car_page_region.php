<?php
require('username.php');

// เช็คว่าเป็น AJAX request หรือไม่
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// ดึงค่าจากฟอร์ม
$source = isset($_POST['source']) ? $_POST['source'] : ['emergency', 'ambulance', 'event'];
$level = isset($_POST['level']) ? $_POST['level'] : ['1', '2', '3'];
$province = isset($_POST['province']) ? $_POST['province'] : 'ทั้งหมด';
$gender = isset($_POST['gender']) ? $_POST['gender'] : 'ทั้งหมด';
$age_min = isset($_POST['age_min']) ? intval($_POST['age_min']) : 1;
$age_max = isset($_POST['age_max']) ? intval($_POST['age_max']) : 120;
$region = isset($_POST['region']) ? $_POST['region'] : ['ภาคเหนือ', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคกลาง', 'ภาคใต้'];
$date_start = isset($_POST['date_start']) ? $_POST['date_start'] : date('Y-m');
$date_end = isset($_POST['date_end']) ? $_POST['date_end'] : date('Y-m');

//sql
// สร้าง SQL Query
$sql = "
SELECT 
    m.member_firstname, 
    m.member_birthdate,
    m.member_gender,
    merged.member_id,
    merged.ambulance_id,
    merged.booking_date,
    merged.province,
    merged.region,
    merged.source,
    IF(merged.source = 'emergency', 
        merged.emergency_case_patient_gender,  -- ดึงเพศจาก emergency_case_report_patient_gender
        m.member_gender) AS gender,  -- ดึงเพศจาก member
    IF(merged.source = 'emergency', 
        merged.emergency_case_patient_age,  -- ดึงอายุจาก emergency_case_patient_age
        TIMESTAMPDIFF(YEAR, m.member_birthdate, CURDATE())) AS age,
    a.ambulance_level -- เพิ่มข้อมูลจากตาราง ambulance
FROM (
    SELECT ab.member_id, ab.ambulance_id, ab.ambulance_booking_date AS booking_date, 
           ab.ambulance_booking_province AS province, ab.ambulance_booking_region AS region, 
           'ambulance' AS source, NULL AS emergency_case_patient_age, NULL AS emergency_case_patient_gender
    FROM ambulance_booking AS ab
    UNION
    SELECT eb.member_id, eb.ambulance_id, eb.event_booking_date AS booking_date, 
           eb.event_booking_province AS province, eb.event_booking_region AS region, 
           'event' AS source, NULL AS emergency_case_patient_age, NULL AS emergency_case_patient_gender
    FROM event_booking AS eb
    UNION
    SELECT ecr.order_emergency_case_id, ecr.ambulance_id, ecr.order_emergency_case_date AS booking_date, 
           'กรุงเทพมหานคร' AS province, 'ภาคกลาง' AS region,
           'emergency' AS source, ecr.order_emergency_case_patient_age, ecr.order_emergency_case_patient_gender
    FROM order_emergency_case AS ecr
) AS merged
LEFT JOIN member AS m ON merged.member_id = m.member_id
LEFT JOIN ambulance AS a ON merged.ambulance_id = a.ambulance_id -- เชื่อมกับตาราง ambulance
WHERE 1=1
";

// กรองประเภทงาน
if (!empty($source)) {
    $source_list = implode("','", $source);
    $sql .= " AND merged.source IN ('$source_list')";
}

// กรองระดับรถ
if (!empty($level)) {
    $level_list = implode("','", $level);
    $sql .= " AND a.ambulance_level IN ('$level_list')";
}

// กรองจังหวัด
if ($province !== 'ทั้งหมด' && !empty($province)) {
    $sql .= " AND merged.province = '$province'";
}

// กรองเพศ
if ($gender !== 'ทั้งหมด' && !empty($gender)) {
    $sql .= " AND IF(merged.source = 'emergency', 
                merged.emergency_case_patient_gender, 
                m.member_gender) = '$gender'";
}

// กรองอายุ
$sql .= " AND IF(merged.source = 'emergency', 
            merged.emergency_case_patient_age,  
            TIMESTAMPDIFF(YEAR, m.member_birthdate, CURDATE())) 
          BETWEEN $age_min AND $age_max";

// กรองภูมิภาค
if (!empty($region)) {
    $region_list = implode("','", $region);
    $sql .= " AND merged.region IN ('$region_list')";
}

// กรองวันที่การจอง
// $sql .= " AND merged.booking_date BETWEEN '$date_start-01' AND '$date_end-31'";
$sql .= " AND DATE_FORMAT(merged.booking_date, '%Y-%m') BETWEEN '$date_start' AND '$date_end'";

$result = $conn->query($sql);

// เตรียมข้อมูลจากฐานข้อมูล
$chartData = [
    'ภาคเหนือ' => ['emergency' => 0, 'ambulance' => 0, 'event' => 0],
    'ภาคตะวันออกเฉียงเหนือ' => ['emergency' => 0, 'ambulance' => 0, 'event' => 0],
    'ภาคกลาง' => ['emergency' => 0, 'ambulance' => 0, 'event' => 0], 
    'ภาคใต้' => ['emergency' => 0, 'ambulance' => 0, 'event' => 0]
];

// ดึงข้อมูลจากฐานข้อมูล
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $region = $row['region'];
        $source = $row['source'];
        
        // เพิ่มการนับตามภูมิภาคและประเภทการใช้งาน
        if (isset($chartData[$region][$source])) {
            $chartData[$region][$source]++;
        }
    }
}

// สร้าง Labels และ Values สำหรับกราฟ
$chartLabels = array_keys($chartData); // Labels: ชื่อภูมิภาค

// แยกข้อมูลแต่ละประเภทออกมาให้เป็น dataset
$sourceTypes = ['emergency', 'ambulance', 'event'];
$chartValues = [];

foreach ($sourceTypes as $source) {
    $values = [];
    foreach ($chartLabels as $region) {
        $values[] = $chartData[$region][$source];
    }
    $chartValues[$source] = $values;
}

// ส่งออกข้อมูลเป็น JSON สำหรับ JavaScript
$chartDataJson = json_encode([
    'labels' => $chartLabels,
    'datasets' => [
        [
            'label' => 'รับเคสฉุกเฉิน',
            'data' => $chartValues['emergency'],
            'backgroundColor' => 'rgb(252, 147, 98)'
        ],
        [
            'label' => 'รับส่งผู้ป่วย',
            'data' => $chartValues['ambulance'],
            'backgroundColor' => 'rgb(129, 179, 210)'
        ],
        [
            'label' => 'รับงาน EVENT',
            'data' => $chartValues['event'],
            'backgroundColor' => 'rgba(75, 192, 192, 0.6)'
        ]
    ]
]);

// ถ้าเป็น AJAX request ให้ส่งข้อมูลกลับเป็น JSON
if ($isAjax) {
    header('Content-Type: application/json');
    echo $chartDataJson;
    exit;
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>สถิติการใช้งานรถ</title>
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
            <a href="summary_page.php" class="nav-item">สถิติคำสั่งซื้อ/เช่าสินค้า</a>
            <a href="case_report_page.php" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item active">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>
    </header>
    <h1 class="header-static-car-page">ดูสถิติการใช้งานรถ</h1>
    <br>

    <main class="main-content">
        <div class="search-section">
            <div class="filter-icon">
                <i class="fa-solid fa-filter"></i> <!-- ไอคอน Filter -->
            </div>

            <div class="filter-sidebar" id="filterSidebar">
                <div class="sidebar-header">
                    <h2>ตัวกรอง</h2>
                    <button class="close-sidebar">&times;</button>
                </div>
                <form id="filterForm" onsubmit="return false;">
                    <div class="sidebar-content">

                        <select class="filter-select" style="margin-left: 2%;" onchange="location = this.value;">
                            <option value="static_car_page.php">ดูสถิติการใช้งานรถตามประเภทงานและระดับรถ</option>
                            <option value="static_car_page_gender.php">ดูสถิติการใช้งานรถแยกตามเพศของสมาชิก</option>
                            <option value="static_car_page_waypoint.php">ดูสถิติการใช้งานรถแยกตามประเภทงานและโรงพยาบาล</option>
                            <option value="static_car_page_region.php" selected>ดูสถิติการใช้งานรถแยกตามประเภทงานและภูมิภาค</option>
                        </select>

                        <label for="">ปี/เดือน:</label>
                        <input type="text" id="start_month" class="month-selected" name="start_month"
                            placeholder="เลือกเดือน/ปี" value="<?= $_GET['start_month'] ?? '' ?>"> ถึง
                        <input type="text" id="end_month" class="month-selected" name="end_month"
                            placeholder="เลือกเดือน/ปี" value="<?= $_GET['end_month'] ?? '' ?>">

                        <label for="">เลือกประเภทงาน:</label>
                        <input type="checkbox" name="source[]" value="emergency" checked> รับเคสฉุกเฉิน
                        <br>
                        <input type="checkbox" name="source[]" value="ambulance" checked> รับส่งผู้ป่วย
                        <br>
                        <input type="checkbox" name="source[]" value="event" checked> รับงาน EVENT
                        <br>

                        <label for="">เลือกระดับรถ:</label>
                        <input type="checkbox" name="level[]" value="1" checked> ระดับ 1 (Basic Life Support)
                        <br>
                        <input type="checkbox" name="level[]" value="2" checked> ระดับ 2 (Advance Life Support)
                        <br>
                        <input type="checkbox" name="level[]" value="3" checked> ระดับ 3 (Mobile Intensive Care Unit)
                        <br>

                        <!-- <label for="filter-price" hidden>จังหวัด:</label> -->
                        <select id="filter-price-list" name="province" class="filter-select" hidden>
                            <option value="" selected hidden>กรุณาเลือกจังหวัด</option>
                            <option value="ทั้งหมด" selected>ทั้งหมด</option>
                            <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                            <option value="กระบี่">กระบี่</option>
                            <option value="กาญจนบุรี">กาญจนบุรี</option>
                            <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                            <option value="กำแพงเพชร">กำแพงเพชร</option>
                            <option value="ขอนแก่น">ขอนแก่น</option>
                            <option value="จันทบุรี">จันทบุรี</option>
                            <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                            <option value="ชลบุรี">ชลบุรี</option>
                            <option value="ชัยนาท">ชัยนาท</option>
                            <option value="ชัยภูมิ">ชัยภูมิ</option>
                            <option value="ชุมพร">ชุมพร</option>
                            <option value="เชียงราย">เชียงราย</option>
                            <option value="เชียงใหม่">เชียงใหม่</option>
                            <option value="ตรัง">ตรัง</option>
                            <option value="ตราด">ตราด</option>
                            <option value="ตาก">ตาก</option>
                            <option value="นครนายก">นครนายก</option>
                            <option value="นครปฐม">นครปฐม</option>
                            <option value="นครพนม">นครพนม</option>
                            <option value="นครราชสีมา">นครราชสีมา</option>
                            <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                            <option value="นครสวรรค์">นครสวรรค์</option>
                            <option value="นนทบุรี">นนทบุรี</option>
                            <option value="นราธิวาส">นราธิวาส</option>
                            <option value="น่าน">น่าน</option>
                            <option value="บึงกาฬ">บึงกาฬ</option>
                            <option value="บุรีรัมย์">บุรีรัมย์</option>
                            <option value="ปทุมธานี">ปทุมธานี</option>
                            <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                            <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                            <option value="ปัตตานี">ปัตตานี</option>
                            <option value="พะเยา">พะเยา</option>
                            <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                            <option value="พังงา">พังงา</option>
                            <option value="พัทลุง">พัทลุง</option>
                            <option value="พิจิตร">พิจิตร</option>
                            <option value="พิษณุโลก">พิษณุโลก</option>
                            <option value="เพชรบุรี">เพชรบุรี</option>
                            <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                            <option value="แพร่">แพร่</option>
                            <option value="ภูเก็ต">ภูเก็ต</option>
                            <option value="มหาสารคาม">มหาสารคาม</option>
                            <option value="มุกดาหาร">มุกดาหาร</option>
                            <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                            <option value="ยโสธร">ยโสธร</option>
                            <option value="ยะลา">ยะลา</option>
                            <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                            <option value="ระนอง">ระนอง</option>
                            <option value="ระยอง">ระยอง</option>
                            <option value="ราชบุรี">ราชบุรี</option>
                            <option value="ลพบุรี">ลพบุรี</option>
                            <option value="ลำปาง">ลำปาง</option>
                            <option value="ลำพูน">ลำพูน</option>
                            <option value="เลย">เลย</option>
                            <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                            <option value="สกลนคร">สกลนคร</option>
                            <option value="สงขลา">สงขลา</option>
                            <option value="สตูล">สตูล</option>
                            <option value="สมุทรปราการ">สมุทรปราการ</option>
                            <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                            <option value="สมุทรสาคร">สมุทรสาคร</option>
                            <option value="สระแก้ว">สระแก้ว</option>
                            <option value="สระบุรี">สระบุรี</option>
                            <option value="สิงห์บุรี">สิงห์บุรี</option>
                            <option value="สุโขทัย">สุโขทัย</option>
                            <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                            <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                            <option value="สุรินทร์">สุรินทร์</option>
                            <option value="หนองคาย">หนองคาย</option>
                            <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                            <option value="อ่างทอง">อ่างทอง</option>
                            <option value="อุดรธานี">อุดรธานี</option>
                            <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                            <option value="อุทัยธานี">อุทัยธานี</option>
                            <option value="อุบลราชธานี">อุบลราชธานี</option>
                            <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                        </select>

                        <label for="gender">เพศ:</label>
                        <select name="gender" class="filter-select">
                            <!-- <option value="" selected hidden>กรุณาเลือกเพศ</option> -->
                            <option value="ทั้งหมด" selected>ทั้งหมด</option>
                            <option value="ชาย">ชาย</option>
                            <option value="หญิง">หญิง</option>
                        </select>
                        <br>

                        <label for="">อายุ:</label>
                        <input class="input-age" name="age_min" value="1" min="1" max="120" type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');"> ถึง
                        <input class="input-age" name="age_max" value="120" min="1" max="120" type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');"> ปี
                        <br>

                        <label for="">เลือกภูมิภาค:</label>
                        <input type="checkbox" name="region[]" value="ภาคเหนือ" checked> ภาคเหนือ
                        <br>
                        <input type="checkbox" name="region[]" value="ภาคตะวันออกเฉียงเหนือ" checked> ภาคตะวันออกเฉียงเหนือ
                        <br>
                        <input type="checkbox" name="region[]" value="ภาคกลาง" checked> ภาคกลาง
                        <br>
                        <input type="checkbox" name="region[]" value="ภาคใต้" checked> ภาคใต้
                        <br>




                        <a href="static_car_page.php" class="reset-button" id="reset-button">Reset</a>
                    </div>
                </form>

    </main>

    <!-- เพิ่ม div สำหรับแสดงผลกราฟ -->
    <div id="chartContainer" style="width: 100%; height: 400px;">
        <canvas id="bookingChart"></canvas>
    </div>


</body>

<script>
    const monthSelectConfig = {
        dateFormat: "Y-m",
        altInput: true,
        altFormat: "F Y",
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y"
            })
        ],
        disableMobile: true
    };
    // กำหนดค่า default dates
    const today = new Date();

    // Initialize start_month flatpickr
    const startMonthPicker = flatpickr("#start_month", {
        ...monthSelectConfig,
        defaultDate: "<?= $_GET['start_month'] ?? date('Y-m') ?>",
        maxDate: today,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates[0]) {
                // Reset end_month configuration
                endMonthPicker.destroy();

                // Reinitialize end_month with updated config
                const endConfig = {
                    ...monthSelectConfig,
                    defaultDate: endMonthPicker.selectedDates[0] || selectedDates[0],
                    minDate: selectedDates[0],
                    maxDate: today,
                    onChange: function(selectedDates, dateStr) {
                        if (selectedDates[0]) {
                            const startDate = startMonthPicker.selectedDates[0];
                            if (startDate && selectedDates[0] < startDate) {
                                this.setDate(startDate);
                            }
                        }
                    }
                };

                endMonthPicker = flatpickr("#end_month", endConfig);
            }
        }
    });

    function handleRegionFilter() {
        const provinceSelect = document.querySelector('select[name="province"]');
        const regionCheckboxes = document.querySelectorAll('input[name="region[]"]');

        // ฟังก์ชันสำหรับ toggle การใช้งาน checkboxes
        function toggleRegionCheckboxes(disabled) {
            regionCheckboxes.forEach(checkbox => {
                checkbox.disabled = disabled;
                if (disabled) {
                    checkbox.checked = false;
                }
                // เพิ่มหรือลบ class สำหรับสไตล์ของ checkbox ที่ถูก disable
                checkbox.parentElement.classList.toggle('disabled-filter', disabled);
            });
        }

        // Event listener สำหรับการเปลี่ยนแปลงจังหวัด
        provinceSelect.addEventListener('change', function() {
            if (this.value === 'ทั้งหมด') {
                toggleRegionCheckboxes(false);
                // เมื่อเลือก "ทั้งหมด" ให้เช็คทุก checkbox กลับคืน
                regionCheckboxes.forEach(checkbox => checkbox.checked = true);
            } else {
                toggleRegionCheckboxes(true);
            }
            // เรียกฟังก์ชัน updateChart() เพื่ออัพเดทกราฟ
            updateChart();
        });

        // เช็คค่าเริ่มต้นเมื่อโหลดหน้า
        if (provinceSelect.value !== 'ทั้งหมด') {
            toggleRegionCheckboxes(true);
        }
    }

    // Initialize end_month flatpickr
    let endMonthPicker = flatpickr("#end_month", {
        ...monthSelectConfig,
        defaultDate: "<?= $_GET['end_month'] ?? date('Y-m') ?>",
        minDate: startMonthPicker.selectedDates[0] || "<?= $_GET['start_month'] ?? date('Y-m') ?>",
        maxDate: today
    });

    //กราฟ
    document.addEventListener('DOMContentLoaded', function() {
        handleRegionFilter();
        // ฟังก์ชันสำหรับอัพเดทกราฟ
        function updateChart() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);

            // เพิ่มการตรวจสอบค่าอายุ
            const ageMin = parseInt(document.querySelector('input[name="age_min"]').value);
            const ageMax = parseInt(document.querySelector('input[name="age_max"]').value);

            if (ageMin > ageMax) {
                alert('อายุต่ำสุดต้องน้อยกว่าอายุสูงสุด');
                return;
            }

            // เพิ่มค่าวันที่จาก flatpickr
            formData.set('date_start', document.getElementById('start_month').value);
            formData.set('date_end', document.getElementById('end_month').value);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (window.myChart instanceof Chart) {
                        window.myChart.destroy();
                    }

                    const ctx = document.getElementById('bookingChart').getContext('2d');
                    window.myChart = new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: false,
                                    title: {
                                        display: true,
                                        text: 'ภูมิภาค'
                                    }
                                },
                                y: {
                                    stacked: false,
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'จำนวนครั้งที่ใช้บริการ (ครั้ง)'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'สถิติการใช้งานรถแยกตามประเภทงานและภูมิภาค'
                                }
                            },
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        // Event listeners for form elements
        const form = document.getElementById('filterForm');
        const filterInputs = form.querySelectorAll('input:not([type="number"]), select');
        const ageInputs = form.querySelectorAll('input[type="number"]');

        // Event listener สำหรับ input ทั่วไปและ select
        filterInputs.forEach(input => {
            input.addEventListener('change', updateChart);
        });

        // Event listener สำหรับ input อายุ
        ageInputs.forEach(input => {
            let debounceTimer;
            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    // ตรวจสอบค่าที่รับเข้ามา
                    let value = parseInt(this.value);
                    if (isNaN(value) || value < 1) {
                        this.value = 1;
                    } else if (value > 120) {
                        this.value = 120;
                    }
                    updateChart();
                }, 300); // รอ 300ms หลังจากผู้ใช้หยุดพิมพ์
            });
        });

        // Event listener สำหรับ form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateChart();
        });

        // Event listener สำหรับ reset button
        document.getElementById('reset-button').addEventListener('click', function(e) {
            e.preventDefault();
            form.reset();
            updateChart();
        });

        // Initial chart load
        updateChart();
    });

    // Sidebar toggle code
    document.addEventListener("DOMContentLoaded", () => {
        const filterIcon = document.querySelector(".filter-icon");
        const sidebar = document.getElementById("filterSidebar");
        const closeSidebar = document.querySelector(".close-sidebar");

        filterIcon.addEventListener("click", () => {
            sidebar.classList.add("open");
        });

        closeSidebar.addEventListener("click", () => {
            sidebar.classList.remove("open");
        });

        document.addEventListener("click", (e) => {
            if (!sidebar.contains(e.target) && !filterIcon.contains(e.target)) {
                sidebar.classList.remove("open");
            }
        });
    });

    // เพิ่ม Event Listener สำหรับ flatpickr
    document.getElementById('start_month').addEventListener('change', updateChart);
    document.getElementById('end_month').addEventListener('change', updateChart);
</script>

</html>