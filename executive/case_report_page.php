<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล

include 'username.php';

// รับค่าฟิลเตอร์จาก GET parameters
$min_age = isset($_GET['min_age']) ? (int)$_GET['min_age'] : 0;
$max_age = isset($_GET['max_age']) ? (int)$_GET['max_age'] : 100;
date_default_timezone_set('Asia/Bangkok');

$selected_date1 = date('Y-m-d');
if (isset($_GET['date1']) && !empty($_GET['date1'])) {
    $selected_date1 = $_GET['date1'];
}
$selected_date2 = date('Y-m-d');
if (isset($_GET['date2']) && !empty($_GET['date2'])) {
    $selected_date2 = $_GET['date2'];
}

$selected_gender = isset($_GET['gender']) ? $_GET['gender'] : "ทั้งหมด";
$selected_symptom = isset($_GET['symptom']) ? $_GET['symptom'] : "ทั้งหมด";
$selected_hospital = isset($_GET['hospital']) ? $_GET['hospital'] : "ทั้งหมด";
$selected_zone = isset($_GET['zone']) ? $_GET['zone'] : "ทั้งหมด";

// สร้าง WHERE Clause ตามฟิลเตอร์ที่เลือก
$where_clause = "WHERE order_emergency_case_patient_age BETWEEN $min_age AND $max_age";
if ($selected_date1 && $selected_date2) {
    $where_clause .= " AND DATE(order_emergency_case_date) BETWEEN '$selected_date1' AND '$selected_date2'";
}
if ($selected_gender !== "ทั้งหมด") {
    $where_clause .= " AND order_emergency_case_patient_gender = '$selected_gender'";
}
if ($selected_symptom !== "ทั้งหมด") {
    if ($selected_symptom === "อื่นๆ") {
        $where_clause .= " AND order_emergency_case_reason NOT LIKE '%อุบัติเหตุ%' AND order_emergency_case_reason NOT LIKE '%อาการป่วย%'";
    } else {
        $where_clause .= " AND order_emergency_case_reason LIKE '%$selected_symptom%'";
    }
}
if ($selected_hospital !== "ทั้งหมด") {
    $where_clause .= " AND order_emergency_case_hospital_waypoint = '$selected_hospital'";
}
if ($selected_zone !== "ทั้งหมด") {
    $where_clause .= " AND order_emergency_case_zone = '$selected_zone'";
}

// Query ดึงข้อมูล
$sql = "SELECT 
    order_emergency_case_reason,
    SUM(CASE WHEN order_emergency_case_patient_gender = 'ชาย' THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN order_emergency_case_patient_gender = 'หญิง' THEN 1 ELSE 0 END) as female_count
    FROM order_emergency_case 
    $where_clause
    GROUP BY order_emergency_case_reason";

$result = $conn->query($sql);

// เตรียมข้อมูลสำหรับกราฟ
$labels = [];
$maleData = [];
$femaleData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['order_emergency_case_reason'];
        $maleData[] = $row['male_count'];
        $femaleData[] = $row['female_count'];
    }
}

// Query ดึงข้อมูลโรงพยาบาล
$hospital_query = "SELECT DISTINCT order_emergency_case_hospital_waypoint FROM order_emergency_case";
$hospital_result = $conn->query($hospital_query);

$hospital_options = [];
if ($hospital_result->num_rows > 0) {
    while ($row = $hospital_result->fetch_assoc()) {
        $hospital_options[] = $row['order_emergency_case_hospital_waypoint'];
    }
}

// Query ดึงข้อมูลเขตพื้นที่เกิดเหตุ
$zone_query = "SELECT DISTINCT order_emergency_case_zone FROM order_emergency_case";
$zone_result = $conn->query($zone_query);

