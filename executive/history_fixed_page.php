<?php
include 'username.php';

// รับค่าฟิลเตอร์จาก GET parameters

date_default_timezone_set('Asia/Bangkok');

$selected_date1 = date('Y-m-d');
if (isset($_GET['date1']) && !empty($_GET['date1'])) {
    $selected_date1 = $_GET['date1'];
}
$selected_date2 = date('Y-m-d');
if (isset($_GET['date2']) && !empty($_GET['date2'])) {
    $selected_date2 = $_GET['date2'];
}

$selected_level = isset($_GET['level']) ? $_GET['level'] : ['1', '2', '3'];
$selected_type = isset($_GET['type']) ? $_GET['type'] : "ทั้งหมด";
$selected_repairing = isset($_GET['repairing']) ? $_GET['repairing'] : "ทั้งหมด";
$selected_reason = isset($_GET['reason']) ? $_GET['reason'] : "ทั้งหมด";
$selected_status = isset($_GET['status']) ? $_GET['status'] : "ทั้งหมด";
$selected_cost = isset($_GET['cost']) ? $_GET['cost'] : " > 1";

// สร้าง WHERE Clause ตามฟิลเตอร์ที่เลือก
$where_clause = "WHERE ";
if ($selected_date1 && $selected_date2) {
    $where_clause .= " DATE(repair_date) BETWEEN '$selected_date1' AND '$selected_date2'";
}
$levels = "";
if (!empty($selected_level)) {
    // แปลง array เป็น string เพื่อใช้ใน SQL
    $levels = implode("','", $selected_level);
    $where_clause .= " AND ambulance_level IN ('$levels')";
}

if ($selected_type !== "ทั้งหมด") {
    $where_clause .= " AND repair_type = '$selected_type'";
}
if ($selected_repairing !== "ทั้งหมด") {
    $where_clause .= " AND repair_repairing = '$selected_repairing'";
}
if ($selected_reason !== "ทั้งหมด") {
    $where_clause .= " AND repair_reason = '$selected_reason'";
}
if ($selected_status !== "ทั้งหมด") {
    $where_clause .= " AND repair_status = '$selected_status'";
}
if ($selected_cost !== "") {
    $where_clause .= " AND ( repair_cost $selected_cost )";
}

// query ข้อมูลประวัติการซ่อมรถพยาบาลและอุปกรณ์การแพทย์ (กราฟ)
$sql = mysqli_query(
    $conn,
    "SELECT * , 
        SUM(CASE WHEN ambulance_level = '1' THEN 1 ELSE 0 END) AS ambulance_level1,
        SUM(CASE WHEN ambulance_level = '2' THEN 1 ELSE 0 END) AS ambulance_level2,
        SUM(CASE WHEN ambulance_level = '3' THEN 1 ELSE 0 END) AS ambulance_level3
        from repair 
        INNER JOIN ambulance on ambulance.ambulance_id = repair.ambulance_id
        INNER JOIN repair_staff on repair.repair_staff_id = repair_staff.repair_staff_id
        $where_clause GROUP BY repair_type"
);
$result = mysqli_fetch_all($sql, MYSQLI_ASSOC);

// เตรียมข้อมูลสำหรับกราฟ
$labels = [];
$level1Data = [];
$level2Data = [];
$level3Data = [];

foreach ($result as $row) {
    $labels[] = $row['repair_type'];
    $level1Data[] = $row['ambulance_level1'];
    $level2Data[] = $row['ambulance_level2'];
    $level3Data[] = $row['ambulance_level3'];
}

// ---------------------------------------------------------------------------------

// ข้อมูลที่ปรากฏในฟิลเตอร์

// ประเภท
$type_query = mysqli_query(
    $conn,
    "SELECT DISTINCT repair_type FROM repair"
);
$type_data = mysqli_fetch_all($type_query, MYSQLI_ASSOC);

// เหตุผล
$reason_query = mysqli_query(
    $conn,
    "SELECT DISTINCT repair_reason FROM repair WHERE repair_reason != ''"
);
$reason_data = mysqli_fetch_all($reason_query, MYSQLI_ASSOC);

// อะไหล่ที่ซ่อม
$repairing_query = mysqli_query(
    $conn,
    "SELECT DISTINCT repair_repairing FROM repair WHERE repair_repairing != ''"
);
$repairing_data = mysqli_fetch_all($repairing_query, MYSQLI_ASSOC);

