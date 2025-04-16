<?php
session_start();
include 'username.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

if (!isset($_GET['event_ids'])) {
    echo "ไม่พบรหัสคำสั่งซื้อ";
    exit();
}
$executive_id = isset($_GET['executive_id']) ? $_GET['executive_id'] : null;

$event_ids = explode(',', $_GET['event_ids']);
$event_ids = array_map('intval', $event_ids);
$placeholders = implode(',', array_fill(0, count($event_ids), '?'));

$sql = "SELECT 
            event_booking.event_booking_id,
            event_booking.event_booking_type,
            event_booking.event_booking_location,
            event_booking.event_booking_province,
            event_booking.event_booking_date,
            event_booking.event_booking_start_time,
            event_booking.event_booking_finish_time,
            event_booking.event_booking_price,
            event_booking.event_booking_distance,
            event_booking.event_booking_amount_nurse,
            event_booking.event_booking_amount_ambulance,
            member.member_firstname,
            member.member_lastname,
            member.member_phone,
            ambulance.ambulance_plate,
            ambulance.ambulance_level
        FROM event_booking
        JOIN member ON event_booking.member_id = member.member_id
        JOIN ambulance ON event_booking.ambulance_id = ambulance.ambulance_id
        WHERE event_booking.member_id = ? AND event_booking.event_booking_id IN ($placeholders)
        ORDER BY event_booking.event_booking_date DESC";

// ตัวแปรสำหรับบีบอัดการส่งค่า
$stmt = $conn->prepare($sql);

// กำหนดประเภทของตัวแปรที่ส่งไป (1 สำหรับ integer ของ member_id และจำนวนที่เท่ากับ event_ids)
$types = str_repeat('i', count($event_ids) + 1); // เพิ่ม 1 สำหรับ $member_id
$params = array_merge([$member_id], $event_ids); // รวมค่า $member_id และ event_ids

// ทำการ binding parameter
$stmt->bind_param($types, ...$params); // ใช้ ...$params เพื่อส่งผ่านหลายๆ ค่า

// Execute statement
$stmt->execute();
$result = $stmt->get_result();


$orders = $result->fetch_all(MYSQLI_ASSOC);
date_default_timezone_set("Asia/Bangkok");


if ($executive_id) {
    // ดึงข้อมูลผู้บริหารจากฐานข้อมูล
    $sql_exec = "SELECT executive_firstname, executive_lastname FROM executive WHERE executive_id = ?";
    $stmt_exec = $conn->prepare($sql_exec);
    $stmt_exec->bind_param("i", $executive_id);
    $stmt_exec->execute();
    $result_exec = $stmt_exec->get_result();

    // ถ้ามีข้อมูลของผู้บริหาร
    if ($executive = $result_exec->fetch_assoc()) {
        $executive_firstname = $executive['executive_firstname'];
        $executive_lastname = $executive['executive_lastname'];
    } else {
        $executive_firstname = "ไม่พบข้อมูลผู้บริหาร";
        $executive_lastname = "";
    }
}


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
            <h2>ใบเสร็จจองรถสำหรับงาน Event / Receipt Event Booking</h2>
            <p style="margin-top: -10px;">(ต้นฉบับ / Original)</p>
        </div>

        <?php if (count($orders) > 0): ?>
            <?php $first = $orders[0]; ?>
            <div class="detail">
                <p><strong>เลขที่ใบเสร็จ / Receipt No.:</strong> 0013<?= htmlspecialchars($first['event_booking_id']) ?></p>
                <p><strong>ลูกค้า / Customer:</strong> <?= htmlspecialchars($first['member_firstname'] . ' ' . $first['member_lastname']) ?></p>
                <p><strong>เบอร์โทร / Phone:</strong> <?= htmlspecialchars($first['member_phone']) ?></p>
                <p><strong>วันที่ เวลา ที่ออกใบเสร็จ / Date time of receipt issue:</strong> <?= date("d/m/Y H:i") ?></p>
                <p><strong>ออกโดย / Issuer:</strong> <?= htmlspecialchars($executive_firstname) ?> <?= htmlspecialchars($executive_lastname) ?></p>

            </div>

            <table>
                <thead>
                    <tr>
                        <th>สถานที่จัด Event<br>Event location</th>
                        <th>ประเภทงาน Event<br>Event type</th>
                        <th>ระยะทาง(กิโลเมตร)<br>distance</th>
                        <th>ราคา (บาท/กิโลเมตร)<br>Price</th>
                        <th>จำนวนรถ (คัน)<br>Number of cars</th>
                        <th>เลขทะเบียนรถ<br>Vehicle registration number</th>
                        <th>วันเวลาเดินทาง<br>Travel date and time</th>
                        <th>ราคารวม<br>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $total = 0;
                    $amount_ambulance = $first['event_booking_amount_ambulance'];
                    foreach ($orders as $order):
                        $total += $order['event_booking_price'] / 1.07;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($order['event_booking_location']) . " " . htmlspecialchars($order['event_booking_province']) ?></td>
                            <td><?= htmlspecialchars($order['event_booking_type']) ?></td>
                            <td><?= htmlspecialchars($order['event_booking_distance']) ?></td>
                            <td>5.25</td>
                            <td><?= htmlspecialchars($order['event_booking_amount_ambulance']) ?></td>
                            <td><?= htmlspecialchars($order['ambulance_plate']) ?></td>
                            <td><?= htmlspecialchars($order['event_booking_date']) ?><br><?= htmlspecialchars($order['event_booking_start_time']) ?></td>
                            <td class="text-end"><?= number_format($order['event_booking_distance'] * 5.25 * $amount_ambulance, 2) ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php $vat = ($total * 7) / 100; ?>
                <tfoot>
                    <?php
                    $nurse_price = $order['event_booking_amount_nurse'] * 150;
                    ?>
                    <tr>
                        <td colspan="7" style="text-align:right;">
                            ราคาจำนวนพยาบาลที่เพิ่ม <?= htmlspecialchars($order['event_booking_amount_nurse']) ?> คน/ <?= htmlspecialchars($order['event_booking_amount_ambulance']) ?>คัน
                        </td>
                        <td><?= number_format($nurse_price * $amount_ambulance, 2) ?></td>
                    </tr>
                    <?php
                    $level_price = 0;
                    if ($order['ambulance_level'] == 1) {
                        $level_price = 900;
                    } elseif ($order['ambulance_level'] == 2) {
                        $level_price = 1500;
                    } elseif ($order['ambulance_level'] == 3) {
                        $level_price = 2100;
                    }
                    ?>

                    <tr>
                        <td colspan="7" style="text-align:right;">
                            ราคาระดับรถ Level <?= htmlspecialchars($order['ambulance_level']) ?> / <?= htmlspecialchars($order['event_booking_amount_ambulance']) ?>คัน
                        </td>
                        <td><?= number_format($level_price * $amount_ambulance, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align:right;">
                            <strong>ราคารวม </strong>
                        </td>
                        <td><strong><?= number_format($level_price + $nurse_price + ($order['event_booking_distance'] * 5.25), 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align:right;"><strong>Vat 7%</strong></td>
                        <td><strong><?= number_format($vat, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align:right;"><strong>ยอดชำระทั้งหมด (บาท)</strong></td>
                        <td><strong><?= number_format($total + $vat, 2) ?></strong></td>
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