$zone_options = [];
if ($zone_result->num_rows > 0) {
    while ($row = $zone_result->fetch_assoc()) {
        $zone_options[] = $row['order_emergency_case_zone'];
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

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
    <title>ดูรายงานเคส</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="summary_page.php" class="nav-item">สถิติคำสั่งซื้อ/เช่าสินค้า</a>
            <a href="case_report_page.php" class="nav-item active">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>
    </header>

    <div id="chart-labels" style="display: none;"><?php echo json_encode($labels); ?></div>
    <div id="chart-maleData" style="display: none;"><?php echo json_encode($maleData); ?></div>
    <div id="chart-femaleData" style="display: none;"><?php echo json_encode($femaleData); ?></div>

    <h1 style="text-align: center;">ดูสรุปรายงานเคสฉุกเฉิน</h1>
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
                <div class="sidebar-content">
                    <label for="calendarSelect">เลือกวันที่:</label>
                    <input class="calendar-selected" id="calendarSelect1" type="text" placeholder="เลือกวันที่" value="2025-01-22">
                    <input class="calendar-selected" id="calendarSelect2" type="text" placeholder="เลือกวันที่" value="2025-01-22">

                    <label for="filter-gender">เพศ:</label>
                    <select id="filter-gender-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_gender == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="ชาย" <?php if ($selected_gender == "ชาย") echo "selected"; ?>>ชาย</option>
                        <option value="หญิง" <?php if ($selected_gender == "หญิง") echo "selected"; ?>>หญิง</option>
                    </select>

                    <!-- แก้เป็น radio -->
                    <label>ช่วงอายุ:</label>
                    <input type="number" id="minAge" class="age-input" value="<?php echo $min_age; ?>" min="0" max="100">
                    <span>ถึง</span>
                    <input type="number" id="maxAge" class="age-input" value="<?php echo $max_age; ?>" min="0" max="100">
                    <span>ปี</span>
                    <br><br>

                    <label for="filter-symtom">สาเหตุ/อาการป่วย:</label>
                    <select id="filter-symtom-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_symptom == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="อุบัติเหตุ" <?php if ($selected_symptom == "อุบัติเหตุ") echo "selected"; ?>>อุบัติเหตุ</option>
                        <option value="อาการป่วย" <?php if ($selected_symptom == "อาการป่วย") echo "selected"; ?>>อาการป่วย</option>
                        <option value="อื่นๆ" <?php if ($selected_symptom == "อื่นๆ") echo "selected"; ?>>อื่นๆ</option>
                    </select>

                    <label for="filter-hospital">โรงพยาบาล:</label>
                    <select id="filter-hospital-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_hospital == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <?php foreach ($hospital_options as $hospital) : ?>
                            <option value="<?php echo $hospital; ?>" <?php if ($selected_hospital == $hospital) echo "selected"; ?>>
                                <?php echo $hospital; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="filter-zone">เขตพื้นที่เกิดเหตุ:</label>
                    <select id="filter-zone-list" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_zone == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <?php foreach ($zone_options as $zone) : ?>
                            <option value="<?php echo $zone; ?>" <?php if ($selected_zone == $zone) echo "selected"; ?>>
                                <?php echo $zone; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <a href="case_report_page.php" class="reset-button" id="reset-button">reset</a>
                </div>
            </div>
        </div>
    </main>

    <canvas id="case"></canvas>

    <script>
        // รับข้อมูลจาก PHP เพื่อใช้ในกราฟ
        const labels = <?php echo json_encode($labels); ?>;
        const maleData = <?php echo json_encode($maleData); ?>;
        const femaleData = <?php echo json_encode($femaleData); ?>;

        Chart.defaults.elements.bar.borderRadius = 5;

        // สร้างกราฟด้วย Chart.js
        const mychart = new Chart(document.getElementById("case"), {
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
                            text: 'สาเหตุการแจ้งเหตุ'
                        }
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'จำนวนผู้ป่วย (คน)'
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'สถิติผู้ป่วยฉุกเฉินแยกตามสาเหตุ',
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

                        // ปิด Sidebar เมื่อคลิกนอก Sidebar ยกเว้น flatpickr calendar
                        document.addEventListener("click", (e) => {
                            if (
                                !sidebar.contains(e.target) &&
                                !filterIcon.contains(e.target) &&
                                !e.target.closest(".flatpickr-calendar")
                            ) {
                                sidebar.classList.remove("open");
                            }
                        });
                    });

                    // ตั้งค่า Flatpickr สำหรับเลือกวันที่
                    flatpickr("#calendarSelect1", {
                        dateFormat: "Y-m-d",
                        defaultDate: "<?php echo $selected_date1; ?>",
                        onChange: updateFilters
                    });
                    flatpickr("#calendarSelect2", {
                        dateFormat: "Y-m-d",
                        defaultDate: "<?php echo $selected_date2; ?>",
                        onChange: updateFilters
                    });

                    // ฟังก์ชันสำหรับอัปเดตฟิลเตอร์และโหลดข้อมูลใหม่
                    function updateFilters() {
                        const date1 = document.getElementById("calendarSelect1").value;
                        const date2 = document.getElementById("calendarSelect2").value;
                        const gender = document.getElementById("filter-gender-list").value;
                        const minAge = document.getElementById("minAge").value;
                        const maxAge = document.getElementById("maxAge").value;
                        const symptom = document.getElementById("filter-symtom-list").value;
                        const hospital = document.getElementById("filter-hospital-list").value;
                        const zone = document.getElementById("filter-zone-list").value;

                        // สร้าง URL Query
                        const params = new URLSearchParams({
                            date1,
                            date2,
                            gender,
                            min_age: minAge,
                            max_age: maxAge,
                            symptom,
                            hospital,
                            zone
                        });

                        // อัปเดต URL โดยไม่โหลดหน้าใหม่
                        const newUrl = window.location.pathname + "?" + params.toString();
                        window.history.replaceState({}, "", newUrl);

                        // โหลดข้อมูลใหม่ผ่าน AJAX
                        fetch(newUrl)
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');

                                // อัปเดตข้อมูลกราฟใหม่
                                const newLabels = JSON.parse(doc.getElementById('chart-labels').textContent);
                                const newMaleData = JSON.parse(doc.getElementById('chart-maleData').textContent);
                                const newFemaleData = JSON.parse(doc.getElementById('chart-femaleData').textContent);

                                // อัปเดตกราฟ Chart.js
                                mychart.data.labels = newLabels;
                                mychart.data.datasets[0].data = newMaleData;
                                mychart.data.datasets[1].data = newFemaleData;
                                mychart.update();
                            })
                            .catch(error => console.error('Error fetching updated data:', error));
                    }

                    // ตั้งค่า Event Listeners สำหรับฟิลเตอร์
                    document.getElementById("calendarSelect1").flatpickr({
                        dateFormat: "Y-m-d",
                        onChange: updateFilters
                    });
                    document.getElementById("calendarSelect2").flatpickr({
                        dateFormat: "Y-m-d",
                        onChange: updateFilters
                    });
                    document.getElementById("filter-gender-list").addEventListener("change", updateFilters);
                    document.getElementById("filter-symtom-list").addEventListener("change", updateFilters);
                    document.getElementById("minAge").addEventListener("input", updateFilters);
                    document.getElementById("maxAge").addEventListener("input", updateFilters);
                    document.getElementById("filter-hospital-list").addEventListener("change", updateFilters);
                    document.getElementById("filter-zone-list").addEventListener("change", updateFilters);
                });
    </script>
</body>

</html>