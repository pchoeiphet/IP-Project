<?php
include 'username.php';

$order_total = $_GET['price_total'] ?? 0;

// รองรับทั้งเดี่ยวและ array
$order_equipment_id = $_GET['order_equipment_id'] ?? [];
$quantities = $_GET['quantity'] ?? [];
$equipment_id = $_GET['equipment_id'] ?? [];

if (!is_array($order_equipment_id)) {
    $order_equipment_id = [$order_equipment_id];
    $quantities = [$_GET['quantity'] ?? 1];
    $equipment_id = [$_GET['id'] ?? 0];
}

// หากมีการ POST เช่น กดยืนยัน หรือ ยกเลิก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $equipment_id = isset($data['equipment_id']) ? (int)$data['equipment_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    $action = isset($data['action']) ? $data['action'] : 'confirm';
    $order_id = isset($data['order_equipment_id']) ? (int)$data['order_equipment_id'] : 0;

    if ($action === 'confirm') {
        // ไม่ต้องลด stock ตรงนี้แล้ว เพราะทำใน insert_order.php แล้ว
        echo json_encode(["status" => "success"]);
        exit();
    } elseif ($action === 'cancel') {
        $sql = "UPDATE equipment SET equipment_quantity = equipment_quantity + ? WHERE equipment_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $quantity, $equipment_id);
        $stmt->execute();

        $delete_order = $conn->prepare("DELETE FROM order_equipment WHERE order_equipment_id = ?");
        $delete_order->bind_param("i", $order_id);
        $delete_order->execute();

        echo json_encode(["status" => "cancelled"]);
        exit();
    }
}

// ตรวจสอบ timeout 10 นาที
foreach ($order_equipment_id as $index => $order_id) {
    // ตรวจสอบ timeout
    $stmt = $conn->prepare("SELECT order_equipment_date, order_equipment_quantity, equipment_id FROM order_equipment WHERE order_equipment_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        $order_time = strtotime($order['order_equipment_date']);
        $now = time();
        $diff = $now - $order_time;

        if ($diff > 600) {
            // คืนของ + ลบออเดอร์
            $qty = $order['order_equipment_quantity'];
            $eid = $order['equipment_id'];

            $return_sql = $conn->prepare("UPDATE equipment SET equipment_quantity = equipment_quantity + ? WHERE equipment_id = ?");
            $return_sql->bind_param("ii", $qty, $eid);
            $return_sql->execute();

            $delete_order = $conn->prepare("DELETE FROM order_equipment WHERE order_equipment_id = ?");
            $delete_order->bind_param("i", $order_id);
            $delete_order->execute();

            echo "<script>alert('หมดเวลาการชำระเงิน กรุณาทำรายการใหม่'); window.location.href = 'shopping.php';</script>";
            exit();
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_QR_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>ชำระเงิน</title>
</head>

<body>
    <div class="top-navbar">
        <nav class="nav-links">
            <div><a href="order_emergency.php">ชำระเงินเคสฉุกเฉิน</a></div>
            <div><a href="contact.html">ติดต่อเรา</a></div>
            <div class="dropdown">
                <img src="image/user.png" alt="Logo" class="nav-logo">
                <div class="dropdown-menu">
                    <a href="profile.html">โปรไฟล์</a>
                    <a href="history.php">ประวัติคำสั่งซื้อ</a>
                    <a href="history_ambulance_booking.php">ประวัติการจองรถ</a>
                    <a href="claim.php">เคลมสินค้า</a>
                    <a href="../logout.php">ออกจากระบบ</a>
                </div>
            </div>
            <a href="index.php">
                <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
            </a>
        </nav>
    </div>
    <!-- Navbar ชั้นล่าง -->
    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
            <div><a href="reservation_car.php">จองคิวรถ</a></div>
            <a href="index.php">
                <img src="image/Logo.png" alt="Logo" class="nav-logo1">
            </a>
            <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
        </nav>

        <div class="cart-icon">
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>

    <section class="QRcode">
        <img src="image/QRcode.jpeg" alt="" class="qr-preview" id="qr-preview"><br>
        <?php echo "ยอดชำระทั้งหมด: ฿" . number_format($order_total, 2);  ?>
        <br><br>
        <div class="bottom-row">
            <p>แนบหลักฐานยืนยัน</p>
            <button class="upload-btn" id="upload-btn">อัพโหลด</button><br>
        </div>
        <p id="fileName"></p> <!-- เพิ่มส่วนนี้ใต้ปุ่มเพื่อแสดงชื่อไฟล์ --> <br>
        <div class="QR-buttons">
            <button class="cancle">ยกเลิก</button>
            <button class="confirm" id="confirm-btn">ยืนยัน</button>

        </div>
    </section>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const orderIds = urlParams.getAll("order_equipment_id[]");
        const equipmentIds = urlParams.getAll("equipment_id[]");
        const quantities = urlParams.getAll("quantity[]");

        if (orderIds.length === 0 && urlParams.get("order_equipment_id")) {
            orderIds.push(urlParams.get("order_equipment_id"));
            equipmentIds.push(urlParams.get("id"));
            quantities.push(urlParams.get("quantity"));
        }

        function postActionToServer(action, callback) {
            let completed = 0;

            for (let i = 0; i < orderIds.length; i++) {
                fetch(window.location.href, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            equipment_id: equipmentIds[i],
                            quantity: quantities[i],
                            action: action,
                            order_equipment_id: orderIds[i]
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        completed++;
                        if (completed === orderIds.length) {
                            callback();
                        }
                    });
            }
        }

        document.getElementById("confirm-btn").addEventListener("click", () => {
            postActionToServer("confirm", () => {
                const total = parseFloat(urlParams.get("price_total") || 0);
                if (total > 100000) {
                    window.location.href = "approve_payment.html";
                } else {
                    window.location.href = "success_payment.html";
                }
            });
        });

        document.querySelector(".cancle").addEventListener("click", () => {
            postActionToServer("cancel", () => {
                alert("ยกเลิกสำเร็จ");
                window.location.href = "shopping.php";
            });
        });

        setTimeout(() => {
            alert("หมดเวลาการชำระเงิน กรุณาสั่งซื้อใหม่");
            window.location.href = "shopping.php";
        }, 600000);
        document.addEventListener("DOMContentLoaded", () => {
            const uploadBtn = document.getElementById("upload-btn");
            const qrPreview = document.getElementById("qr-preview");
            const cancelBtn = document.getElementById("cancel-btn");
            const confirmBtn = document.getElementById("confirm-btn");

            // สร้าง input สำหรับอัพโหลดไฟล์
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';

            // เมื่อมีการเลือกไฟล์
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (file) {
                    // แสดงชื่อไฟล์ใต้ปุ่ม
                    const fileNameDisplay = document.querySelector('#fileName'); // สมมติว่าเรามี element ที่มี id="fileName" สำหรับแสดงชื่อไฟล์
                    fileNameDisplay.textContent = file.name; // ตั้งชื่อไฟล์ที่เลือกลงใน element

                    // แสดงปุ่มยืนยันและยกเลิกหลังจากเลือกไฟล์
                    cancelBtn.style.display = 'inline-block';
                    confirmBtn.style.display = 'inline-block';
                }
            });


            // เมื่อคลิกที่ปุ่ม "อัพโหลด"
            uploadBtn.addEventListener('click', () => {
                fileInput.click(); // เปิดหน้าต่างเลือกไฟล์
            });
        });
    </script>
</body>

</html>