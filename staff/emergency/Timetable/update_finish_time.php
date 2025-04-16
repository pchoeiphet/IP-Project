<?php
// กำหนดเขตเวลาให้เป็น Asia/Bangkok เพื่อให้เวลาแสดงผลถูกต้อง
// ป้องกันปัญหาการแสดงเวลาผิดจาก Time Zone ของเซิร์ฟเวอร์
date_default_timezone_set('Asia/Bangkok'); 
session_start();

// เชื่อมต่อฐานข้อมูล MySQL
$con = new mysqli('localhost', 'root', '', 'intpro'); // ใช้ database intpro

// ตรวจสอบการเชื่อมต่อฐานข้อมูล ถ้าล้มเหลวจะแสดงข้อความข้อผิดพลาด
if ($con->connect_error) {
    die(json_encode(['error' => 'Connection Failed: ' . $con->connect_error]));
}

// ตรวจสอบว่ามีข้อมูลที่ส่งมาหรือไม่
if (isset($_POST['id'], $_POST['type'], $_POST['newStartTime'], $_POST['newEndTime'])) {
    $id = $_POST['id']; // ชื่อของ Event ที่ต้องการอัปเดต
    $type = $_POST['type']; // ประเภทของ Event (ambulance หรือ event)
    $newStartTime = $_POST['newStartTime']; // เวลาที่อัปเดตเริ่มต้นใหม่
    $newEndTime = $_POST['newEndTime']; // เวลาที่อัปเดตสิ้นสุดใหม่

    // แยกวันที่และเวลาออกจาก Timestamp ที่ส่งมา
    $startDateTime = explode("T", $newStartTime);
    $startDate = $startDateTime[0]; // แยกเอาวันที่
    $startTime = substr($startDateTime[1], 0, 8); // แยกเอาเวลา (HH:MM:SS)

    $endDateTime = explode("T", $newEndTime);
    $endDate = $endDateTime[0]; // แยกเอาวันที่
    $endTime = substr($endDateTime[1], 0, 8); // แยกเอาเวลา (HH:MM:SS)

    // ตรวจสอบประเภทของ event และเลือกตารางที่ต้องอัปเดต
    if ($type === 'ambulance') {
        $sql = "UPDATE ambulance_booking 
                SET ambulance_booking_start_time = ?, ambulance_booking_finish_time = ? 
                WHERE ambulance_booking_id = ? LIMIT 1";
    } elseif ($type === 'event') {
        $sql = "UPDATE event_booking 
                SET event_booking_start_time = ?, event_booking_finish_time = ? 
                WHERE event_booking_id = ? LIMIT 1";
    } else {
        die(json_encode(['error' => 'Invalid event type']));
    }

    // เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูล
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssi", $startTime, $endTime, $id);

    // ดำเนินการอัปเดตข้อมูลลงในฐานข้อมูล
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Event time updated successfully']);
    } else {
        echo json_encode(['error' => $stmt->error]);
    }

    $stmt->close(); // ปิด Statement
} else {
    // ถ้าไม่มีข้อมูลที่ส่งมา จะแสดงข้อความข้อผิดพลาด
    echo json_encode(['error' => 'Invalid input data']);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$con->close();
?>