// สถานะ
$status_query = mysqli_query(
    $conn,
    "SELECT DISTINCT repair_status FROM repair"
);
$status_data = mysqli_fetch_all($status_query, MYSQLI_ASSOC);

// ------------------------------

// นับจำนวนรถทั้งหมด
$count_all_ambu_query = mysqli_query(
    $conn,
    "SELECT COUNT(ambulance_id) as AllAmbu FROM ambulance"
);
$all_ambu_data = mysqli_fetch_all($count_all_ambu_query, MYSQLI_ASSOC);
// เก็บจำนวนรถทั้งหมดไว้ในตัวแปรชื่อว่า $all_ambu
$all_ambu = 0;
foreach ($all_ambu_data as $num) {
    foreach ($num as $key => $value) {
        $all_ambu = $value;
    }
}

// นับจำนวนรถที่พร้อม
$count_ready_ambu_query = mysqli_query(
    $conn,
    "SELECT COUNT(ambulance_id) as readyAmbu FROM ambulance WHERE ambulance_status='พร้อม'"
);
$ready_ambu_data = mysqli_fetch_all($count_ready_ambu_query, MYSQLI_ASSOC);
// เก็บจำนวนรถทั้งหมดไว้ในตัวแปรชื่อว่า $ready_ambu
$ready_ambu = 0;
foreach ($ready_ambu_data as $num) {
    foreach ($num as $key => $value) {
        $ready_ambu = $value;
    }
}

// นับจำนวนรถที่ไม่พร้อม
$count_notReady_ambu_query = mysqli_query(
    $conn,
    "SELECT COUNT(ambulance_id) as readyAmbu FROM ambulance WHERE ambulance_status='ไม่พร้อม'"
);
$notReady_ambu_data = mysqli_fetch_all($count_notReady_ambu_query, MYSQLI_ASSOC);
// เก็บจำนวนรถทั้งหมดไว้ในตัวแปรชื่อว่า $notReady_ambu
$notReady_ambu = 0;
foreach ($notReady_ambu_data as $num) {
    foreach ($num as $key => $value) {
        $notReady_ambu = $value;
    }
}

// Query นับจำนวนรถพยาบาลแยกตาม level และ status
$query = mysqli_query($conn, "
    SELECT ambulance_level, ambulance_status, COUNT(*) as count
    FROM ambulance
    GROUP BY ambulance_level, ambulance_status
");

// เตรียม array เก็บข้อมูล
$levels = [];
$ready_data = [];
$not_ready_data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $level = $row['ambulance_level'];
    $status = $row['ambulance_status'];
    $count = (int)$row['count'];

    // เพิ่มคำว่า "ระดับ" ตอนเก็บค่า label
    $label_with_prefix = "ระดับ " . $level;

    if (!in_array($label_with_prefix, $levels)) {
        $levels[] = $label_with_prefix;
    }

    // ใช้ label ที่มี prefix เป็น key
    if ($status === 'พร้อม') {
        $ready_data[$label_with_prefix] = $count;
    } else if ($status === 'ไม่พร้อม') {
        $not_ready_data[$label_with_prefix] = $count;
    }
}

foreach ($levels as $level) {
    if (!isset($ready_data[$level])) $ready_data[$level] = 0;
    if (!isset($not_ready_data[$level])) $not_ready_data[$level] = 0;
}

// เตรียมข้อมูลแบบเรียงตามลำดับ levels
$ready_counts = [];
$not_ready_counts = [];

foreach ($levels as $level) {
    $ready_counts[] = $ready_data[$level];
    $not_ready_counts[] = $not_ready_data[$level];
}


