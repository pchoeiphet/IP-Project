<?php
$conn = new mysqli('localhost', 'root', '', 'intpro');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection Failed: ' . $conn->connect_error]));
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $sql = "UPDATE ambulance_booking SET ambulance_booking_status = 'ปฏิบัติเสร็จสิ้นแล้ว' WHERE ambulance_booking_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
}
?>
