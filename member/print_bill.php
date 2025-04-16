<?php
session_start();
include 'username.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

if (!isset($_GET['order_ids'])) {
    echo "ไม่พบรหัสคำสั่งซื้อ";
    exit();
}



$order_ids = explode(',', $_GET['order_ids']);
$order_ids = array_map('intval', $order_ids);
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));

$sql = "SELECT 
            order_equipment.order_equipment_id, 
            order_equipment.order_equipment_quantity, 
            order_equipment.order_equipment_total, 
            equipment.equipment_name,
            member.member_firstname,
            member.member_lastname, 
            member.member_address,
            member.member_phone
        FROM order_equipment
        JOIN equipment ON order_equipment.equipment_id = equipment.equipment_id
        JOIN member ON order_equipment.member_id = member.member_id
        WHERE order_equipment.member_id = ? AND order_equipment.order_equipment_id IN ($placeholders)";

$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($order_ids) + 1);
$params = array_merge([$member_id], $order_ids);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$orders = $result->fetch_all(MYSQLI_ASSOC);
date_default_timezone_set("Asia/Bangkok");

// รับค่า executive_id จาก URL
$executive_id = isset($_GET['executive_id']) ? $_GET['executive_id'] : null;

if ($executive_id) {
    // ดึงข้อมูลผู้บริหารจากฐานข้อมูล
    $sql_exec = "SELECT executive_firstname, executive_lastname FROM executive WHERE executive_id = ?";
    $stmt_exec = $conn->prepare($sql_exec);
    $stmt_exec->bind_param("i", $executive_id);
    $stmt_exec->execute();
    $result_exec = $stmt_exec->get_result();

    // ถ้าหากข้อมูลผู้บริหารถูกดึงมา
    if ($executive = $result_exec->fetch_assoc()) {
        $executive_firstname = $executive['executive_firstname'];
        $executive_lastname = $executive['executive_lastname'];
    } else {
        // ถ้าไม่พบข้อมูลของผู้บริหาร
        $executive_firstname = "ไม่พบข้อมูลผู้บริหาร";
        $executive_lastname = "";
    }
} else {
    // ถ้า executive_id ไม่ถูกส่งมา
    $executive_firstname = "ไม่พบข้อมูลผู้บริหาร";
    $executive_lastname = "";
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
            <h2>ใบเสร็จรับเงิน / Receipt</h2>
            <p style="margin-top: -10px;">(ต้นฉบับ / Original)</p>
        </div>

        <!-- แสดงชื่อของลูกค้าและวันที่แยกจากตาราง -->
        <?php if (count($orders) > 0): ?>
            <?php $first = $orders[0]; ?>
            <div class="detail">
                <p><strong>เลขที่ใบเสร็จ / Receipt No.:</strong> 0021<?= htmlspecialchars($first['order_equipment_id']) ?></p>
                <p><strong>ลูกค้า / Customer:</strong> <?= htmlspecialchars($first['member_firstname'] . ' ' . $first['member_lastname']) ?></p>
                <p><strong>ที่อยู่ / Address:</strong> <?= htmlspecialchars($first['member_address']) ?></p>
                <p><strong>เบอร์โทร / Phone:</strong> <?= htmlspecialchars($first['member_phone']) ?></p>
                <p><strong>วันที่ เวลา ที่ออกใบเสร็จ / Date time of receipt issue:</strong> <?= date("d/m/Y H:i") ?></p>
                <p><strong>ออกโดย / Issuer:</strong> <?= htmlspecialchars($executive_firstname) ?> <?= htmlspecialchars($executive_lastname) ?></p>

            </div>

            <table>
                <thead>
                    <tr>
                        <th>ลำดับ<br>No.</th>
                        <th>ชื่อสินค้า<br>Equipment Name</th>
                        <th>จำนวน<br>Quantity</th>
                        <th>ราคาต่อหน่วย<br>Unit Price</th>
                        <th>ราคารวม (บาท)<br>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $total = 0;
                    foreach ($orders as $order):
                        $total += $order['order_equipment_total'];
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($order['equipment_name']) ?></td>
                            <td><?= htmlspecialchars($order['order_equipment_quantity']) ?></td>
                            <td><?= number_format($order['order_equipment_total'] / $order['order_equipment_quantity'], 2) ?></td>
                            <td><?= number_format($order['order_equipment_total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <?php $vat = ($total * 7) / 100; ?>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>ราคารวม</strong></td>
                        <td><strong><?= number_format($total, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>Vat 7%</strong></td>
                        <td><strong><?= number_format($vat, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>ค่าจัดส่งสินค้า</strong></td>
                        <td><strong>120</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>ยอดชำระทั้งหมด</strong></td>
                        <td><strong><?= number_format($total + 120 + $vat, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p><strong>ไม่พบข้อมูลใบเสร็จ</strong></p>
        <?php endif; ?>

        <div class="signature-section">
            <div class="signature">
                <div class="signature-line"></div>
                ผู้รับสินค้า / Receiver
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