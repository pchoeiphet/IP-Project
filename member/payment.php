<?php
include 'username.php';
$ids = $_GET['id'];

// get medical_equipment table
$medical_equipment_sql = "SELECT * 
            FROM equipment
            LEFT JOIN order_equipment ON equipment.equipment_id = order_equipment.equipment_id
            WHERE equipment.`equipment_id` = '$ids'";
$result_medical_equipment = mysqli_query($conn, $medical_equipment_sql);
$row = mysqli_fetch_assoc($result_medical_equipment);

// ถ้าจำนวนสินค้าคงเหลือ = 0 ให้ redirect หรือแสดงข้อความไม่พบสินค้า
if ($row['equipment_quantity'] == 0) {
    echo "<script>alert('สินค้าหมดแล้ว'); window.location.href = 'shopping.php';</script>";
    exit();
}

// get member
$random_memeber = $_GET['member_id'];
$member_sql = "SELECT * FROM member WHERE member_id = $random_memeber";
$result_member = mysqli_query($conn, $member_sql);
$row_result_member = mysqli_fetch_assoc($result_member);
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>หน้าการชำระเงิน</title>
    <?php include 'username.php'; ?>
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

    <section class="address">
        <p>ที่อยู่ในการจัดส่ง</p>
        <p><?= $row_result_member["member_firstname"] ?> <?= $row_result_member["member_lastname"] ?> (+66)<?= $row_result_member["member_phone"] ?> <?= $row_result_member["member_address"] ?> <?= $row_result_member["member_province"] ?></p>
        <a href="#" class="change-button">เปลี่ยนที่อยู่</a>
    </section>

    <section class="order-section">
        <div class="left-section">
            <div class="product">
                <div class="product-left">
                    <img src="image/<?= $row['equipment_image'] ?>" alt="">
                </div><br><br>
                <div class="product-center">
                    <?= $row['equipment_name'] ?><br>
                </div>
            </div>
            <div class="note">
                <label for="note">หมายเหตุ</label>
                <textarea id="note" placeholder="เพิ่มข้อความ"></textarea>
            </div>
        </div>

        <div class="right-section">
            <div class="product-right">
                <table class="product-table">
                    <tr>
                        <th>จำนวน</th>
                        <th>ราคารวม</th>
                    </tr>
                    <tr>
                        <td>
                            <div class="quantity-controls">
                                <div class="add-monts">
                                    <button class="btn-decrease">-</button>
                                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max=" " />
                                    <button class="btn-increase">+</button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="cost">฿ <?= number_format($row['equipment_price_per_unit'], 2) ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="options">
                <div class="purchase-type">
                    <p>ประเภทการสั่งซื้อ :</p>
                    <label><input type="button" name="purchase" value="เช่า" id="purchase-rent"></label>
                    <label><input type="button" name="purchase" value="ซื้อ" id="purchase-buy"></label>
                </div>

                <div class="installment">
                    <p>ระยะเวลาเช่าสินค้า :</p>
                    <div class="installment-control">
                        <div class="add-mont">
                            <button class="decrease-month" disabled>-</button>
                            <input type="number" class="installment-input" value="1" min="1" disabled>
                            <button class="increase-month" disabled>+</button>
                        </div>
                        <span>เดือน</span>
                    </div>
                </div>

                <div class="payment-method">
                    <p>วิธีชำระเงิน :</p>
                    <button type="button" id="payment-qr" class="payment-button">QR Promptpay</button>
                    <button type="button" id="payment-credit" class="payment-button" disabled>บัตรเครดิต</button>
                </div>
            </div>

            <div class="summary">
                <p>ราคารวม <span class="sumprice">฿ 5,900</span></p>
                <p>Vat 7 % <span class="vat">฿ 413</span> </p>
                <p>ค่าจัดส่งสินค้า <span class="delivery">฿ 120</span></p>
                <p>ยอดชำระทั้งหมด <span class="totalprice">฿ 6,433</span></p>
            </div>
            <button class="order-button2" id="tbtn">สั่งสินค้า</button>
        </div>
    </section>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateTotalPrice(); // คำนวณราคา

            // ตรวจสอบว่า stock เหลือ 0 หรือไม่ (จาก PHP ฝังไว้)
            const stockLeft = <?= $row['equipment_quantity'] ?>;

            if (stockLeft <= 0) {
                alert("สินค้าหมดแล้ว");
                window.location.href = "shopping.php";
            }
        });


        document.getElementById("tbtn").addEventListener("click", function() {
            let totalOrderPrice = parseFloat(document.querySelector('.summary .totalprice').textContent.replace('฿', '').trim());
            const payment_quantity = parseInt(document.querySelector(".quantity-input").value || 1); // จำนวนสินค้าที่เลือก
            // ดึง equipment_id จาก URL (ถ้ามี)
            const urlParams = new URLSearchParams(window.location.search);
            const equipmentId = urlParams.get("id"); // เช่นจาก shopping.php?id=5

            // ส่ง price_total + equipment_id ไปหน้า QRpayment
            fetch_order_equipment(formData).then((response) => {
                const orderId = response.order_equipment_id;
                window.location.href = `QRpayment_order.php?price_total=${totalOrderPrice}&id=${equipmentId}&quantity=${payment_quantity}&order_equipment_id=${orderId}`;
            });


        });

        // 1. ประกาศราคาต่อชิ้นจากฐานข้อมูล
        const pricePerItem = parseFloat("<?= $row['equipment_price_per_unit'] ?>"); // ราคาต่อชิ้นจากฐานข้อมูล
        const shippingCost = 120; // ค่าจัดส่ง

        const totalPriceElement = document.querySelector('.cost'); // ตารางที่แสดงราคารวม
        const totalPriceSum = document.querySelector('.summary .sumprice'); // ตารางที่แสดงราคารวม
        const totalOrderPriceElement = document.querySelector('.summary .totalprice'); // ราคารวมคำสั่งซื้อในส่วนของ summary (ยอดชำระทั้งหมด)
        const vatElement = document.querySelector('.summary .vat'); // ตารางแสดง VAT

        // 2. ฟังก์ชันคำนวณราคารวม
        function updateTotalPrice() {
            const quantity = parseInt(quantityInput.value || 0);
            const totalPrice = pricePerItem * quantity; // คำนวณราคารวมสินค้า

            totalPriceElement.textContent = `฿ ${totalPrice.toFixed(2)}`; // อัพเดตราคารวมสินค้า

            // คำนวณ VAT 7%
            const vat = totalPrice * 0.07; // คำนวณภาษีมูลค่าเพิ่ม (7%)
            vatElement.textContent = `฿ ${vat.toFixed(2)}`; // อัพเดตค่า VAT

            // คำนวณยอดชำระทั้งหมด (รวมค่าจัดส่งและ VAT)
            const totalOrderPrice = totalPrice + shippingCost + vat; // รวมราคาสินค้า, ค่าจัดส่ง, และ VAT
            totalPriceSum.textContent = `฿ ${totalPrice.toFixed(2)}`; // อัพเดตราคารวมสินค้าใน sumprice
            totalOrderPriceElement.textContent = `฿ ${totalOrderPrice.toFixed(2)}`; // อัพเดตยอดรวมคำสั่งซื้อ (ยอดชำระทั้งหมด)
        }


        // ฟังก์ชันเพิ่ม/ลดจำนวนสินค้า
        const decreaseButton = document.querySelector('.btn-decrease');
        const increaseButton = document.querySelector('.btn-increase');
        const quantityInput = document.querySelector('.quantity-input');

        // ดึงค่าจำนวนสินค้าสูงสุดจาก PHP
        const maxQuantity = <?= $row['equipment_quantity'] ?>;

        // ฟังก์ชันเพิ่มจำนวน
        increaseButton.addEventListener('click', function() {
            let currentQuantity = parseInt(quantityInput.value || 0);
            if (currentQuantity < maxQuantity) { // ตรวจสอบไม่ให้เกินจำนวนสินค้าในฐานข้อมูล
                quantityInput.value = currentQuantity + 1;
                updateTotalPrice(); // อัพเดตราคาใหม่หลังจากเพิ่ม
            }
        });

        // ฟังก์ชันลดจำนวน
        decreaseButton.addEventListener('click', function() {
            let currentQuantity = parseInt(quantityInput.value || 0);
            if (currentQuantity > 1) { // ตรวจสอบไม่ให้ต่ำกว่า 1
                quantityInput.value = currentQuantity - 1;
                updateTotalPrice(); // อัพเดตราคาใหม่หลังจากลด
            }
        });

        // ตรวจสอบการกรอกค่าในช่อง input
        quantityInput.addEventListener('input', function() {
            let currentQuantity = parseInt(quantityInput.value || 0);

            if (isNaN(currentQuantity) || currentQuantity < 1) {
                quantityInput.value = 1; // ถ้ากรอกเลขต่ำกว่า 1 ให้เป็น 1
            } else if (currentQuantity > maxQuantity) {
                quantityInput.value = maxQuantity; // ถ้ากรอกเกินให้เป็นค่ามากสุด
            }
            updateTotalPrice(); // อัพเดตราคาเมื่อกรอกข้อมูลใหม่
        });

        // 5. ฟังก์ชันจัดการการเลือก "เช่า" หรือ "ซื้อ"
        let payment_purchase = "" // ประกาศตัวแปรเก็บ เช่า หรือ ซื้อ

        document.getElementById('purchase-rent').addEventListener('click', function() {
            document.getElementById('purchase-rent').style.border = "2px solid #2D5696";
            document.getElementById('purchase-buy').style.border = "none";
            document.querySelector('.installment-input').disabled = false; // เปิดการใช้งานระยะเวลาเช่า
            decreaseMonthButton.disabled = false;
            increaseMonthButton.disabled = false;

            // รีเซ็ตให้เป็นค่าว่างเมื่อเลือก "เช่า"
            installmentInput.value = "1"; // รีเซ็ตเป็นค่าว่าง

            // คำนวณราคาหลังจากเลือก "เช่า"
            updateTotalPrice();
            payment_purchase = "เช่า";

        });

        document.getElementById('purchase-buy').addEventListener('click', function() {
            document.getElementById('purchase-buy').style.border = "2px solid #2D5696";
            document.getElementById('purchase-rent').style.border = "none";
            document.querySelector('.installment-input').disabled = true; // ปิดการใช้งานระยะเวลาเช่า
            decreaseMonthButton.disabled = true;
            increaseMonthButton.disabled = true;

            // รีเซ็ตเป็นค่าว่างเมื่อเลือก "ซื้อ"
            installmentInput.value = "0"; // รีเซ็ตเป็นค่าว่าง

            // คำนวณราคาหลังจากเลือก "ซื้อ"
            updateTotalPrice();
            payment_purchase = "ซื้อ"
        });


        // ฟังก์ชันจัดการการเพิ่ม/ลดระยะเวลาเช่า
        const decreaseMonthButton = document.querySelector('.decrease-month');
        const increaseMonthButton = document.querySelector('.increase-month');
        const installmentInput = document.querySelector('.installment-input');

        // เมื่อกดเพิ่ม
        increaseMonthButton.addEventListener('click', function() {
            let currentMonth = parseInt(installmentInput.value || 0);
            if (currentMonth < 36) { // ตรวจสอบไม่ให้เกิน 36 เดือน
                installmentInput.value = currentMonth + 1;
            }
        });

        // เมื่อกดลด
        decreaseMonthButton.addEventListener('click', function() {
            let currentMonth = parseInt(installmentInput.value || 0);
            if (currentMonth > 1) { // ตรวจสอบไม่ให้ต่ำกว่า 1 เดือน
                installmentInput.value = currentMonth - 1;
            }
        });
        // ตรวจสอบการกรอกค่าในช่อง input เดือน
        installmentInput.addEventListener('input', function() {
            let currentMonth = parseInt(installmentInput.value || 0);

            // ถ้ากรอกค่าที่ไม่ใช่ตัวเลขหรือต่ำกว่า 1 ให้เป็น 1
            if (isNaN(currentMonth) || currentMonth < 1) {
                installmentInput.value = 1; // ถ้ากรอกเลขต่ำกว่า 1 ให้เป็น 1
            }
            // ถ้ากรอกค่าเกิน 36 เดือน ให้ตั้งค่าเป็น 36
            else if (currentMonth > 36) {
                installmentInput.value = 36; // ถ้ากรอกเกินให้เป็น 36
            }

            // อัพเดตราคาเมื่อกรอกข้อมูลใหม่ (หรือทำการคำนวณอย่างอื่นที่ต้องการ)
            updateTotalPrice(); // ฟังก์ชันนี้ต้องมีการคำนวณใหม่
        });



        // ฟังก์ชันจัดการการเลือก "QR Promptpay" และ "บัตรเครดิต"
        let payment_type = "";
        document.getElementById('payment-qr').addEventListener('click', function() {
            document.getElementById('payment-qr').style.border = "2px solid #2D5696"; // เปลี่ยนสีขอบเมื่อเลือก
            document.getElementById('payment-credit').style.border = "none"; // รีเซ็ตขอบของบัตรเครดิต
            payment_type = "QR Promptpay";
        });

        // ฟังก์ชันจัดการการเลือก "บัตรเครดิต"
        document.getElementById('payment-credit').addEventListener('click', function() {
            document.getElementById('payment-credit').style.border = "2px solid #2D5696"; // เปลี่ยนสีขอบเมื่อเลือก
            document.getElementById('payment-qr').style.border = "none"; // รีเซ็ตขอบของ QR Promptpay
            payment_type = "บัตรเครดิต";
        });

        // 6. ฟังก์ชันคำนวณราคาเมื่อหน้าโหลด
        document.addEventListener('DOMContentLoaded', function() {
            updateTotalPrice(); // คำนวณราคาเมื่อหน้าโหลด
        });


        // รับข้อมูล
        document.getElementById("tbtn").addEventListener("click", function() {
            let totalOrderPrice = parseFloat(document.querySelector('.summary .totalprice').textContent.replace('฿', '').trim());
            const payment_quantity = parseInt(document.querySelector(".quantity-input").value || 1);

            const urlParams = new URLSearchParams(window.location.search);
            const equipmentId = urlParams.get("id");
            const member_id = urlParams.get("member_id");

            // ✅ คำนวณราคาต่อหน่วยใหม่ก่อนใช้
            let payment_cost = pricePerItem;
            let payment_rent_months = parseInt(document.querySelector(".installment-input").value || 0);

            let formData = {
                "member_id": member_id,
                "equipment_id": equipmentId,
                "order_equipment_type": payment_purchase,
                "order_equipment_price": payment_cost,
                "order_equipment_quantity": payment_quantity,
                "order_equipment_total": totalOrderPrice,
                "order_equipment_buy_type": payment_type,
                "order_equipment_months": payment_rent_months
            };

            // ✅ ส่งข้อมูล แล้ว redirect พร้อม order_equipment_id
            fetch('insert_order.php', {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        const order_equipment_id = data.order_equipment_id;
                        window.location.href = `QRpayment_order.php?price_total=${totalOrderPrice}&id=${equipmentId}&quantity=${payment_quantity}&order_equipment_id=${order_equipment_id}`;
                    } else {
                        alert("เกิดข้อผิดพลาดในการสั่งซื้อ");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("เชื่อมต่อกับเซิร์ฟเวอร์ไม่ได้");
                });
        });
    </script>
</body>

</html>