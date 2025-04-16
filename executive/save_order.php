<?php
include 'username.php'; // เชื่อมต่อฐานข้อมูล

header("Content-Type: application/json");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die(json_encode(["status" => "error", "message" => "เชื่อมต่อฐานข้อมูลไม่ได้: " . mysqli_connect_error()]));
}

// รับและตรวจสอบ JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["orders"]) || !is_array($data["orders"])) {
    die(json_encode(["status" => "error", "message" => "ข้อมูลไม่ถูกต้อง"]));
}

$success = 0;
$errors = [];

foreach ($data["orders"] as $order) {
    $order_id = intval($order["order_equipment_id"] ?? 0);
    $status = strtolower(trim($order["approval_status"] ?? ""));

    // ตรวจสอบค่าอนุมัติ
    $status_map = ["approve" => "อนุมัติ", "reject" => "ไม่อนุมัติ"];
    if (!isset($status_map[$status]) || $order_id <= 0) {
        $errors[] = "ข้อมูลไม่ถูกต้อง (order_equipment_id: $order_id)";
        continue;
    }

    // อัปเดตฐานข้อมูล
    $stmt = $conn->prepare("UPDATE order_equipment SET order_equipment_approve = ? WHERE order_equipment_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status_map[$status], $order_id);
        $stmt->execute() ? $success++ : $errors[] = "อัปเดตล้มเหลว (order_equipment_id: $order_id)";
        $stmt->close();
    } else {
        $errors[] = "SQL Error: " . $conn->error;
    }
}

$conn->close();

// ส่งผลลัพธ์กลับ
echo json_encode([
    "status" => empty($errors) ? "success" : "partial",
    "message" => empty($errors) ? "อัปเดตสำเร็จ $success รายการ" : "บางรายการไม่สำเร็จ",
    "errors" => $errors
]);
?>
