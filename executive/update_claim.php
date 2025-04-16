<?php
// เชื่อมต่อกับฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intpro";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่ามีข้อมูลถูกส่งมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['claim_approve']) && is_array($_POST['claim_approve'])) {
        $claim_approves = $_POST['claim_approve'];

        // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
        $sql = "UPDATE claim SET claim_approve = ? WHERE claim_id = ?";
        $stmt = $conn->prepare($sql);

        foreach ($claim_approves as $claim_id => $approve) {
            // ตรวจสอบค่าว่าเป็น "อนุมัติแล้ว" หรือ "ถูกปฏิเสธ"
            if ($approve == "อนุมัติแล้ว" || $approve == "ถูกปฏิเสธ") {
                $stmt->bind_param("si", $approve, $claim_id);
                $stmt->execute();
            }
        }

        $stmt->close();
        echo "✅ อัปเดตสถานะการอนุมัติสำเร็จ!";
    } else {
        echo "⚠️ กรุณาเลือกสถานะก่อนกดยืนยัน!";
    }
} else {
    echo "⛔ ไม่อนุญาตให้เข้าถึงหน้านี้โดยตรง!";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// Redirect กลับไปหน้าหลัก
header("Location: approve_claim_page.php");
exit();
?>
