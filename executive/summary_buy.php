<?php
include 'username.php';

$start_month = mysqli_real_escape_string($conn, $_GET['selected_month'] ?? '') . "-01";
$end_raw = mysqli_real_escape_string($conn, $_GET['selected_month2'] ?? '') . "-01";
$end_month = date("Y-m-t", strtotime($end_raw));

$booking_where_sql_ambulance = "1=1";
$booking_where_sql_event = "1=1";
$booking_where_sql_emergency = "1=1";

if (!empty($_GET['selected_month']) && !empty($_GET['selected_month2'])) {
    $booking_where_sql_ambulance .= " AND ab.ambulance_booking_date BETWEEN '$start_month' AND '$end_month'";
    $booking_where_sql_event .= " AND eb.event_booking_date BETWEEN '$start_month' AND '$end_month'";
    $booking_where_sql_emergency .= " AND ecr.order_emergency_case_date BETWEEN '$start_month' AND '$end_month'";
}

if (!empty($_GET['province'])) {
    $province = mysqli_real_escape_string($conn, $_GET['province']);
    $booking_where_sql_ambulance .= " AND ab.ambulance_booking_province = '$province'";
    $booking_where_sql_event .= " AND eb.event_booking_province = '$province'";
    $booking_where_sql_emergency .= " AND 'กรุงเทพมหานคร' = '$province'";
}
if (!isset($_GET['region'])) {
    $_GET['region'] = ['ภาคเหนือ', 'ภาคกลาง', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคใต้'];
}

if (!empty($_GET['region']) && is_array($_GET['region'])) {
    $regions = array_map(fn($r) => "'" . mysqli_real_escape_string($conn, $r) . "'", $_GET['region']);
    $in_region = implode(",", $regions);
    $booking_where_sql_ambulance .= " AND ab.ambulance_booking_region IN ($in_region)";
    $booking_where_sql_event .= " AND eb.event_booking_region IN ($in_region)";
    $booking_where_sql_emergency .= " AND 'ภาคกลาง' IN ($in_region)";
}

if (!empty($_GET['ambulance_level']) && is_array($_GET['ambulance_level'])) {
    $levels = array_map(fn($lvl) => "'" . mysqli_real_escape_string($conn, $lvl) . "'", $_GET['ambulance_level']);
    $booking_level_filter = "a.ambulance_level IN (" . implode(",", $levels) . ")";
} else {
    $booking_level_filter = "1=1";
}

$gender_filter_booking = '';
$gender_filter_emergency = '';
if (!empty($_GET['gender'])) {
    $gender = mysqli_real_escape_string($conn, $_GET['gender']);
    $gender_filter_booking = " AND m.member_gender = '$gender'";
    $gender_filter_emergency = " AND ecr.order_emergency_case_patient_gender = '$gender'";
}

$where_clauses = ["order_equipment_date BETWEEN '$start_month' AND '$end_month'"];
if (!empty($_GET['gender'])) $where_clauses[] = "member_gender = '$gender'";
if (!empty($_GET['order_type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['order_type']);
    $where_clauses[] = "order_equipment_type = '$type'";
} else {
    $where_clauses[] = "order_equipment_type IN ('ซื้อ', 'เช่า')";
}
if (!empty($_GET['province'])) $where_clauses[] = "member_province = '$province'";
if (!empty($_GET['region']) && is_array($_GET['region'])) {
    $regions = array_map(fn($r) => "'" . mysqli_real_escape_string($conn, $r) . "'", $_GET['region']);
    $where_clauses[] = "member_region IN (" . implode(",", $regions) . ")";
} else {
    $where_clauses[] = "1=0";
}
$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$sql = "
SELECT 
    merged.source AS source_type, 
    merged.gender,
    SUM(merged.reservation_price) AS total_sales
FROM (
    SELECT ab.ambulance_booking_price AS reservation_price, 'ambulance' AS source, m.member_gender AS gender
    FROM ambulance_booking AS ab
    JOIN member m ON ab.member_id = m.member_id
    JOIN ambulance a ON ab.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_ambulance $gender_filter_booking AND $booking_level_filter

    UNION ALL

    SELECT eb.event_booking_price AS reservation_price, 'event' AS source, m.member_gender AS gender
    FROM event_booking AS eb
    JOIN member m ON eb.member_id = m.member_id
    JOIN ambulance a ON eb.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_event $gender_filter_booking AND $booking_level_filter

    UNION ALL

    SELECT ecr.order_emergency_case_price AS reservation_price, 'emergency' AS source, ecr.order_emergency_case_patient_gender AS gender
    FROM order_emergency_case AS ecr
    JOIN ambulance a ON ecr.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_emergency $gender_filter_emergency AND $booking_level_filter
) AS merged
GROUP BY merged.source, merged.gender
";

$sqrt = "
SELECT 
    member_gender AS gender,
    SUM(CASE WHEN order_equipment_type = 'ซื้อ' THEN order_equipment_total ELSE 0 END) AS total_purchase,
    SUM(CASE WHEN order_equipment_type = 'เช่า' THEN order_equipment_total ELSE 0 END) AS total_rent
FROM order_equipment
JOIN equipment ON order_equipment.equipment_id = equipment.equipment_id
JOIN member ON order_equipment.member_id = member.member_id
$where_sql
GROUP BY member_gender
";

$sql_region_all = "
SELECT 
    region_data.region,
    SUM(region_data.total) AS total_sales
FROM (
    SELECT ab.ambulance_booking_region AS region, ab.ambulance_booking_price AS total
    FROM ambulance_booking ab
    LEFT JOIN member m ON ab.member_id = m.member_id
    LEFT JOIN ambulance a ON ab.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_ambulance $gender_filter_booking AND $booking_level_filter

    UNION ALL

    SELECT eb.event_booking_region AS region, eb.event_booking_price AS total
    FROM event_booking eb
    LEFT JOIN member m ON eb.member_id = m.member_id
    LEFT JOIN ambulance a ON eb.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_event $gender_filter_booking AND $booking_level_filter

    UNION ALL

    SELECT 'ภาคกลาง' AS region, ecr.order_emergency_case_price AS total
    FROM order_emergency_case ecr
    LEFT JOIN ambulance a ON ecr.ambulance_id = a.ambulance_id
    WHERE $booking_where_sql_emergency $gender_filter_emergency AND $booking_level_filter
) AS region_data
GROUP BY region_data.region
";

$sql_equipment_region = "
SELECT member_region AS region, SUM(order_equipment_total) AS total_sales
FROM order_equipment
JOIN member ON order_equipment.member_id = member.member_id
$where_sql
GROUP BY member_region
";

$region_totals = [
    'ภาคเหนือ' => 0,
    'ภาคกลาง' => 0,
    'ภาคตะวันออกเฉียงเหนือ' => 0,
    'ภาคใต้' => 0
];

$result_region_booking = mysqli_query($conn, $sql_region_all);
while ($row = mysqli_fetch_assoc($result_region_booking)) {
    $region = $row['region'];
    $amount = $row['total_sales'];
    if (isset($region_totals[$region])) {
        $region_totals[$region] += $amount;
    }
}

$result_region_equipment = mysqli_query($conn, $sql_equipment_region);
while ($row = mysqli_fetch_assoc($result_region_equipment)) {
    $region = $row['region'];
    $amount = $row['total_sales'];
    if (isset($region_totals[$region])) {
        $region_totals[$region] += $amount;
    }
}

$result_booking = mysqli_query($conn, $sql);
$result_equipment = mysqli_query($conn, $sqrt);

$data = [
    'ambulance' => ['ชาย' => 0, 'หญิง' => 0],
    'event' => ['ชาย' => 0, 'หญิง' => 0],
    'emergency' => ['ชาย' => 0, 'หญิง' => 0],
    'equipment' => ['ชาย' => 0, 'หญิง' => 0],
];

while ($row = mysqli_fetch_assoc($result_booking)) {
    $type = $row['source_type'];
    $gender = $row['gender'] ?? 'ไม่ระบุ';
    $amount = $row['total_sales'] ?? 0;
    if (isset($data[$type][$gender])) {
        $data[$type][$gender] += $amount;
    }
}

while ($row = mysqli_fetch_assoc($result_equipment)) {
    $gender = $row['gender'] ?? 'ไม่ระบุ';
    $data['equipment'][$gender] += ($row['total_purchase'] ?? 0) + ($row['total_rent'] ?? 0);
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'category_gender' => $data,
        'region_total_sales' => $region_totals
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="summary_buy.css?v=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="summary_buy.js"></script>
    <title>สรุปยอดขาย</title>

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
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item active">สรุปยอดขาย</a>
        </nav>
    </header>
    <h1 class="header-summary-buy-page">สรุปยอดขาย</h1>
    <br>

    <main class="main-content">
        <div class="search-section">
            <div class="filter-icon">
                <i class="fa-solid fa-filter"></i> <!-- ไอคอน Filter -->
            </div>
        </div>
        <div class="filter-sidebar" id="filterSidebar">
            <div class="sidebar-header">
                <h2>ตัวกรอง</h2>
                <button type="button" class="close-sidebar">&times;</button>
            </div>

            <form method="GET" action="summary_buy.php" id="filterForm">


                <label for="start_month">เริ่มต้น (ปี/เดือน):</label>
                <input type="month" id="start_month" class="month-selected" name="selected_month" value="<?= $_GET['selected_month'] ?? '' ?>">
                <br>
                <label for="end_month">สิ้นสุด (ปี/เดือน):</label>
                <input type="month" id="end_month" class="month-selected" name="selected_month2" value="<?= $_GET['selected_month2'] ?? '' ?>">
                <br>
                <label>เพศ:</label>
                <select name="gender" class="filter-select">
                    <option value="">ทั้งหมด</option>
                    <option value="ชาย" <?= ($_GET['gender'] ?? '') == 'ชาย' ? 'selected' : '' ?>>ชาย</option>
                    <option value="หญิง" <?= ($_GET['gender'] ?? '') == 'หญิง' ? 'selected' : '' ?>>หญิง</option>
                </select>

                <label>ประเภทคำสั่งซื้อ:</label>
                <select name="order_type" class="filter-select">
                    <option value="">ทั้งหมด</option>
                    <option value="ซื้อ" <?= ($_GET['order_type'] ?? '') == 'ซื้อ' ? 'selected' : '' ?>>ซื้อ</option>
                    <option value="เช่า" <?= ($_GET['order_type'] ?? '') == 'เช่า' ? 'selected' : '' ?>>เช่า</option>
                </select>

                <label>เลือกระดับรถ:</label><br>
                <?php
                $selected_levels = $_GET['ambulance_level'] ?? [];
                foreach ([1, 2, 3] as $level) {
                    $checked = in_array($level, $selected_levels) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='ambulance_level[]' value='$level' $checked checked> ระดับ $level</label><br>";
                }
                ?>

                <label>จังหวัด:</label>
                <select id="province_selected" name="province" class="filter-select">
                    <option value="">ทั้งหมด</option>
                    <?php
                    $provinces = [
                        'กรุงเทพมหานคร',
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
                        'เชียงใหม่',
                        'เชียงราย',
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
                        'สตูล',
                        'หนองคาย',
                        'หนองบัวลำภู',
                        'อ่างทอง',
                        'อำนาจเจริญ',
                        'อุดรธานี',
                        'อุตรดิตถ์',
                        'อุทัยธานี',
                        'อุบลราชธานี'
                    ];
                    foreach ($provinces as $p) {
                        $selected = ($_GET['province'] ?? '') == $p ? 'selected' : '';
                        echo "<option value='$p' $selected>$p</option>";
                    }
                    ?>
                </select>

                <label>เลือกภูมิภาค:</label><br>
                <?php
                $selected_regions = $_GET['region'] ?? [];
                $regions = ['ภาคเหนือ', 'ภาคกลาง', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคใต้'];
                foreach ($regions as $r) {
                    $checked = in_array($r, $selected_regions) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='region[]' value='$r' $checked checked> $r</label><br>";
                }
                ?>
            </form>
        </div>
        </div>
        <div class="chart" id="chart">
            <canvas class="bar_chart" id="salesChart"></canvas>
            <canvas class="donut_chart" id="regionChart"></canvas>
        </div>



    </main>
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
            ]
        };

        // กำหนดค่า default dates
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        flatpickr("#start_month", {
            ...monthSelectConfig,
            defaultDate: "<?= $_GET['start_month'] ?? date('Y-m') ?>",
            maxDate: new Date()
        });

        flatpickr("#end_month", {
            ...monthSelectConfig,
            defaultDate: "<?= $_GET['end_month'] ?? date('Y-m') ?>",
            maxDate: new Date()
        });
        //chart
        document.addEventListener("DOMContentLoaded", function() {
            const filterForm = document.getElementById("filterForm");
            const chartCanvas = document.getElementById("salesChart");
            const regionCanvas = document.getElementById("regionChart");
            const ctx = chartCanvas.getContext("2d");

            // สร้างกราฟเริ่มต้น
            let salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['รับส่งผู้ป่วย', 'รับงาน Event', 'รับเคสฉุกเฉิน', 'อุปกรณ์ทางการแพทย์'],
                    datasets: [{
                            label: 'ชาย',
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            data: [0, 0, 0, 0]
                        },
                        {
                            label: 'หญิง',
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            data: [0, 0, 0, 0]
                        },
                        {
                            label: 'รวมยอดขายทั้งชายและหญิง',
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            data: [0, 0, 0, 0]
                        }
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'ยอดขายตามเพศของผู้บริการ (บาท)'
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
            let regionChart = new Chart(regionCanvas, {
                type: 'bar',
                data: {
                    labels: ['ภาคเหนือ', 'ภาคกลาง', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคใต้'],
                    datasets: [{
                        label: 'ยอดขายรวมตามภูมิภาค',
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'ยอดขายตามภูมิภาคของผู้บริการ (บาท)'
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });


            // ฟังก์ชันส่งฟอร์มด้วย AJAX
            function updateChart() {

                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();

                fetch(`summary_buy.php?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log(data);

                        const purchase = Number(data.purchase_sales); // ✅ แปลงเป็นตัวเลข
                        const rent = Number(data.rent_sales); // ✅ แปลงเป็นตัวเลข

                        const categories = ['ambulance', 'event', 'emergency', 'equipment'];
                        const maleData = categories.map(cat => Number(data.category_gender[cat]?.ชาย ?? 0));
                        const femaleData = categories.map(cat => Number(data.category_gender[cat]?.หญิง ?? 0));
                        const totalData = categories.map((_, i) => maleData[i] + femaleData[i]);

                        salesChart.data.datasets[0].data = maleData;
                        salesChart.data.datasets[1].data = femaleData;
                        salesChart.data.datasets[2].data = totalData;

                        salesChart.update();

                        const regionLabels = ['ภาคเหนือ', 'ภาคกลาง', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคใต้'];
                        const regionSales = regionLabels.map(region => Number(data.region_total_sales[region] ?? 0));

                        // ✅ เอาแค่ชื่อภาค ไม่แสดงเปอร์เซ็นต์ใน label
                        regionChart.data.labels = regionLabels;
                        regionChart.data.datasets[0].data = regionSales;
                        regionChart.update();


                    })

                    .catch(err => console.error("Error fetching chart data:", err));
            }

            // เรียก updateChart เมื่อเปลี่ยนค่า filter ใด ๆ
            const inputs = filterForm.querySelectorAll("input, select");
            inputs.forEach(input => {
                input.addEventListener("change", updateChart);
            });

            // เรียกครั้งแรกเมื่อโหลดหน้า
            updateChart();
        });
    </script>
</body>

</html>