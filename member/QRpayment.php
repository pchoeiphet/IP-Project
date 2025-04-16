<?php
$order_total = isset($_GET['price_total']) ? $_GET['price_total'] : 0;
?>

<html lang="en">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_QR_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>Document</title>
    <!-- <script src="javascrip_member/QRpayment.js" defer></script> -->
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

    <body>
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
    </body>
    <script>
        document.getElementById("confirm-btn").addEventListener("click", function() {
            let orderTotal = <?php echo $order_total; ?>;

            if (orderTotal > 100000) {
                window.location.href = "approve_payment.html"; // ไปหน้าอื่นเมื่อมากกว่า 100,000
            } else {
                window.location.href = "success_payment.html"; // ปกติไปหน้าสำเร็จ
            }
        });

        // เลือกปุ่มด้วย class 'cancle'
        document.querySelector(".cancle").addEventListener("click", function() {
            window.location.href = "form.php"; // ไปที่ form.php
        });


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

        function submitForm() {
            // สามารถใช้ AJAX เพื่อส่งข้อมูลที่ต้องการก่อนเปลี่ยนหน้า
            let formData = {
                "key": "value"
            };

            fetch('success_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    // ส่งข้อมูลสำเร็จแล้ว ทำการเปลี่ยนหน้า
                    window.location.href = 'success_payment.html'; // ไปยังไฟล์เป้าหมาย
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

</html>