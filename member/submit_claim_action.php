<?php
//-----------Session and Login-------------
session_start();
include 'username.php';

// ถ้าไม่ได้ล็อกอิน ให้ redirect กลับไปหน้า login
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// เรียก member_id จาก session มาใช้ :
// $_SESSION['user_id'];
//------------------------------------------

//รับค่าจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //ตรวจว่าได้รับค่ามามั้ย print_r($_POST);
    // ตรวจสอบว่าคีย์ 'action' มีอยู่ใน $_POST หรือไม่
    if (isset($_POST['action'])) {
        $action = $_POST['action']; // รับค่า "เคลม","ซ่อม" หรือ "ต่ออายุการใช้งาน"
    } else {
        echo "Action is not set.";
        exit; // หยุดการทำงานหากไม่มี action
    }
 // รับค่า "เคลม" หรือ "ต่ออายุการใช้งาน"
    $reason = htmlspecialchars($_POST['reason']); // เหตุผลจากฟอร์ม
    $equipment_id = $_POST['equipment']; // สมมติว่าเรามี id ของอุปกรณ์การแพทย์จาก URL หรือ session
    $member_id = $_SESSION['user_id']; // สมมติ
    $executive_id = 1; // สมมติ
    // $member_id = $_SESSION['member_id']; // ดึง ID สมาชิกจาก session หลัง loginรอทำlogin
    $approve_status = "รออนุมัติ"; // สถานะการอนุมัติของผู้บริหาร

    // ตรวจสอบว่าเป็นเคลมหรือต่ออายุ และบันทึกลงฐานข้อมูล
if ($action == "เคลม") {
    $sql = "INSERT INTO claim (member_id, equipment_id, executive_id, claim_approve, claim_detail, claim_date, claim_type) VALUES ('$member_id', '$equipment_id', '$executive_id', '$approve_status', '$reason', NOW(), '$action')";
    } else if ($action == "ต่ออายุการใช้งาน") {
    $sql = "INSERT INTO claim (member_id, equipment_id, executive_id, claim_approve, claim_detail, claim_date, claim_type) VALUES ('$member_id', '$equipment_id', '$executive_id', '$approve_status', '$reason', NOW(), '$action')";
    } else if ($action == "ซ่อม") {
    $sql = "INSERT INTO claim (member_id, equipment_id, executive_id, claim_approve, claim_detail, claim_date, claim_type) VALUES ('$member_id', '$equipment_id', '$executive_id', '$approve_status', '$reason', NOW(), '$action')";
}


require("username.php");

if ($conn->query($sql) === TRUE) {
    header("Location:success_claim.html");
    exit(0);
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
}
?>
