<?php
include 'username.php';

// รับค่า emergency_id จาก URL
$emergency_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if ($emergency_id) {
    // คิวรีข้อมูลพร้อม JOIN ตาราง ambulance แบบไม่ใช้ตัวย่อ
    $sql_query = "
        SELECT order_emergency_case.*, ambulance.ambulance_plate
        FROM order_emergency_case
        LEFT JOIN ambulance ON order_emergency_case.ambulance_id = ambulance.ambulance_id
        WHERE order_emergency_case.order_emergency_case_id = ?
    ";

    $statement = $conn->prepare($sql_query);
    $statement->bind_param("i", $emergency_id);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ดึงข้อมูลป้ายทะเบียนรถพยาบาล
        $ambulance_plate = $row['ambulance_plate'];

    } else {
        echo "ไม่พบข้อมูลในฐานข้อมูลสำหรับ emergency_id: " . $emergency_id;
    }
} else {
    echo "ไม่มีค่า emergency_id";
}

// สุ่มข้อมูลผู้บริหาร
$sql_executive = "
    SELECT executive_id, executive_firstname, executive_lastname 
    FROM executive 
    ORDER BY RAND() 
    LIMIT 1
";
$statement_exec = $conn->prepare($sql_executive);
$statement_exec->execute();
$result_exec = $statement_exec->get_result();
$executive = $result_exec->fetch_assoc();
$executive_firstname = $executive['executive_firstname'];
$executive_lastname = $executive['executive_lastname'];

?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงิน</title>
    <link rel="stylesheet" href="css/style_bill.css">
    <style>
        body {
            font-family: Tahoma, sans-serif;
            padding: 5px;
            background: #fff;
        }

        .receipt {
            max-width: 900px;
            margin: auto;
            padding: 30px;
        }

        .header,
        .footer {
            text-align: center;
        }

        .company-info {
            float: right;
            text-align: right;
        }

        .logo {
            width: 120px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 12px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            font-size: 12px;
            border-right: 1px solid #000;
            /* เส้นคั่นแนวตั้ง */
        }

        /* ปรับเส้นคั่นแนวตั้งสำหรับคอลัมน์แรก */
        th:first-child,
        td:first-child {
            border-left: 1px solid #000;
            /* เส้นคั่นทางด้านซ้ายของคอลัมน์แรก */
        }

        th {
            border-top: 2px solid #000;
            /* เส้นคั่นด้านบนหัวตาราง */
            border-bottom: 2px solid #000;
            /* เส้นคั่นด้านล่างหัวตาราง */
        }

        td {
            border-bottom: none;
            /* ลบเส้นคั่นแนวนอนในแถวข้อมูล */
        }

        tfoot td {
            border-top: 2px solid #000;
            /* เส้นคั่นด้านบนของตารางรวม */
            border-bottom: 2px solid #000;
            /* เพิ่มเส้นคั่นด้านล่างในแถวสุดท้าย */
        }

        .no-border {
            border: none;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 40%;
            text-align: center;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px dotted #000;
        }

        /* ซ่อนข้อมูลที่ไม่ต้องการแสดงตอนพิมพ์ */
        @media print {
            body {
                visibility: hidden;
            }

            .receipt {
                visibility: visible;
                position: absolute;
                top: 0;
            }

            .footer {
                display: none;
            }

            /* ซ่อน URL หรือข้อความไม่ต้องการแสดง */
            footer {
                display: none;
            }

            /* ซ่อนข้อความหรือคำสั่งที่เกี่ยวข้องกับการพิมพ์ที่ขอบล่างซ้าย */
            @page {
                margin: 0;
            }

            .receipt {
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="receipt">
        <div class="clearfix">
            <img src="image/Logo.png" alt="Logo" class="logo">
            <div class="company-info">
                <strong>บริษัท Sky Medical Service จำกัด</strong><br>
                123 ถนนสุขภาพดี แขวงใจดี เขตปลอดภัย กรุงเทพฯ 10200<br>
                โทร: 097-20-30-555| อีเมล: skymedicalservice@gmail.com
            </div>
        </div>
        <hr>
        <div class="header">
            <h2>ใบเสร็จรับส่งผู้ป่วยเคสฉุกเฉิน / Emergency patient transfer receipt</h2>
            <p style="margin-top: -10px;">(ต้นฉบับ / Original)</p>
        </div>

        <?php if ($row): ?>
            <div class="detail">
                <p><strong>เลขที่ใบเสร็จ / Receipt No.:</strong> 0012<?= htmlspecialchars($row['order_emergency_case_id']) ?></p>
                <p><strong>ผู้ติดต่อ / Contact person:</strong> <?= htmlspecialchars($row['order_emergency_case_communicant']) ?></p>
                <p><strong>เบอร์โทร / Phone:</strong> <?= htmlspecialchars($row['order_emergency_case_communicant_phone']) ?></p>
                <p><strong>วันที่ เวลา ที่ออกใบเสร็จ / Date time of receipt issue:</strong> <?= date("d/m/Y H:i") ?></p>
                <p><strong>ออกโดย / Issuer:</strong> <?= htmlspecialchars($executive_firstname) ?> <?= htmlspecialchars($executive_lastname) ?></p>

            </div>

            <table>
                <thead>
                    <tr>
                        <th>ชื่อผู้รับบริการ<br>Route</th>
                        <th>สถานที่เกิดเหตุ<br>Event location</th>
                        <th>โรงพยาบาลที่ไปส่ง<br>Route</th>
                        <th>ประเภทอุบัติเหตุ<br>Route</th>

                        <th>เลขทะเบียนรถ<br>Vehicle registration number</th>
                        <th>วันเวลาที่รับบริการ<br>Travel date and time</th>
                        <th>ราคารวม<br>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($row['order_emergency_case_patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['order_emergency_case_accident_location']) ?></td>
                        <td><?= htmlspecialchars($row['order_emergency_case_hospital_waypoint']) ?></td>
                        <td><?= htmlspecialchars($row['order_emergency_case_reason']) ?></td>

                        <td><?= htmlspecialchars($row['order_emergency_case_patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['order_emergency_case_date']) ?><br><?= htmlspecialchars($row['order_emergency_case_time']) ?></td>
                        <td class="text-end"><?= number_format($row['order_emergency_case_price'], 2) ?></td>
                    </tr>
                </tbody>
                <?php
                $vat = ($row['order_emergency_case_price'] * 7) / 100;
                $total = $row['order_emergency_case_price'] + $vat;
                ?>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align:right;"><strong>Vat 7%</strong></td>
                        <td><strong><?= number_format($vat, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align:right;"><strong>ยอดชำระทั้งหมด (บาท)</strong></td>
                        <td><strong><?= number_format($total, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p><strong>ไม่พบข้อมูลใบเสร็จ</strong></p>
        <?php endif; ?>

        <div class="signature-section">
            <div class="signature">
                <div class="signature-line"></div>
                ผู้รับบริการ / Receiver
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                ผู้จัดทำ / Issuer
            </div>
        </div>

        <div class="footer">
            <p>ขอบคุณที่ใช้บริการ</p>
        </div>
    </div>
</body>

</html>