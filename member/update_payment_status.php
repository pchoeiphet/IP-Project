<?php
include('username.php'); // เชื่อมต่อฐานข้อมูล

if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // อัปเดตสถานะการชำระเงิน
    $sql = "UPDATE order_emergency_case SET order_emergency_case_status = 'ชำระเงินแล้ว' WHERE order_emergency_case_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo "success"; // ส่งผลลัพธ์กลับไปยัง JavaScript
    } else {
        echo "error";
    }

    $stmt->close();
}
?>
