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
$_SESSION['user_id'];
//------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = file_get_contents("php://input");
    $user = json_decode($data, true);

    if (!isset(
        $user["member_id"],
        $user["equipment_id"],
        $user["order_equipment_type"],
        $user["order_equipment_price"],
        $user["order_equipment_quantity"],
        $user["order_equipment_total"],
        $user["order_equipment_buy_type"],
        $user["order_equipment_months"]
    )) {
        die("Missing required fields");
    }

    $member_id = $user["member_id"];
    $equipment_id = $user["equipment_id"];
    $order_equipment_type  = $user["order_equipment_type"];
    $order_equipment_price = $user["order_equipment_price"];
    $order_equipment_quantity = $user["order_equipment_quantity"];
    $order_equipment_total = $user["order_equipment_total"];
    $order_equipment_months =  $user["order_equipment_months"];
    $order_equipment_buy_type = $user["order_equipment_buy_type"];

    $order_time = date("Y-m-d H:i:s");

    $sql = "INSERT INTO `order_equipment` 
            (member_id, equipment_id, order_equipment_type, order_equipment_price, 
             order_equipment_quantity, order_equipment_total, order_equipment_months, 
             order_equipment_buy_type, order_equipment_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param(
        "iissiiiss",
        $member_id,
        $equipment_id,
        $order_equipment_type,
        $order_equipment_price,
        $order_equipment_quantity,
        $order_equipment_total,
        $order_equipment_months,
        $order_equipment_buy_type,
        $order_time
    );

    if ($stmt->execute()) {
        $order_equipment_id = mysqli_insert_id($conn);

        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "order_equipment_id" => $order_equipment_id
        ]);


        // ✅ ลด stock ตามจำนวนที่สั่ง
        $update_stock_sql = "UPDATE equipment 
                             SET equipment_quantity = equipment_quantity - ? 
                             WHERE equipment_id = ?";
        $update_stmt = $conn->prepare($update_stock_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("ii", $order_equipment_quantity, $equipment_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            echo "Error updating stock: " . $conn->error;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method";
}
