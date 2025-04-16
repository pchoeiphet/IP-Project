<?php
// เชื่อมต่อฐานข้อมูลจากไฟล์ con_repair.php และเริ่ม session
include 'con_repair.php';
session_start();

// รับวันที่ปัจจุบันในรูปแบบ ปี-เดือน-วัน
$date = date('Y-m-d');

// รับข้อมูลที่ส่งมาจากฟอร์มผ่าน POST และตรวจสอบว่ามีค่าหรือไม่
$ambulance_id = $_POST['car_number'] ?? '';
$category = $_POST['category'] ?? '';
$device = $_POST['device'] ?? '';
$reason = $_POST['reason'] ?? '';

// ดึงรหัสผู้แจ้งซ่อมจาก session (ผู้ใช้งานที่เข้าสู่ระบบ)
$reporter = $_SESSION['user_id'];

// กำหนดสถานะเริ่มต้นของการแจ้งซ่อมเป็น "รอดำเนินการ"
$re_status = 'รอดำเนินการ';

// เพิ่มข้อมูลลงในตาราง repair
$stmt = $conn->prepare("INSERT INTO repair (ambulance_id, repair_staff_id, repair_date, repair_type, repair_repairing, repair_reason, repair_status ) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssss", $ambulance_id, $reporter, $date, $category , $device, $reason, $re_status);
$stmt->execute();

// ตรวจสอบว่ารถคันนี้มีการแจ้งซ่อมที่ยังไม่เสร็จหรือไม่
$sqlCheck = "SELECT COUNT(*) as count FROM repair WHERE ambulance_id = ? AND repair_status IN ('รอดำเนินการ', 'กำลังดำเนินการ')";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $ambulance_id);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$row = $result->fetch_assoc();  // ดึงจำนวนรายการแจ้งซ่อมที่ยังดำเนินการอยู่
$stmtCheck->close();

// ถ้าจำนวนมากกว่า 0 แสดงว่ารถยังไม่พร้อมใช้งาน
$ambu_status = ($row['count'] > 0) ? 'ไม่พร้อม' : 'พร้อม';

// อัปเดตสถานะของรถพยาบาลในตาราง ambulance ตามผลลัพธ์ที่ได้
$sqlUpdate = "UPDATE ambulance SET ambulance_status = ? WHERE ambulance_id = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("si", $ambu_status, $ambulance_id);
$stmtUpdate->execute();
$stmtUpdate->close();

// ส่งผู้ใช้กลับไปยังหน้าซ่อม (repair.php) หลังจากบันทึกข้อมูลเสร็จ
header("Location: repair.php");

// ปิดการเชื่อมต่อ statement และ database
$stmt->close();
$conn->close();
?>