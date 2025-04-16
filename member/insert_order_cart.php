<?php
session_start();
include 'username.php'; // ต้องมี $conn สำหรับเชื่อมฐานข้อมูล

$member_id = $_SESSION['user_id'] ?? null;

// ตรวจสอบว่ามีข้อมูลผู้ใช้และตะกร้าหรือไม่
if (!$member_id || !isset($_SESSION["strProductID"])) {
    echo "❌ ไม่พบข้อมูลผู้ใช้หรือสินค้าในตะกร้า";
    exit();
}

$last_orders = [];
$sum_total = 0; // ใช้เก็บราคารวมของสินค้า
$shipping_cost = 120; // ค่าจัดส่งคงที่

// วนลูปสินค้าในตะกร้าและทำการ insert + อัปเดต stock
for ($i = 0; $i <= (int)$_SESSION["intLine"]; $i++) {
    if (empty($_SESSION["strProductID"][$i])) {
        continue;
    }

    $equipment_id = $_SESSION["strProductID"][$i];
    $quantity = $_SESSION["strQty"][$i];

    // ดึงข้อมูลสินค้า
    $sql = "SELECT * FROM equipment WHERE equipment_id = '$equipment_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        continue; // ไม่เจอสินค้านี้
    }

    // ตรวจสอบว่า stock เพียงพอ
    if ($row['equipment_quantity'] < $quantity) {
        continue; // stock ไม่พอ ข้ามรายการนี้
    }

    $price = $row['equipment_price_per_unit'];
    $total = $price * $quantity;
    $sum_total += $total; // สะสมราคาสินค้าทั้งหมด

    // เพิ่มข้อมูลลงตาราง order_equipment
    $insert = "INSERT INTO order_equipment (
        member_id,
        equipment_id,
        order_equipment_price,
        order_equipment_buy_type,
        order_equipment_type,
        order_equipment_quantity,
        order_equipment_total
    ) VALUES (
        '$member_id',
        '$equipment_id',
        '$price',
        'QR Promptpay',
        'ซื้อ',
        '$quantity',
        '$total'
    )";

    if (mysqli_query($conn, $insert)) {
        // รับ ID ล่าสุดที่ insert เข้า order_equipment
        $order_equipment_id = mysqli_insert_id($conn);

        // ลด stock หลัง insert สำเร็จ
        $update_stock_sql = "UPDATE equipment 
                             SET equipment_quantity = equipment_quantity - ? 
                             WHERE equipment_id = ?";
        $update_stmt = $conn->prepare($update_stock_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("ii", $quantity, $equipment_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // เก็บรายการที่ insert สำเร็จไว้
        $last_orders[] = [
            'order_equipment_id' => $order_equipment_id,
            'quantity' => $quantity,
            'equipment_id' => $equipment_id
        ];
    }
}

// ล้าง session ตะกร้า
unset($_SESSION["strProductID"]);
unset($_SESSION["strQty"]);
unset($_SESSION["intLine"]);

// คำนวณราคารวมทั้งหมด + ค่าจัดส่ง
$final_total = $sum_total + $shipping_cost;

// สร้าง query string สำหรับ redirect ไป QRpayment_order.php
$queryString = '';
if (!empty($last_orders)) {
    $params = [];
    foreach ($last_orders as $order) {
        $params[] = "order_equipment_id[]=" . $order['order_equipment_id'];
        $params[] = "quantity[]=" . $order['quantity'];
        $params[] = "equipment_id[]=" . $order['equipment_id'];
    }
    $queryString = '&' . implode('&', $params);
}

// ใช้ยอดรวมของสินค้าที่บันทึกไว้ใน order_equipment
$total = $sum_total; // <<<<< ตรงนี้แหละที่คุณต้องการ

$vat = $total * 0.07;
$total_with_vat = $total + $vat + $shipping_cost;

header("Location: QRpayment_order.php?price_total=$total_with_vat$queryString");
exit();

?>