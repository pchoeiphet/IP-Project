<?php
include 'username.php';

// query ข้อมูลจาก ambulance booking และ event booking

$selected_level = isset($_GET['level']) ? $_GET['level'] : ['1', '2', '3'];
$selected_booking_type = isset($_GET['booking_type']) ? $_GET['booking_type'] : 'ทั้งหมด';

$booking_type = $selected_booking_type;

$where_clause = "WHERE ";
$levels = "";
if (!empty($selected_level)) {
    $levels = implode("','", $selected_level);
    $where_clause .= " ambulance_level IN ('$levels')";
}
if ($booking_type == 'ambulance_booking') {
    $total_distance_subquery = "
        SELECT 
            ambulance_id,
            ambulance_booking_distance AS total_distance
        FROM ambulance_booking
    ";
} elseif ($booking_type == 'event_booking') {
    $total_distance_subquery = "
        SELECT 
            ambulance_id,
            event_booking_distance AS total_distance
        FROM event_booking
    ";
} else {
    $total_distance_subquery = "
        SELECT 
            ambulance_id,
            ambulance_booking_distance AS total_distance
        FROM ambulance_booking
        UNION ALL
        SELECT 
            ambulance_id,
            event_booking_distance AS total_distance
        FROM event_booking
    ";
}

$query_ambuDistance = mysqli_query(
    $conn,
    "SELECT 
        a.ambulance_id,
        a.ambulance_plate,
        a.ambulance_level,
        total.total_distance,
        COALESCE(r.repair_count, 0) AS repair_count
    FROM ambulance a
    JOIN (
        SELECT 
            ambulance_id,
            SUM(total_distance) AS total_distance
        FROM (
            $total_distance_subquery
        ) AS combined_distances
        GROUP BY ambulance_id
    ) AS total ON a.ambulance_id = total.ambulance_id

    LEFT JOIN (
        SELECT 
            ambulance_id,
            COUNT(*) AS repair_count
        FROM repair 
        GROUP BY ambulance_id
    ) AS r ON a.ambulance_id = r.ambulance_id

    $where_clause

    ORDER BY total.total_distance DESC;"
);


$ambuDistance = mysqli_fetch_all($query_ambuDistance, MYSQLI_ASSOC);

$distance_labels = [];
$total_distances = [];
$repair_counts = [];

foreach ($ambuDistance as $row) {
    $distance_labels[] = $row["ambulance_plate"];
    $total_distances[] = $row["total_distance"];
    $repair_counts[] = $row["repair_count"];
}

// ------------------------------
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
    <title>ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            width: 100% !important;
            height: 100% !important;
            max-width: 600px;
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
            <a href="case_report_page.php" class="nav-item">ดูสรุปรายงานเคส</a>
            <a href="history_fixed_page.php" class="nav-item active">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</a>
            <a href="static_car_page.php" class="nav-item">สถิติการใช้งานรถ</a>
            <a href="summary_buy.php" class="nav-item">สรุปยอดขาย</a>
        </nav>
    </header>

    <div id="chart-distance_labels" style="display: none;"><?php echo json_encode($distance_labels); ?></div>
    <div id="chart-total_distances" style="display: none;"><?php echo json_encode($total_distances); ?></div>
    <div id="chart-repair_counts" style="display: none;"><?php echo json_encode($repair_counts); ?></div>

    <h1 style="text-align: center;">ประวัติระยะทางและจำนวนการซ่อมรถพยาบาล</h1>
    
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

                    <select class="filter-select" onchange="location = this.value;">
                        <option value="history_fixed_page.php">ประวัติการส่งซ่อมและอุปกรณ์การแพทย์</option>
                        <option value="history_ambuByDistance.php" selected>ประวัติระยะทางและจำนวนการซ่อมรถพยาบาล</option>
                    </select>

                    <label for="">ระดับรถ:</label>
                    <div class="checkbox">
                        <input id="level_select1" type="checkbox" name="level[]" value="1" checked> Level 1
                        <input id="level_select2" type="checkbox" name="level[]" value="2" checked> Level 2
                        <input id="level_select3" type="checkbox" name="level[]" value="3" checked> Level 3
                    </div> <br>

                    <label for="booking-type">ประเภทการจอง:</label>
                    <select id="booking-type" name="booking_type" class="filter-select">
                        <option value="ทั้งหมด" <?php if ($selected_booking_type == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                        <option value="event_booking" <?php if ($selected_booking_type == "event_booking") echo "selected"; ?>>Event Booking</option>
                        <option value="ambulance_booking" <?php if ($selected_booking_type == "ambulance_booking") echo "selected"; ?>>Ambulance Booking</option>
                    </select>
                    
                    <a href="history_ambuByDistance.php" class="reset-button" id="reset-button">reset</a>

                </div>
            </div>
        </div>
    </main>

    <canvas id="ambulanceChart"></canvas>

    <script>
        // สร้างกราฟ Chart.js

        var distance_labels = <?php echo json_encode($distance_labels); ?>;
        var total_distances = <?php echo json_encode($total_distances); ?>;
        var repair_counts = <?php echo json_encode($repair_counts); ?>;

        console.log("distance_labels: ", distance_labels);
        console.log("total_distances: ", total_distances);
        console.log("repair_counts: ", repair_counts);

        var disAmbuChart = new Chart(document.getElementById("ambulanceChart"), {
            type: 'bar',
            data: {
                labels: distance_labels,
                datasets: [{
                        label: 'ระยะทางที่วิ่ง (กม.)',
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        data: total_distances
                    },
                    {
                        label: 'จำนวนครั้งที่ซ่อม (ครั้ง)',
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        data: repair_counts
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'สรุปจำนวนการซ่อมและระยะทางทั้งหมดที่รถพยาบาลวิ่ง',
                        font: {
                            size: 18
                        }
                    },
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

            });

            // ฟังก์ชันสำหรับอัปเดตฟิลเตอร์และโหลดข้อมูลใหม่
            function updateFilters() {

                const bookingType = document.getElementById("booking-type").value;

                const selectedLevels = [];
                document.querySelectorAll('input[name="level[]"]:checked').forEach((checkbox) => {
                    selectedLevels.push(checkbox.value);
                });

                // สร้าง URL Query
                const params = new URLSearchParams({
                    booking_type: bookingType
                });
                //เพิ่มระดับรถที่เลือกเข้า params
                selectedLevels.forEach(level => {
                    params.append("level[]", level);
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

                        const newDistanceLabels = JSON.parse(doc.getElementById('chart-distance_labels').textContent);
                        const newTotalDistances = JSON.parse(doc.getElementById('chart-total_distances').textContent);
                        const newRepairCounts = JSON.parse(doc.getElementById('chart-repair_counts').textContent);

                        // อัปเดตกราฟ Chart.js
                        disAmbuChart.data.labels = newDistanceLabels;
                        disAmbuChart.data.datasets[0].data = newTotalDistances;
                        disAmbuChart.data.datasets[1].data = newRepairCounts;
                        disAmbuChart.update();
                    })
                    .catch(error => console.error('Error fetching updated data:', error));
            }

            // เพิ่ม listener ให้ checkbox ทุกตัว
            document.querySelectorAll('input[name="level[]"]').forEach((checkbox) => {
                checkbox.addEventListener("change", updateFilters);
            });
            document.getElementById("booking-type").addEventListener("change", updateFilters);
        });
    </script>
</body>

</html>