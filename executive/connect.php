<?php
// เชื่อมต่อกับฐานข้อมูล intpro
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intpro"; // ชื่อฐานข้อมูล

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // ถ้าเชื่อมต่อไม่ได้จะแสดงข้อความผิดพลาด
} else {
    echo "เชื่อมต่อฐานข้อมูลสำเร็จ"; // ถ้าเชื่อมต่อได้จะแสดงข้อความนี้
}

// ปิดการเชื่อมต่อ
$conn->close();
?>

