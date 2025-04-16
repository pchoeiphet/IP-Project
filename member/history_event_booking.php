<?php
session_start();
include 'username.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

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
            event_booking_amount_ambulance,
            member.member_firstname,
            member.member_lastname,
            member.member_phone,
            ambulance.ambulance_plate
        FROM event_booking
        JOIN member ON event_booking.member_id = member.member_id
        JOIN ambulance ON event_booking.ambulance_id = ambulance.ambulance_id
        WHERE event_booking.member_id = ?
        ORDER BY event_booking.event_booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$sql_exec = "SELECT executive_id, executive_firstname, executive_lastname FROM executive ORDER BY RAND() LIMIT 1";
$stmt_exec = $conn->prepare($sql_exec);
$stmt_exec->execute();
$result_exec = $stmt_exec->get_result();
$executive = $result_exec->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติการจองรถสำหรับ Event</title>
    <link rel="stylesheet" href="css/style_history_booking.css">
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
                    <a href="logout.html">ออกจากระบบ</a>
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

    <div class="custom-dropdown">
        <select class="dropdown-select" onchange="window.location.href=this.value;">
            <option value="" selected hidden>เลือกประเภทการจอง</option>
            <option value="history_ambulance_booking.php">จองรถสำหรับผู้ป่วย</option>
            <option value="history_event_booking.php">จองรถสำหรับงาน Event</option>
        </select>
    </div>

    <div class="content container mt-5">
        <h3 class="mb-4">ประวัติการจองรถสำหรับรับงาน Event</h3>
        <?php if ($result->num_rows > 0): ?>
            <?php
            $currentDate = "";
            $index = 0;
            $event_ids = [];
            $total = 0;

            while ($row = $result->fetch_assoc()):
                $eventDate = date("d/m/Y", strtotime($row['event_booking_date']));

                // ถ้าเปลี่ยนกลุ่ม
                if ($eventDate != $currentDate):
                    if ($currentDate != "") {
                        // แสดงค่าบริการ + ราคารวมกลุ่มก่อนหน้า
                        echo '</tbody></table>';
                        $event_ids_str = implode(',', $event_ids);
                        echo '<div class="print-button-wrapper">';
                        echo '<a href="print_bill_event_booking.php?event_ids=' . $event_ids_str . '&executive_id=' . $executive['executive_id'] . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
                        echo '</div>';
                        echo '</div><br>'; // ปิดกล่อง
                        $event_ids = [];
                        $total = 0;
                    }

                    $index++;
                    echo '<div id="print-section-' . $index . '" class="mb-4">';
                    echo "<h4 class='mt-4 mb-3'>วันที่จอง: <strong>$eventDate</strong></h4>";
                    echo '<div class="table-responsive">';
                    echo '<table class="custom-table">';
                    echo '<thead>
                        <tr>
                            <th>ประเภทงาน</th>
                            <th>สถานที่</th>
                            <th>ทะเบียนรถ</th>
                            <th>วันเวลางาน</th>
                            <th>ระยะทาง (กิโลเมตร)</th>
                            <th>เบอร์โทร</th>
                            <th>ค่าบริการ (บาท)</th>
                        </tr>
                      </thead><tbody>';

                    $currentDate = $eventDate;
                endif;

                $event_ids[] = $row['event_booking_id'];
                $total += $row['event_booking_price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['event_booking_type']) ?></td>
                    <td><?= htmlspecialchars($row['event_booking_location']) . " " . htmlspecialchars($row['event_booking_province']) ?></td>
                    <td><?= htmlspecialchars($row['ambulance_plate']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['event_booking_date']) ?><br>
                        <?= htmlspecialchars($row['event_booking_start_time']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['event_booking_distance']) ?></td>
                    <td><?= htmlspecialchars($row['member_phone']) ?></td>
                    <td class="text-end"><?= number_format($row['event_booking_price'], 2) ?></td>
                </tr>
            <?php endwhile; ?>

            </tbody>
            </table>
            <?php
            $event_ids_str = implode(',', $event_ids);
            echo '<div class="print-button-wrapper">';
            echo '<a href="print_bill_event_booking.php?event_ids=' . $event_ids_str . '&executive_id=' . $executive['executive_id'] . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
            ?>
        <?php else: ?>
            <div class="alert alert-warning">ไม่พบรายการจองรถสำหรับรับงาน Event</div>
        <?php endif; ?>
    </div>

</body>

</html>