// ------------------------------
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/history_fixed_page.css">
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
            width: 80% !important;
            height: 60% !important;
            max-width: 600px;
            max-height: 400px;
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

    <div id="chart-labels" style="display: none;"><?php echo json_encode($labels); ?></div>
    <div id="chart-level1Data" style="display: none;"><?php echo json_encode($level1Data); ?></div>
    <div id="chart-level2Data" style="display: none;"><?php echo json_encode($level2Data); ?></div>
    <div id="chart-level3Data" style="display: none;"><?php echo json_encode($level3Data); ?></div>

    <br>
    <h1 style="text-align: center;">ประวัติการส่งซ่อมรถและอุปกรณ์การแพทย์</h1>

    <div style="display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap;">
        <canvas id="ambuStatusChart"></canvas>
        <canvas id="history"></canvas>

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
                            <option value="history_fixed_page.php" selected>ประวัติการส่งซ่อมและอุปกรณ์การแพทย์</option>
                            <option value="history_ambuByDistance.php">ประวัติระยะทางและจำนวนการซ่อมรถพยาบาล</option>
                        </select>

                        <label for="calendarSelect">เลือกวันที่:</label>
                        <input class="calendar-selected" id="calendarSelect1" type="text" placeholder="เลือกวันที่" value="2025-01-22">
                        <input class="calendar-selected" id="calendarSelect2" type="text" placeholder="เลือกวันที่" value="2025-01-22">

                        <label for="">ระดับรถ:</label>
                        <div class="checkbox">
                            <input id="level_select1" type="checkbox" name="level[]" value="1" checked> Level 1
                            <input id="level_select2" type="checkbox" name="level[]" value="2" checked> Level 2
                            <input id="level_select3" type="checkbox" name="level[]" value="3" checked> Level 3
                        </div> <br>

                        <label for="filter-type">ประเภท:</label>
                        <select id="filter-type-list" class="filter-select">
                            <option value="ทั้งหมด" <?php if ($selected_type == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                            <option value="อุปกรณ์ทางการแพทย์" <?php if ($selected_type == "อุปกรณ์ทางการแพทย์") echo "selected"; ?>>อุปกรณ์ทางการแพทย์</option>
                            <option value="รถพยาบาล" <?php if ($selected_type == "รถพยาบาล") echo "selected"; ?>>รถพยาบาล</option>
                        </select>

                        <label for="filter-repairing">อะไหล่:</label>
                        <select id="filter-repairing" class="filter-select">
                            <option value="ทั้งหมด" <?php if ($selected_repairing == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                            <?php foreach ($repairing_data as $row) :
                                $value = $row["repair_repairing"];
                            ?>
                                <option value="<?php echo $value; ?>" <?php if ($selected_repairing == $value) echo "selected"; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="filter-reason">สาเหตุ:</label>
                        <select id="filter-reason" class="filter-select">
                            <option value="ทั้งหมด" <?php if ($selected_reason == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                            <?php foreach ($reason_data as $row) :
                                $value = $row["repair_reason"];
                            ?>
                                <option value="<?php echo $value; ?>" <?php if ($selected_reason == $value) echo "selected"; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="filter-status">สถานะการซ่อม:</label>
                        <select id="filter-status" class="filter-select">
                            <option value="ทั้งหมด" <?php if ($selected_status == "ทั้งหมด") echo "selected"; ?>>ทั้งหมด</option>
                            <?php foreach ($status_data as $row) :
                                $value = $row["repair_status"];
                            ?>
                                <option value="<?php echo $value; ?>" <?php if ($selected_status == $value) echo "selected"; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="">ค่าใช้จ่าย:</label>
                        <select id="filter-cost" class="filter-select">
                            <option value="" <?php if ($selected_cost == "") echo "selected"; ?> selected>ทั้งหมด</option>
                            <option value=" BETWEEN 1 AND 9999" <?php if ($selected_cost == " BETWEEN 1 AND 9999") echo "selected"; ?>>ต่ำกว่า 10,000 บาท</option>
                            <option value=" BETWEEN 10000 AND 50000" <?php if ($selected_cost == " BETWEEN 10000 AND 50000") echo "selected"; ?>>10,000-50,000 บาท</option>
                            <option value=" > 50000" <?php if ($selected_cost == " > 50000") echo "selected"; ?>>มากกว่า 50,000 บาท</option>
                        </select>
                        <a href="history_fixed_page.php" class="reset-button" id="reset-button">reset</a>

                    </div>
                </div>
            </div>
        </main>
        <main class="main-content">
            <table>
                <thead>
                    <tr>
                        <th>จำนวนรถทั้งหมด</th>
                        <th>จำนวนรถที่พร้อม</th>
                        <th>จำนวนรถที่ไม่พร้อม</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $all_ambu?> คัน</td>
                        <td><?php echo $ready_ambu?> คัน</td>
                        <td><?php echo $notReady_ambu?> คัน</td>
                    </tr>
                </tbody>
            </table>
        </main>


    </div>


    <script>
        // จำนวนรถพยาบาลที่พร้อมและไม่พร้อม

        // ตัวเลขใช้คำนวณ
        var allAmbu = <?php echo $all_ambu; ?>;
        var readyAmbu = <?php echo $ready_ambu; ?>;
        var notReadyAmbu = <?php echo $notReady_ambu; ?>;

        // คำนวณ % รถที่ไม่พร้อมต่อจำนวนรถทั้งหมด
        let percent = (notReadyAmbu / allAmbu) * 100;
        console.log("notReadyAmbu: ", notReadyAmbu);
        console.log("allAmbu: ", allAmbu);
        console.log("readyAmbu: ", readyAmbu);
        console.log("percent: ", percent);

        // ถ้า % รถที่ไม่พร้อมมากกว่า 65 ให้ขึ้นแจ้งเตือน
        if (percent > 65) {
            alert("รถพยาบาลไม่พร้อมใช้งานมากกว่า 65%");
        }

        // ข้อมูลที่ใช้ในกราฟ
        const levels = <?php echo json_encode($levels); ?>;
        const readyCounts = <?php echo json_encode($ready_counts); ?>;
        const notReadyCounts = <?php echo json_encode($not_ready_counts); ?>;

        const AmbuChart = document.getElementById('ambuStatusChart').getContext('2d');
        new Chart(AmbuChart, {
            type: 'bar',
            data: {
                labels: levels,
                datasets: [{
                        label: 'พร้อม',
                        data: readyCounts,
                        backgroundColor: '#B7E5B4'
                    },
                    {
                        label: 'ไม่พร้อม',
                        data: notReadyCounts,
                        backgroundColor: '#F28585'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนรถ (คัน)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'ระดับรถพยาบาล (1-3)'
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
                        text: 'จำนวนรถพยาบาลแยกตามระดับและสถานะ'
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                },
            }
        });


        // รับข้อมูลจาก PHP เพื่อใช้ในกราฟ
        const labels = <?php echo json_encode($labels); ?>;
        const level1Data = <?php echo json_encode($level1Data); ?>;
        const level2Data = <?php echo json_encode($level2Data); ?>;
        const level3Data = <?php echo json_encode($level3Data); ?>;

        Chart.defaults.elements.bar.borderRadius = 5;

        // สร้างกราฟด้วย Chart.js
        const mychart1 = new Chart(document.getElementById("history"), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'ระดับ 1',
                        data: level1Data,
                        backgroundColor: 'rgba(131, 255, 141, 0.5)',
                    }, {
                        label: 'ระดับ 2',
                        data: level2Data,
                        backgroundColor: 'rgba(99, 213, 255, 0.5)',
                    },
                    {
                        label: 'ระดับ 3',
                        data: level3Data,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'ประเภทการซ่อม'
                        }
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'จำนวนครั้งที่ซ่อม (ครั้ง)'
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
                        text: 'สรุปการซ่อมรถพยาบาลและอุปกรณ์ทางการแพทย์',
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
                const selectedLevels = [];
                document.querySelectorAll('input[name="level[]"]:checked').forEach((checkbox) => {
                    selectedLevels.push(checkbox.value);
                });
                const type = document.getElementById("filter-type-list").value;
                const repairing = document.getElementById("filter-repairing").value;
                const reason = document.getElementById("filter-reason").value;
                const status = document.getElementById("filter-status").value;
                const cost = document.getElementById("filter-cost").value;

                // สร้าง URL Query
                const params = new URLSearchParams({
                    date1,
                    date2,
                    type,
                    repairing,
                    reason,
                    status,
                    cost
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
                        const newLabels = JSON.parse(doc.getElementById('chart-labels').textContent);
                        const newlevel1Data = JSON.parse(doc.getElementById('chart-level1Data').textContent);
                        const newlevel2Data = JSON.parse(doc.getElementById('chart-level2Data').textContent);
                        const newlevel3Data = JSON.parse(doc.getElementById('chart-level3Data').textContent);

                        // อัปเดตกราฟ Chart.js
                        mychart1.data.labels = newLabels;
                        mychart1.data.datasets[0].data = newlevel1Data;
                        mychart1.data.datasets[1].data = newlevel2Data;
                        mychart1.data.datasets[2].data = newlevel3Data;
                        mychart1.update();
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
            // เพิ่ม listener ให้ checkbox ทุกตัว
            document.querySelectorAll('input[name="level[]"]').forEach((checkbox) => {
                checkbox.addEventListener("change", updateFilters);
            });
            document.getElementById("filter-type-list").addEventListener("change", updateFilters);
            document.getElementById("filter-repairing").addEventListener("change", updateFilters);
            document.getElementById("filter-reason").addEventListener("change", updateFilters);
            document.getElementById("filter-status").addEventListener("change", updateFilters);
            document.getElementById("filter-cost").addEventListener("change", updateFilters);
        });
    </script>
</body>

</html>