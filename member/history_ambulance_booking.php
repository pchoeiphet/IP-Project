<?php
session_start();
include 'username.php';

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

$sql = "SELECT 
            ambulance_booking.ambulance_booking_id,
            ambulance_booking.ambulance_booking_location,
            ambulance_booking.ambulance_booking_hospital_waypoint,
            ambulance_booking.ambulance_booking_province,
            ambulance_booking.ambulance_booking_date,
            ambulance_booking.ambulance_booking_start_time,
            ambulance_booking.ambulance_booking_finish_time,
            ambulance_booking.ambulance_booking_price,
            ambulance_booking.ambulance_booking_status,
            ambulance_booking.ambulance_booking_distance,
            member.member_firstname,
            member.member_lastname,
            member.member_phone,
            ambulance.ambulance_plate
        FROM ambulance_booking
        JOIN member ON ambulance_booking.member_id = member.member_id
        JOIN ambulance ON ambulance_booking.ambulance_id = ambulance.ambulance_id
        WHERE ambulance_booking.member_id = ?
        ORDER BY ambulance_booking.ambulance_booking_date DESC";

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
    <title>ประวัติการจองรถสำหรับรับส่งผู้ป่วย</title>
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

    <div class="custom-dropdown">
        <select class="dropdown-select" onchange="window.location.href=this.value;">
            <option value="" selected hidden>เลือกประเภทการจอง</option>
            <option value="history_ambulance_booking.php">จองรถสำหรับผู้ป่วย</option>
            <option value="history_event_booking.php">จองรถสำหรับงาน Event</option>
        </select>
    </div>

    <div class="content container mt-5">
        <h3 class="mb-4">ประวัติการจองรถสำหรับรับส่งผู้ป่วย</h3>

        <?php if ($result->num_rows > 0): ?>
            <?php
            $currentDate = "";
            $index = 0;
            $booking_ids = [];
            $total = 0;

            while ($row = $result->fetch_assoc()):
                $bookingDate = date("d/m/Y", strtotime($row['ambulance_booking_date']));

                // ถ้าเปลี่ยนกลุ่ม
                if ($bookingDate != $currentDate):
                    if ($currentDate != "") {
                        // แสดงค่าบริการ + ราคารวมกลุ่มก่อนหน้า
                        echo '</tbody></table>';
                        $booking_ids_str = implode(',', $booking_ids);
                        echo '<div class="print-button-wrapper">';
                        echo '<a href="print_bill_ambulance_booking.php?booking_ids=' . $booking_ids_str . '&executive_id=' . $executive_id . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
                        echo '</div>';
                        echo '</div><br>'; // ปิดกล่อง
                        $booking_ids = [];
                        $total = 0;
                    }

                    $index++;
                    echo '<div id="print-section-' . $index . '" class="mb-4">';
                    echo "<h4 class='mt-4 mb-3'>วันที่จอง: <strong>$bookingDate</strong></h4>";
                    echo '<div class="table-responsive">';
                    echo '<table class="custom-table">';
                    echo '<thead>
                        <tr>
                            <th>เส้นทาง</th>
                            <th>ทะเบียนรถ</th>
                            <th>วันเวลาจอง</th>
                            <th>ระยะทาง (กิโลเมตร)</th>
                            <th>สถานะการจอง</th>
                            <th>ค่าบริการ (บาท)</th>
                        </tr>
                      </thead><tbody>';

                    $currentDate = $bookingDate;
                endif;

                $booking_ids[] = $row['ambulance_booking_id'];
                $total += $row['ambulance_booking_price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['ambulance_booking_location']) . " " . htmlspecialchars($row['ambulance_booking_province'])  ?> <strong>ไป</strong> <?= htmlspecialchars($row['ambulance_booking_hospital_waypoint']) ?></td>
                    <td><?= htmlspecialchars($row['ambulance_plate']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['ambulance_booking_date']) ?><br>
                        <?= htmlspecialchars($row['ambulance_booking_start_time']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['ambulance_booking_distance']) ?></td>
                    <td><?= htmlspecialchars($row['ambulance_booking_status']) ?></td>
                    <td class="text-end"><?= number_format($row['ambulance_booking_price'], 2) ?></td>

                </tr>
            <?php endwhile; ?>

            </tbody>
            </table>
            <?php
            $booking_ids_str = implode(',', $booking_ids);
            echo '<div class="print-button-wrapper">';
            echo '<a href="print_bill_ambulance_booking.php?booking_ids=' . $booking_ids_str . '&executive_id=' . $executive_id . '" target="_blank" class="btn btn-primary">พิมพ์ใบเสร็จ</a>';
            echo '</div>';
            echo '</div>';
            ?>
        <?php else: ?>
            <div class="alert alert-warning">ไม่พบรายการจองรถสำหรับรับส่งผู้ป่วยที่ชำระเงินเสร็จสิ้น</div>
        <?php endif; ?>
    </div>


</body>

</html>