<?php
session_start();
include 'username.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

$sql = "SELECT * FROM order_equipment 
        JOIN equipment ON order_equipment.equipment_id = equipment.equipment_id
        WHERE member_id = ? AND order_equipment_status = 'ชำระเงินเสร็จสิ้น'
        ORDER BY order_equipment.order_equipment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$sql_exec = "SELECT executive_id, executive_firstname, executive_lastname FROM executive ORDER BY RAND() LIMIT 1";
$stmt_exec = $conn->prepare($sql_exec);
$stmt_exec->execute();
$result_exec = $stmt_exec->get_result();
$executive = $result_exec->fetch_assoc();

// ตรวจสอบข้อมูลผู้บริหาร
if ($executive) {
    $executive_id = $executive['executive_id'];
    $executive_firstname = $executive['executive_firstname'];
    $executive_lastname = $executive['executive_lastname'];
} else {
    $executive_id = null;  // หรือกำหนดเป็นค่าเริ่มต้น
    $executive_firstname = "ไม่พบข้อมูลผู้บริหาร";
    $executive_lastname = "";
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติคำสั่งซื้อ</title>
    <link rel="stylesheet" href="css/style_history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="top-navbar">
        <nav class="nav-links">
            <div><a href="order_emergency.php">ชำระเงินเคสฉุกเฉิน</a></div>
            <div><a href="contact.html">ติดต่อเรา</a></div>
            <div class="dropdown">
                <img src="image/user.png" alt="Logo" class="nav-logo">
                <div class="dropdown-menu">
                    <a href="profile.html">โปรไฟล์</a>
                    <a href="history.php">ประวัติคำสั่งซื้อ</a>
                    <a href="history_ambulance_booking.php">ประวัติการจองรถ</a>
                    <a href="claim.php">เคลมสินค้า</a>
                    <a href="../logout.php">ออกจากระบบ</a>
                </div>
            </div>
            <a href="index.php">
                <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
            </a>
        </nav>
    </div>

    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
            <div><a href="reservation_car.php">จองคิวรถ</a></div>
            <a href="index.php">
                <img src="image/Logo.png" alt="Logo" class="nav-logo1">
            </a>
            <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
        </nav>

        <div class="cart-icon">
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>

    <div class="content container mt-5">
        <h2 class="mb-4">ประวัติคำสั่งซื้อที่ชำระเงินเสร็จสิ้น</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php
            $currentDate = "";
            $index = 0;
            $order_ids = [];
            $total = 0;
            $vat = 0;  // VAT เริ่มต้นที่ 0

            while ($row = $result->fetch_assoc()):
                $orderDate = date("d/m/Y H:i:s", strtotime($row['order_equipment_date']));

                if ($orderDate != $currentDate):
                    if ($currentDate != ""):
                        // คำนวณ VAT สำหรับรายการทั้งหมดในวันนั้น
                        $vat = ($total * 7) / 100;  // 7% VAT

                        // แสดงค่าจัดส่งและราคารวม
                        echo '<tr><td colspan="5" style="text-align:right;"><strong>ราคารวม (บาท)</strong></td><td><strong>' . number_format($total + 120, 2) . '</strong></td><td></td></tr>';
                        echo '<tr><td colspan="5" style="text-align:right;"><strong>Vat 7%</strong></td><td><strong>' . number_format($vat, 2) . '</strong></td><td></td></tr>';
                        echo '<tr><td colspan="5" style="text-align:right;"><strong>ค่าจัดส่งสินค้า (บาท)</strong></td><td><strong>120</strong></td><td></td></tr>';
                        echo '<tr><td colspan="5" style="text-align:right;"><strong>ราคารวมทั้งหมด (รวม VAT และค่าจัดส่ง)</strong></td><td><strong>' . number_format($total + $vat + 120, 2) . '</strong></td><td></td></tr>';
                        echo '</tbody></table>';

                        $order_ids_str = implode(',', $order_ids);
                        echo '<div class="print-button-wrapper">';
                        echo '<a href="print_bill.php?order_ids=' . $order_ids_str . '&executive_id=' . $executive_id . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
                        echo '</div>';
                        echo '</div><br>';

                        // รีเซ็ตตัวแปรเพื่อเตรียมพร้อมสำหรับการแสดงผลในรอบถัดไป
                        $order_ids = [];
                        $total = 0;
                    endif;

                    $index++;
                    echo '<div id="print-section-' . $index . '" class="mb-4">';
                    echo "<h4 class='mt-4 mb-3'>วันที่สั่งซื้อ: <strong>$orderDate</strong></h4>";
                    echo '<div class="table-responsive">';
                    echo '<table class="custom-table">';
                    echo '<thead>
                    <tr>
                        <th></th>
                        <th>ชื่อสินค้า</th>
                        <th>ประเภทการชำระเงิน</th>
                        <th>ประเภทการสั่งซื้อ</th>
                        <th>จำนวน</th>
                        <th>ราคารวม (บาท)</th>
                        <th>สถานะคำสั่งซื้อ</th>
                    </tr>
                  </thead><tbody>';

                    $currentDate = $orderDate;
                endif;

                // คำนวณรวมยอดทั้งหมด
                $order_ids[] = $row['order_equipment_id'];
                $total += $row['order_equipment_total'];
            ?>
                <tr>
                    <td><img src="image/<?= htmlspecialchars($row['equipment_image']) ?>" width="50"></td>
                    <td><?= htmlspecialchars($row['equipment_name']) ?></td>
                    <td><?= htmlspecialchars($row['order_equipment_buy_type']) ?></td>
                    <td><?= htmlspecialchars($row['order_equipment_type']) ?></td>
                    <td><?= htmlspecialchars($row['order_equipment_quantity']) ?></td>
                    <td><?= htmlspecialchars($row['order_equipment_total']) ?></td>
                    <td><?= htmlspecialchars($row['order_equipment_status']) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php
            // คำนวณ VAT และแสดงผลในกรณีที่มีรายการคำสั่งซื้อสุดท้าย
            $vat = ($total * 7) / 100;  // 7% VAT

            echo '<tr><td colspan="5" style="text-align:right;"><strong>ค่าจัดส่งสินค้า</strong></td><td><strong>120 บาท</strong></td><td></td></tr>';
            echo '<tr><td colspan="5" style="text-align:right;"><strong>Vat 7%</strong></td><td><strong>' . number_format($vat, 2) . ' บาท</strong></td><td></td></tr>';
            echo '<tr><td colspan="5" style="text-align:right;"><strong>ราคารวม</strong></td><td><strong>' . number_format($total + $vat + 120, 2) . ' บาท</strong></td><td></td></tr>';
            echo '</table>';

            $order_ids_str = implode(',', $order_ids);
            echo '<div class="print-button-wrapper">';
            echo '<a href="print_bill.php?order_ids=' . $order_ids_str . '&executive_id=' . $executive_id . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
            echo '</div>';
            echo '</div>';
            ?>
        <?php else: ?>
            <div class="alert alert-warning">ไม่พบรายการสั่งซื้อที่ชำระเงินเสร็จสิ้น</div>
        <?php endif; ?>

    </div>
</body>

</html>