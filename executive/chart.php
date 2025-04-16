<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'intpro';

$conn = new mysqli($host, $user, $password, $database);


$sqrt = "SELECT member.member_gender, COUNT(*) as count
        FROM `order`
        JOIN `equipment` ON `order`.equipment_id = equipment.equipment_id
        JOIN `member` ON `order`.member_id = member.member_id
        GROUP BY member.member_gender";

$result = mysqli_query($conn, $sqrt);

$label = [];
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $label[] = $row['member_gender'];
        $data[] = $row['count'];
    }
}
// ----------------------------------------------------------------------
$sqrt1 = "SELECT member.member_province, COUNT(*) as count
        FROM `order`
        JOIN `equipment` ON `order`.equipment_id = equipment.equipment_id
        JOIN `member` ON `order`.member_id = member.member_id
        GROUP BY member.member_province";

$result1 = mysqli_query($conn, $sqrt1);

$label1 = [];
$data1 = [];

if ($result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $label1[] = $row['member_province'];
        $data1[] = $row['count'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Data to Chart</title>
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
    </style>
</head>

<body>
    <h2>Sales Data Chart</h2>
    <canvas id="myChart"></canvas>

    <script>
        const labels = <?php echo json_encode($label); ?>;
        const data = <?php echo json_encode($data); ?>;

        new Chart(document.getElementById("myChart"), {
            type: 'pie',
            data: {
                labels: ['<?php echo $label[0] ?> <?php echo $data[0]; ?> ', '<?php echo $label[1] ?> <?php echo $data[1]; ?>'],
                datasets: [{
                    label: 'จำนวนผู้ใช้บริการ',
                    data: [<?php echo $data[0]; ?>, <?php echo $data[1]; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)', // น้ำเงิน (ชาย)
                        'rgba(255, 99, 132, 0.5)' // แดง (หญิง)
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },



            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'เพศชายและหญิงที่ซื้อบริการกับเรา'
                    }
                }
            },
        });
    </script>

    <!-- ------------------------------------------------------------------------------------------------------------------------------------------------ -->
    <h2>Sales Data Chart</h2>
    <canvas id="provinceChart"></canvas>

    <script>
        const labels1 = <?php echo json_encode($label1); ?>; // รายชื่อจังหวัด
        const dataValues1 = <?php echo json_encode($data1); ?>; // จำนวนผู้ใช้บริการในแต่ละจังหวัด

        // สร้าง dataset แยกแต่ละจังหวัด
        const datasets = labels1.map((province, index) => ({
            label: `${province}`,
            data: [dataValues1[index]], // ค่าของแต่ละจังหวัด
            backgroundColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.5)`,
            borderColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 1)`,
            borderWidth: 1
        }));

        new Chart(document.getElementById("provinceChart"), {
            type: 'bar',
            data: {
                labels: ["จังหวัดที่ใช้บริการ"], // ใช้เป็นค่าเดียวกันสำหรับทุก dataset
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }, // มีตัวเลือกติ๊กเปิด/ปิด
                    title: {
                        display: true,
                        text: 'จำนวนผู้ใช้บริการตามจังหวัด'
                    }
                }
            }
        });
    </script>
</body>

</html>