<?php
// เชื่อมต่อฐานข้อมูลจากไฟล์ con_repair.php และเริ่ม session
include '../../../username.php';
session_start();

$current_date = date('Y-m-d'); // วันที่ปัจจุบัน
$registration_car = $_POST['registration_car'] ?? ''; // หมายเลขทะเบียนรถ
$id_staff = $_SESSION['user_id']; // รหัสพนักงานซ่อม (ค่าเริ่มต้น)

// วนลูปข้อมูลที่ส่งมาจากฟอร์มแบบ array ชื่อ 'status'
// โดย $section_title คือชื่ออุปกรณ์หรือส่วนที่แจ้งซ่อม
// และ $status คือสถานะของอุปกรณ์นั้น
foreach ($_POST["status"] as $section_title => $status) {

     // รับเหตุผลการซ่อมของแต่ละรายการจากฟอร์ม ถ้าไม่มีให้เป็นค่าว่าง
    $repair_reason = $_POST["reason"][$section_title] ?? '';

    // ตรวจสอบว่าอุปกรณ์ที่แจ้งซ่อมเป็นอุปกรณ์ทางการแพทย์หรือไม่
    $type = in_array($section_title, ['เครื่องAED', 'เครื่องช่วยหายใจ', 'ถังออกซิเจน', 'เครื่องวัดความดัน', 'เครื่องวัดชีพจร', 'เตียงพยาบาล', 'เปลสนาม', 'อุปกรณ์ปฐมพยาบาล', 'อุปกรณ์การดาม']) ? 'อุปกรณ์ทางการแพทย์' : 'รถพยาบาล';
    
    // ถ้าสถานะคือ "ไม่พร้อม" ให้แปลงเป็น "รอดำเนินการ" (สำหรับบันทึกลงฐานข้อมูล)
    if ($status == "ไม่พร้อม"){
        $status = "รอดำเนินการ";
    }
     // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูลการแจ้งซ่อมลงในตาราง repair
    $stmt3 = $conn->prepare("INSERT INTO repair (ambulance_id, repair_staff_id, repair_date, repair_type, repair_repairing, repair_reason, repair_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt3->bind_param("iisssss", $registration_car, $id_staff, $current_date, $type, $section_title, $repair_reason, $status);
    $stmt3->execute();
    $stmt3->close();
}
// ตรวจสอบว่ารถพยาบาลคันนี้มีการแจ้งซ่อมที่ยังไม่เสร็จอยู่หรือไม่
$sqlCheck = "SELECT COUNT(*) as count FROM repair WHERE ambulance_id = ? AND repair_status IN ('รอดำเนินการ', 'กำลังดำเนินการ')";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $registration_car);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$row = $result->fetch_assoc();
$stmtCheck->close();

// ถ้ามีรายการซ่อมที่ยังไม่เสร็จ => สถานะของรถ = "ไม่พร้อม"
// ถ้าไม่มี => รถ "พร้อม"
$ambu_status = ($row['count'] > 0) ? 'ไม่พร้อม' : 'พร้อม';

// อัปเดตสถานะของรถพยาบาลในตาราง ambulance
$sqlUpdate = "UPDATE ambulance SET ambulance_status = ? WHERE ambulance_id = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("si", $ambu_status, $registration_car);
$stmtUpdate->execute();
$stmtUpdate->close();

// เปลี่ยนเส้นทางไปยังหน้าบันทึกสำเร็จ
header("Location: car_report_success.php");
exit();

$conn->close();