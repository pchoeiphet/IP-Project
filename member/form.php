<?php
session_start();
include 'username.php';

// ถ้าไม่ได้ล็อกอิน ให้ redirect กลับไปหน้า login
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// เรียก member_id จาก session มาใช้
$member_id = $_SESSION['user_id'];
//------------------------------------------

// รับค่าจาก URL หรือ POST
$booking_date = $_GET['booking_date'] ?? 'ไม่มีวันที่';
$booking_start_time = $_GET['booking_start_time'] ?? 'ไม่มีเวลา';

$sql = "SELECT member_firstname, member_lastname 
        FROM member 
        WHERE member_id = '$member_id'";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_car.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <title>ฟอร์มจองรถ</title>
    <style>
        #map,
        #patientMap {
            height: 300px;
            width: 100%;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .form-group {
            display: flex;
            align-items: center;
        }

        #searchEventLocation,
        #searchPatientLocation {
            margin-right: 10px;
            /* ระยะห่างระหว่าง input กับปุ่ม */
        }

        #searchEventLocation+button,
        #searchPatientLocation+button {
            padding: 10px 20px;
            background-color: #FFB898;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-left: 10px;
            /* ระยะห่างที่ต้องการให้ปุ่มขยับไปทางขวา */
        }

        #searchEventLocation+button:hover {
            background-color: #0056b3;
        }
    </style>
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
            <a href="index.html">
                <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
            </a>
        </nav>
    </div>

    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
            <div><a href="reservation_car.php" style="color: #FFB898">จองคิวรถ</a></div>
            <a href="index.php">
                <img src="image/Logo.png" alt="Logo" class="nav-logo1">
            </a>
            <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
        </nav>

        <div class="cart-icon">
            <a href="cart.html">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>
    <br>

    <!-- Dropdown for selecting forms -->
    <div style="text-align: center; font-weight: bold;" class="form-select">
        <label for="formSelect">ประเภทการจอง</label>
        <select id="formSelect" name="event_type" required
            style="width: 30%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
            <option value="form1">จองงาน Event</option>
            <option value="form2">จองสำหรับรับส่งผู้ป่วย</option>
        </select>
    </div>

    <!-- Form 1 -->
    <form action="insert_data_form.php" method="POST">
        <div id="form1" class="form-container active">
            <h2 style="text-align: center;">จองงาน Event</h2>
            <div id="selectedDateTime">
                <p>วันที่เลือก: <?php echo $booking_date; ?></span></p>
                <p>เวลาที่เลือก: <?php echo $booking_start_time; ?></span></p>
            </div>
            <br>
            <!-- สร้าง form ซ่อนไว้สำหรับส่งข้อมูล -->
            <form id="bookingForm">
                <input type="hidden" name="booking_date" id="bookingDate" value="<?php echo $booking_date; ?>">
                <input type="hidden" name="booking_start_time" id="bookingTime" value="<?php echo $booking_start_time; ?>">
            </form>

            <div class="form-group">

                <div class="radio">
                    <label for="level">ระดับรถ</label>

                    <div>
                        <input type="radio" id="first" name="level" value="1" required onchange="calculatePrice()"> ระดับ 1 (Basic Life Support)
                    </div>
                    <div>
                        <input type="radio" id="basic" name="level" value="2" required onchange="calculatePrice()"> ระดับ 2 (Advance Life Support)
                    </div>
                    <div>
                        <input type="radio" id="advanced" name="level" value="3" required onchange="calculatePrice()"> ระดับ 3 (Mobile Intensive Care Unit)
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="province1">จังหวัด</label>
                <select id="province1" name="province1" required onchange="updateMapByProvince('event')"
                    style="width: 25%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
                    <option value="" selected hidden>เลือกจังหวัด</option>
                    <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                    <option value="กระบี่">กระบี่</option>
                    <option value="กาญจนบุรี">กาญจนบุรี</option>
                    <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                    <option value="กำแพงเพชร">กำแพงเพชร</option>
                    <option value="ขอนแก่น">ขอนแก่น</option>
                    <option value="จันทบุรี">จันทบุรี</option>
                    <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                    <option value="ชลบุรี">ชลบุรี</option>
                    <option value="ชัยนาท">ชัยนาท</option>
                    <option value="ชัยภูมิ">ชัยภูมิ</option>
                    <option value="ชุมพร">ชุมพร</option>
                    <option value="เชียงราย">เชียงราย</option>
                    <option value="เชียงใหม่">เชียงใหม่</option>
                    <option value="ตรัง">ตรัง</option>
                    <option value="ตราด">ตราด</option>
                    <option value="ตาก">ตาก</option>
                    <option value="นครนายก">นครนายก</option>
                    <option value="นครปฐม">นครปฐม</option>
                    <option value="นครพนม">นครพนม</option>
                    <option value="นครราชสีมา">นครราชสีมา</option>
                    <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                    <option value="นครสวรรค์">นครสวรรค์</option>
                    <option value="นนทบุรี">นนทบุรี</option>
                    <option value="นราธิวาส">นราธิวาส</option>
                    <option value="น่าน">น่าน</option>
                    <option value="บึงกาฬ">บึงกาฬ</option>
                    <option value="บุรีรัมย์">บุรีรัมย์</option>
                    <option value="ปทุมธานี">ปทุมธานี</option>
                    <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                    <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                    <option value="ปัตตานี">ปัตตานี</option>
                    <option value="พะเยา">พะเยา</option>
                    <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                    <option value="พังงา">พังงา</option>
                    <option value="พัทลุง">พัทลุง</option>
                    <option value="พิจิตร">พิจิตร</option>
                    <option value="พิษณุโลก">พิษณุโลก</option>
                    <option value="เพชรบุรี">เพชรบุรี</option>
                    <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                    <option value="แพร่">แพร่</option>
                    <option value="ภูเก็ต">ภูเก็ต</option>
                    <option value="มหาสารคาม">มหาสารคาม</option>
                    <option value="มุกดาหาร">มุกดาหาร</option>
                    <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                    <option value="ยโสธร">ยโสธร</option>
                    <option value="ยะลา">ยะลา</option>
                    <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                    <option value="ระนอง">ระนอง</option>
                    <option value="ระยอง">ระยอง</option>
                    <option value="ราชบุรี">ราชบุรี</option>
                    <option value="ลพบุรี">ลพบุรี</option>
                    <option value="ลำปาง">ลำปาง</option>
                    <option value="ลำพูน">ลำพูน</option>
                    <option value="เลย">เลย</option>
                    <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                    <option value="สกลนคร">สกลนคร</option>
                    <option value="สงขลา">สงขลา</option>
                    <option value="สตูล">สตูล</option>
                    <option value="สมุทรปราการ">สมุทรปราการ</option>
                    <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                    <option value="สมุทรสาคร">สมุทรสาคร</option>
                    <option value="สระแก้ว">สระแก้ว</option>
                    <option value="สระบุรี">สระบุรี</option>
                    <option value="สิงห์บุรี">สิงห์บุรี</option>
                    <option value="สุโขทัย">สุโขทัย</option>
                    <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                    <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                    <option value="สุรินทร์">สุรินทร์</option>
                    <option value="หนองคาย">หนองคาย</option>
                    <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                    <option value="อ่างทอง">อ่างทอง</option>
                    <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                    <option value="อุดรธานี">อุดรธานี</option>
                    <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                    <option value="อุทัยธานี">อุทัยธานี</option>
                    <option value="อุบลราชธานี">อุบลราชธานี</option>
                </select>
            </div>
            <div class="form-group">
                <label for="place_event_detail">รายละเอียดสถานที่</label>
                <textarea id="place_event_detail" name="place_event_detail" rows="4" cols="50" required></textarea>
            </div>
            <div class="form-group">
                <label for="searchEventLocation">ค้นหาชื่อสถานที่</label>
                <input type="text" id="searchEventLocation" name="searchEventLocation" placeholder="เช่น ชื่อสถานที่">
                <button onclick="searchLocation('searchEventLocation', 'event')">ค้นหา</button>
            </div>
            <div class="form-group">
                <label for="place_event_location">สถานที่รับงาน</label>
                <div id="map"></div>
                <input type="hidden" id="place_event_location" name="place_event_location">
            </div>


            <div class="form-group">
                <label for="type">ประเภทงาน</label>
                <select id="type" name="event_type" required
                    style="width: 30%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
                    <option value="" selected hidden>เลือกประเภทงาน</option>
                    <option value="กีฬาสีและการแข่งขัน">กีฬาสีและการแข่งขัน</option>
                    <option value="งานชุมนุม">งานชุมนุม</option>
                    <option value="งานพิธีการ">งานพิธีการ</option>
                    <option value="อุตสาหกรรมก่อสร้าง">อุตสาหกรรมก่อสร้าง</option>
                    <option value="กิจกรรมเด็กหรือผู้สูงวัย">กิจกรรมเด็กหรือผู้สูงวัย</option>
                    <option value="คัดกรองโรค">คัดกรองโรค</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nurse_number">จำนวนพยาบาล</label>
                <input type="number" id="nurse_number" name="nurse_number" required min="0" step="1" value="0"
                    style="text-align: center; width: 100px;" oninput="calculatePrice()"> คน/คัน
            </div>

            <div class="form-group">
                <label for="ambulance_number">จำนวนรถพยาบาล</label>
                <input type="number" id="ambulance_number" name="ambulance_number" required min="1" step="1" value="1"
                    style="text-align: center; width: 100px;" oninput="calculatePrice()"> คัน
                <!-- oninput="validateNumber(event) -->
            </div>

            <div class="form-group">
                <label for="payment_method">วิธีการชำระเงิน</label>
                <input type="hidden" id="payment_method_event" name="payment_method_event">
                <div class="payment-options">
                    <button type="button" id="payment-qr" class="payment-button">QR Promptpay</button>
                    <button type="button" id="payment-credit" class="payment-button" disabled>บัตรเครดิต</button>
                </div>
            </div>

            <!-- แสดงราคาค่าบริการ -->
            <div class="form-group">
                <p id="priceDisplay1" style="text-align: left; font-size: 18px;">ราคาค่าบริการ : 0 บาท</p>
            </div>

            <!-- เก็บราคาสำหรับส่งไปยัง Backend -->
            <input type="hidden" id="calculatedPrice1" name="calculatedPrice1">
            <input type="hidden" id="calculatedDistance1" name="calculatedDistance1">

            <div class="form-submit">
                <button type="button" id="cancel-button" class="cancel-button"
                    style="background-color: #F8E6DE;">ยกเลิก</button>
                <button type="submit" name="submit_event" style="background-color: #FFB898;" id="submit-button" onclick="submitPaymentEvent()">ยืนยัน</button>
            </div>
    </form>
    </div>

    <!-- Form 2 -->
    <form action="insert_data_form.php" method="post">
        <div id="form2" class="form-container">
            <h2 style="text-align: center;">จองสำหรับรับส่งผู้ป่วย</h2>
            <div id="selectedDateTime">
                <p>วันที่เลือก: <?php echo $booking_date; ?></span></p>
                <p>เวลาที่เลือก: <?php echo $booking_start_time; ?></span></p>
            </div>
            <br>
            <!-- สร้าง form ซ่อนไว้สำหรับส่งข้อมูล -->
            <form id="bookingForm">
                <input type="hidden" name="booking_date" id="bookingDate" value="<?php echo $booking_date; ?>">
                <input type="hidden" name="booking_start_time" id="bookingTime" value="<?php echo $booking_start_time; ?>">
            </form>
            <div class="form-group">

                <div class="radio">
                    <label for="level">ระดับรถ</label>

                    <div>
                        <input type="radio" id="first" name="level" value="1" require onchange="calculatePrice()"> ระดับ 1 (Basic Life Support)
                    </div>
                    <div>
                        <input type="radio" id="basic" name="level" value="2" require onchange="calculatePrice()"> ระดับ 2 (Advance Life Support)
                    </div>
                    <div>
                        <input type="radio" id="advanced" name="level" value="3" require onchange="calculatePrice()"> ระดับ 3 (Mobile Intensive Care Unit)
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="patient-name">ชื่อผู้ป่วย</label>
                <label for="patient-name"><?php while ($row = $result->fetch_assoc()) {
                                                echo $row['member_firstname'] . " " . $row['member_lastname'];
                                            }; ?>
                </label>
            </div>
            <div class="form-group">
                <label for="province2">จังหวัด</label>
                <select id="province2" name="province2" required onchange="updateMapByProvince('patient')">
                    style="width: 25%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
                    <option value="" selected hidden>เลือกจังหวัด</option>
                    <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                    <option value="กระบี่">กระบี่</option>
                    <option value="กาญจนบุรี">กาญจนบุรี</option>
                    <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                    <option value="กำแพงเพชร">กำแพงเพชร</option>
                    <option value="ขอนแก่น">ขอนแก่น</option>
                    <option value="จันทบุรี">จันทบุรี</option>
                    <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                    <option value="ชลบุรี">ชลบุรี</option>
                    <option value="ชัยนาท">ชัยนาท</option>
                    <option value="ชัยภูมิ">ชัยภูมิ</option>
                    <option value="ชุมพร">ชุมพร</option>
                    <option value="เชียงราย">เชียงราย</option>
                    <option value="เชียงใหม่">เชียงใหม่</option>
                    <option value="ตรัง">ตรัง</option>
                    <option value="ตราด">ตราด</option>
                    <option value="ตาก">ตาก</option>
                    <option value="นครนายก">นครนายก</option>
                    <option value="นครปฐม">นครปฐม</option>
                    <option value="นครพนม">นครพนม</option>
                    <option value="นครราชสีมา">นครราชสีมา</option>
                    <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                    <option value="นครสวรรค์">นครสวรรค์</option>
                    <option value="นนทบุรี">นนทบุรี</option>
                    <option value="นราธิวาส">นราธิวาส</option>
                    <option value="น่าน">น่าน</option>
                    <option value="บึงกาฬ">บึงกาฬ</option>
                    <option value="บุรีรัมย์">บุรีรัมย์</option>
                    <option value="ปทุมธานี">ปทุมธานี</option>
                    <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                    <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                    <option value="ปัตตานี">ปัตตานี</option>
                    <option value="พะเยา">พะเยา</option>
                    <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                    <option value="พังงา">พังงา</option>
                    <option value="พัทลุง">พัทลุง</option>
                    <option value="พิจิตร">พิจิตร</option>
                    <option value="พิษณุโลก">พิษณุโลก</option>
                    <option value="เพชรบุรี">เพชรบุรี</option>
                    <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                    <option value="แพร่">แพร่</option>
                    <option value="ภูเก็ต">ภูเก็ต</option>
                    <option value="มหาสารคาม">มหาสารคาม</option>
                    <option value="มุกดาหาร">มุกดาหาร</option>
                    <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                    <option value="ยโสธร">ยโสธร</option>
                    <option value="ยะลา">ยะลา</option>
                    <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                    <option value="ระนอง">ระนอง</option>
                    <option value="ระยอง">ระยอง</option>
                    <option value="ราชบุรี">ราชบุรี</option>
                    <option value="ลพบุรี">ลพบุรี</option>
                    <option value="ลำปาง">ลำปาง</option>
                    <option value="ลำพูน">ลำพูน</option>
                    <option value="เลย">เลย</option>
                    <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                    <option value="สกลนคร">สกลนคร</option>
                    <option value="สงขลา">สงขลา</option>
                    <option value="สตูล">สตูล</option>
                    <option value="สมุทรปราการ">สมุทรปราการ</option>
                    <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                    <option value="สมุทรสาคร">สมุทรสาคร</option>
                    <option value="สระแก้ว">สระแก้ว</option>
                    <option value="สระบุรี">สระบุรี</option>
                    <option value="สิงห์บุรี">สิงห์บุรี</option>
                    <option value="สุโขทัย">สุโขทัย</option>
                    <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                    <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                    <option value="สุรินทร์">สุรินทร์</option>
                    <option value="หนองคาย">หนองคาย</option>
                    <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                    <option value="อ่างทอง">อ่างทอง</option>
                    <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                    <option value="อุดรธานี">อุดรธานี</option>
                    <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                    <option value="อุทัยธานี">อุทัยธานี</option>
                    <option value="อุบลราชธานี">อุบลราชธานี</option>
                </select>
            </div>
            <div class="form-group">
                <label for="place_ambulance_detail">รายละเอียดสถานที่</label>
                <textarea id="place_ambulance_detail" name="place_ambulance_detail" rows="4" cols="50" required></textarea>
            </div>
            <div class="form-group">
                <label for="searchPatientLocation">ค้นหาชื่อสถานที่:</label>
                <input type="text" id="searchPatientLocation" name="searchPatientLocation" placeholder="เช่น ชื่อสถานที่">
                <button type="button" onclick="searchLocation('searchPatientLocation', 'patient')">ค้นหา</button>
            </div>

            <div class="form-group">
                <label for="pickup-location">สถานที่รับงาน</label>
                <div id="patientMap"></div>
                <input type="hidden" id="pickup-location" name="pickup-location">
            </div>
            <div class="form-group">
                <label for="destinationProvince">จังหวัดจุดหมายปลายทาง</label>
                <select id="destinationProvince" name="destinationProvince" required>
                    <option value="" selected hidden>เลือกจังหวัด</option>
                    <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                    <option value="กระบี่">กระบี่</option>
                    <option value="กาญจนบุรี">กาญจนบุรี</option>
                    <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                    <option value="กำแพงเพชร">กำแพงเพชร</option>
                    <option value="ขอนแก่น">ขอนแก่น</option>
                    <option value="จันทบุรี">จันทบุรี</option>
                    <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                    <option value="ชลบุรี">ชลบุรี</option>
                    <option value="ชัยนาท">ชัยนาท</option>
                    <option value="ชัยภูมิ">ชัยภูมิ</option>
                    <option value="ชุมพร">ชุมพร</option>
                    <option value="เชียงราย">เชียงราย</option>
                    <option value="เชียงใหม่">เชียงใหม่</option>
                    <option value="ตรัง">ตรัง</option>
                    <option value="ตราด">ตราด</option>
                    <option value="ตาก">ตาก</option>
                    <option value="นครนายก">นครนายก</option>
                    <option value="นครปฐม">นครปฐม</option>
                    <option value="นครพนม">นครพนม</option>
                    <option value="นครราชสีมา">นครราชสีมา</option>
                    <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                    <option value="นครสวรรค์">นครสวรรค์</option>
                    <option value="นนทบุรี">นนทบุรี</option>
                    <option value="นราธิวาส">นราธิวาส</option>
                    <option value="น่าน">น่าน</option>
                    <option value="บึงกาฬ">บึงกาฬ</option>
                    <option value="บุรีรัมย์">บุรีรัมย์</option>
                    <option value="ปทุมธานี">ปทุมธานี</option>
                    <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                    <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                    <option value="ปัตตานี">ปัตตานี</option>
                    <option value="พะเยา">พะเยา</option>
                    <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                    <option value="พังงา">พังงา</option>
                    <option value="พัทลุง">พัทลุง</option>
                    <option value="พิจิตร">พิจิตร</option>
                    <option value="พิษณุโลก">พิษณุโลก</option>
                    <option value="เพชรบุรี">เพชรบุรี</option>
                    <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                    <option value="แพร่">แพร่</option>
                    <option value="ภูเก็ต">ภูเก็ต</option>
                    <option value="มหาสารคาม">มหาสารคาม</option>
                    <option value="มุกดาหาร">มุกดาหาร</option>
                    <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                    <option value="ยโสธร">ยโสธร</option>
                    <option value="ยะลา">ยะลา</option>
                    <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                    <option value="ระนอง">ระนอง</option>
                    <option value="ระยอง">ระยอง</option>
                    <option value="ราชบุรี">ราชบุรี</option>
                    <option value="ลพบุรี">ลพบุรี</option>
                    <option value="ลำปาง">ลำปาง</option>
                    <option value="ลำพูน">ลำพูน</option>
                    <option value="เลย">เลย</option>
                    <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                    <option value="สกลนคร">สกลนคร</option>
                    <option value="สงขลา">สงขลา</option>
                    <option value="สตูล">สตูล</option>
                    <option value="สมุทรปราการ">สมุทรปราการ</option>
                    <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                    <option value="สมุทรสาคร">สมุทรสาคร</option>
                    <option value="สระแก้ว">สระแก้ว</option>
                    <option value="สระบุรี">สระบุรี</option>
                    <option value="สิงห์บุรี">สิงห์บุรี</option>
                    <option value="สุโขทัย">สุโขทัย</option>
                    <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                    <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                    <option value="สุรินทร์">สุรินทร์</option>
                    <option value="หนองคาย">หนองคาย</option>
                    <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                    <option value="อ่างทอง">อ่างทอง</option>
                    <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                    <option value="อุดรธานี">อุดรธานี</option>
                    <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                    <option value="อุทัยธานี">อุทัยธานี</option>
                    <option value="อุบลราชธานี">อุบลราชธานี</option>
                </select>
            </div>


            <div class="form-group">
                <label for="hospital">โรงพยาบาล</label>
                <select id="hospital" name="hospital" required>
                    <option value="" selected hidden>เลือกโรงพยาบาล</option>
                </select>
                <input type="hidden" id="hospitalLatLon" name="hospitalLatLon">
            </div>
            <div class="form-group">
                <label for="symptom">อาการ/โรค</label>
                <select id="symptom" name="symptom" required
                    style="width: 30%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
                    <option value="" selected hidden>เลือกอาการ/โรค</option>
                    <option value="เกี่ยวกับระบบทางเดินหายใจ">เกี่ยวกับระบบทางเดินหายใจ</option>
                    <option value="เกี่ยวกับระบบไหลเวียนเลือด">เกี่ยวกับระบบไหลเวียนเลือด</option>
                    <option value="เกี่ยวกับกล้ามเนื้อและกระดูก">เกี่ยวกับกล้ามเนื้อและกระดูก</option>
                    <option value="โรคเรื้อรัง">โรคเรื้อรัง</option>
                    <option value="สุขภาพจิต">สุขภาพจิต</option>
                </select>
            </div>
            <div class="form-group">
                <label for="allergy">แพ้ยา/อาหาร</label>
                <select id="allergy" name="allergy" required
                    style="width: 30%; padding: 8px; border: 1px solid #ccc; border-radius: 15px;">
                    <option value="" selected hidden>เลือกแพ้ยา/อาหาร</option>
                    <option value="อาหารทะเล">อาหารทะเล</option>
                    <option value="นมวัว">นมวัว</option>
                    <option value="ถั่วลิสง">ถั่วลิสง</option>
                    <option value="ไข่">ไข่</option>
                    <option value="ยาปฏิชีวนะ">ยาปฏิชีวนะ</option>
                    <option value="ยาชา">ยาชา</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payment-method">วิธีการชำระเงิน</label>
                <input type="hidden" id="payment_method_hospital" name="payment_method_hospital">
                <div class="payment-options">
                    <button type="button" id="payment-qr2" class="payment-button">QR Promptpay</button>
                    <button type="button" id="payment-credit2" class="payment-button" disabled>บัตรเครดิต</button>
                </div>
            </div>

            <!-- แสดงราคาค่าบริการ -->
            <div class="form-group">
                <p id="priceDisplay2" style="text-align: left; font-size: 18px;">ราคาค่าบริการ: 0 บาท</p>
            </div>

            <!-- เก็บราคาสำหรับส่งไปยัง Backend -->
            <input type="hidden" id="calculatedPrice2" name="calculatedPrice2">
            <input type="hidden" id="calculatedDistance2" name="calculatedDistance2">

            <div class="form-submit">
                <button type="button" id="cancel-button" class="cancel-button"
                    style="background-color: #F8E6DE;">ยกเลิก</button>
                <button type="submit" name="submit_ambulance" style="background-color: #FFB898;" id="submit-button">ยืนยัน</button>
            </div>
    </form>
    </div>

    <script>
        // ฟังก์ชันที่จะส่งข้อมูลไปยัง QRpayment.php เมื่อคลิกปุ่ม "ยืนยัน"
        function submitPaymentEvent() {
            var calculatedPrice = document.getElementById("calculatedPrice1").value; // รับค่า calculatedPrice1
            var url = "QRpayment.php?total_price=" + calculatedPrice; // สร้าง URL สำหรับส่งข้อมูล
            window.location.href = url; // เปลี่ยนหน้าผ่าน URL ที่ส่งข้อมูล
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("payment-qr").addEventListener("click", function() {
                document.getElementById("payment_method_event").value = "QR Promptpay";
            });

            document.getElementById("payment-credit").addEventListener("click", function() {
                document.getElementById("payment_method_event").value = "บัตรเครดิต";
            });
        });

        // การเลือกวิธีการชำระเงิน
        let selectedPaymentMethod = "";

        document.getElementById('payment-qr').addEventListener('click', function() {
            selectedPaymentMethod = 'QR Promptpay';
            document.getElementById('payment-qr').style.border = "2px solid #2D5696";
            document.getElementById('payment-credit').style.border = "none";
        });

        document.getElementById('payment-credit').addEventListener('click', function() {
            selectedPaymentMethod = 'บัตรเครดิต';
            document.getElementById('payment-credit').style.border = "2px solid #2D5696";
            document.getElementById('payment-qr').style.border = "none";
        });

        // ยกเลิกการจอง
        document.getElementById('cancel-button').addEventListener('click', function() {
            // รีเซ็ตฟอร์มเมื่อคลิกปุ่มยกเลิก
            document.querySelector("form").reset();
            document.getElementById('payment-qr').style.border = "none";
            document.getElementById('payment-credit').style.border = "none";
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("payment-qr2").addEventListener("click", function() {
                document.getElementById("payment_method_hospital").value = "QR Promptpay";
            });

            document.getElementById("payment-credit2").addEventListener("click", function() {
                document.getElementById("payment_method_hospital").value = "บัตรเครดิต";
            });
        });

        // การเลือกวิธีการชำระเงิน
        let selectedPaymentMethod2 = "";

        document.getElementById('payment-qr2').addEventListener('click', function() {
            selectedPaymentMethod = 'QR Promptpay';
            document.getElementById('payment-qr2').style.border = "2px solid #2D5696";
            document.getElementById('payment-credit2').style.border = "none";
        });

        document.getElementById('payment-credit2').addEventListener('click', function() {
            selectedPaymentMethod = 'บัตรเครดิต';
            document.getElementById('payment-credit2').style.border = "2px solid #2D5696";
            document.getElementById('payment-qr2').style.border = "none";
        });

        // ยกเลิกการจอง
        document.getElementById('cancel-button').addEventListener('click', function() {
            // รีเซ็ตฟอร์มเมื่อคลิกปุ่มยกเลิก
            document.querySelector("form").reset();
            document.getElementById('payment-qr2').style.border = "none";
            document.getElementById('payment-credit2').style.border = "none";
        });
    </script>
    <script>
        //ตรวจการรับค่าจังหวัดและเก็บเป็นภูมิภาค
        function checkRegion() {
            const provinces = {
                "ภาคเหนือ": ["เชียงใหม่", "เชียงราย", "ลำปาง", "ลำพูน", "แพร่", "น่าน", "พะเยา", "แม่ฮ่องสอน", "อุตรดิตถ์", "สุโขทัย", "พิษณุโลก", "ตาก", "เพชรบูรณ์", "นครสวรรค์", "กำแพงเพชร", "พิจิตร", "อุทัยธานี"],
                "ภาคกลาง": ["กรุงเทพมหานคร", "สมุทรปราการ", "นนทบุรี", "ปทุมธานี", "พระนครศรีอยุธยา", "สระบุรี", "ลพบุรี", "อ่างทอง", "ชัยนาท", "สิงห์บุรี", "นครนายก", "นครปฐม", "สุพรรณบุรี", "สมุทรสาคร", "สมุทรสงคราม", "เพชรบุรี", "ประจวบคีรีขันธ์", "ราชบุรี", "กาญจนบุรี",
                    // รวมภาคตะวันออก
                    "ชลบุรี", "ระยอง", "จันทบุรี", "ตราด", "ฉะเชิงเทรา", "ปราจีนบุรี", "สระแก้ว"
                ],
                "ภาคตะวันออกเฉียงเหนือ": ["ขอนแก่น", "นครราชสีมา", "อุดรธานี", "อุบลราชธานี", "หนองคาย", "มหาสารคาม", "ร้อยเอ็ด", "สุรินทร์", "บุรีรัมย์", "ศรีสะเกษ", "กาฬสินธุ์", "ชัยภูมิ", "ยโสธร", "สกลนคร", "หนองบัวลำภู", "นครพนม", "บึงกาฬ", "มุกดาหาร", "อำนาจเจริญ"],
                "ภาคใต้": ["ภูเก็ต", "สุราษฎร์ธานี", "สงขลา", "นราธิวาส", "ยะลา", "ปัตตานี", "พังงา", "กระบี่", "ตรัง", "นครศรีธรรมราช", "พัทลุง", "ชุมพร", "ระนอง", "สตูล"]
            };

            let province = document.getElementById("province").value;
            let regionInput = document.querySelector(".region input");

            let region = "ไม่พบภูมิภาค";
            for (let key in provinces) {
                if (provinces[key].includes(province)) {
                    region = key;
                    break;
                }
            }
            regionInput.value = region;
        }
    </script>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        // กำหนดแผนที่
        let map, patientMap, marker, patientMarker;

        // พิกัดจังหวัด
        const provinceCoordinates = {
            "กรุงเทพมหานคร": [13.7500, 100.5166],
            "กระบี่": [8.0520, 98.9120],
            "กาญจนบุรี": [14.0174, 99.5220],
            "กาฬสินธุ์": [16.4280, 103.5090],
            "กำแพงเพชร": [16.4730, 99.5290],
            "ขอนแก่น": [16.4200, 102.8300],
            "จันทบุรี": [12.6133, 102.0979],
            "ฉะเชิงเทรา": [13.6790, 101.0760],
            "ชลบุรี": [13.1590, 100.9287],
            "ชัยนาท": [15.1790, 100.1260],
            "ชัยภูมิ": [15.8040, 102.0386],
            "ชุมพร": [10.5127, 99.1872],
            "เชียงราย": [19.9119, 99.8265],
            "เชียงใหม่": [18.7999, 98.9800],
            "ตรัง": [7.5634, 99.6080],
            "ตราด": [12.2370, 102.5090],
            "ตาก": [16.7162, 98.5708],
            "นครนายก": [14.2000, 101.2160],
            "นครปฐม": [13.8180, 100.0640],
            "นครพนม": [17.3945, 104.7695],
            "นครราชสีมา": [15.5840, 102.4186],
            "นครศรีธรรมราช": [8.1540, 99.7286],
            "นครสวรรค์": [15.7000, 100.0700],
            "นนทบุรี": [13.8337, 100.4833],
            "นราธิวาส": [6.4318, 101.8214],
            "น่าน": [18.7868, 100.7715],
            "บึงกาฬ": [18.3609, 103.6466],
            "บุรีรัมย์": [15.0004, 103.1166],
            "ปทุมธานี": [14.0171, 100.5333],
            "ประจวบคีรีขันธ์": [11.8030, 99.8000],
            "ปราจีนบุรี": [14.0572, 101.3768],
            "ปัตตานี": [6.8640, 101.2500],
            "พระนครศรีอยุธยา": [14.3588, 100.5684],
            "พะเยา": [19.1707, 99.9083],
            "พังงา": [8.4510, 98.5340],
            "พัทลุง": [7.6150, 100.0810],
            "พิจิตร": [16.4390, 100.3490],
            "พิษณุโลก": [16.8283, 100.2729],
            "เพชรบุรี": [13.1133, 99.9412],
            "เพชรบูรณ์": [16.4190, 101.1590],
            "แพร่": [18.1533, 100.1629],
            "ภูเก็ต": [7.8765, 98.3815],
            "มหาสารคาม": [16.1840, 103.2980],
            "มุกดาหาร": [16.5453, 104.7231],
            "แม่ฮ่องสอน": [19.3010, 97.9690],
            "ยโสธร": [15.7880, 104.1510],
            "ยะลา": [6.5505, 101.2851],
            "ร้อยเอ็ด": [16.0510, 103.6550],
            "ระนอง": [9.9620, 98.6380],
            "ระยอง": [12.6718, 101.2815],
            "ราชบุรี": [13.5419, 99.8215],
            "ลพบุรี": [14.8040, 100.6186],
            "ลำปาง": [18.2916, 99.4813],
            "ลำพูน": [18.5030, 99.0740],
            "เลย": [17.4919, 101.7315],
            "ศรีสะเกษ": [15.1203, 104.3298],
            "สกลนคร": [17.1679, 104.1479],
            "สงขลา": [6.9964, 100.4714],
            "สตูล": [6.6167, 100.0667],
            "สมุทรปราการ": [13.6069, 100.6115],
            "สมุทรสงคราม": [13.4130, 100.0010],
            "สมุทรสาคร": [13.5360, 100.2740],
            "สระแก้ว": [13.6824, 102.4969],
            "สระบุรี": [14.5304, 100.8800],
            "สิงห์บุรี": [14.8870, 100.4010],
            "สุโขทัย": [17.0119, 99.7515],
            "สุพรรณบุรี": [14.4710, 100.1290],
            "สุราษฎร์ธานี": [9.1501, 99.3401],
            "สุรินทร์": [14.8868, 103.4915],
            "หนองคาย": [17.8733, 102.7479],
            "หนองบัวลำภู": [17.2218, 102.4260],
            "อ่างทอง": [14.5833, 100.4500],
            "อำนาจเจริญ": [15.8600, 104.6300],
            "อุดรธานี": [17.4048, 102.7893],
            "อุตรดิตถ์": [17.6316, 100.0972],
            "อุทัยธานี": [15.3819, 100.0264],
            "อุบลราชธานี": [15.2500, 104.8300]
        };

        function createEventMap() {
            // สร้างแผนที่สำหรับงาน Event
            map = L.map('map').setView([13.7563, 100.5018], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([13.7563, 100.5018], {
                draggable: true
            }).addTo(map);
            marker.on('dragend', function(event) {
                updateEventLocation(marker.getLatLng());
            });
            map.on('click', function(event) {
                marker.setLatLng(event.latlng);
                updateEventLocation(event.latlng);
            });
        }

        // สร้างแผนที่สำหรับรับส่งผู้ป่วย
        function createPatientMap() {
            patientMap = L.map('patientMap').setView([13.7563, 100.5018], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(patientMap);

            patientMarker = L.marker([13.7563, 100.5018], {
                draggable: true
            }).addTo(patientMap);

            // เรียก updatePatientLocation เมื่อ Marker ถูกลาก
            patientMarker.on('dragend', function(event) {
                updatePatientLocation(patientMarker.getLatLng());
            });

            // เรียก updatePatientLocation เมื่อคลิกบนแผนที่
            patientMap.on('click', function(event) {
                patientMarker.setLatLng(event.latlng);
                updatePatientLocation(event.latlng);
            });
        }

        // อัปเดตพิกัดสถานที่งาน event
        function updateEventLocation(position) {
            let lat = position.lat.toFixed(6);
            let lon = position.lng.toFixed(6);
            document.getElementById('place_event_location').value = `${lat},${lon}`;
            calculatePrice(); // คำนวณราคาใหม่เมื่อพิกัดเปลี่ยน
        }

        // อัปเดตพิกัดสถานที่รับส่งผู้ป่วย
        function updatePatientLocation(position) {
            const lat = position.lat.toFixed(6);
            const lon = position.lng.toFixed(6);

            // อัปเดตค่าพิกัดใน input hidden
            document.getElementById('pickup-location').value = `${lat},${lon}`;

            // แสดงพิกัดใน UI (ถ้าต้องการ)
            const locationDisplay = document.getElementById('patientLocationDisplay');
            if (locationDisplay) {
                locationDisplay.innerText = `ละติจูด: ${lat}, ลองจิจูด: ${lon}`;
            }

            // เรียกฟังก์ชันคำนวณราคาใหม่
            calculatePatientPrice();
        }

        // อัปเดตแผนที่ตามจังหวัดที่เลือก
        function updateMapByProvince(type) {
            let provinceDropdown = document.getElementById(type === 'event' ? 'province1' : 'province2');
            let province = provinceDropdown.value;

            if (provinceCoordinates[province]) {
                let newLatLng = provinceCoordinates[province];

                if (type === 'event') {
                    map.setView(newLatLng, 12);
                    marker.setLatLng(newLatLng);
                    document.getElementById('place_event_location').value = `${newLatLng[0]},${newLatLng[1]}`;
                    calculatePrice(); // คำนวณราคาใหม่เมื่อพิกัดเปลี่ยน
                } else if (type === 'patient') {
                    patientMap.setView(newLatLng, 12);
                    patientMarker.setLatLng(newLatLng);
                    document.getElementById('pickup-location').value = `${newLatLng[0]},${newLatLng[1]}`;
                    calculatePatientPrice(); // คำนวณราคาใหม่เมื่อพิกัดเปลี่ยน
                }
            }
        }

        // ฟังก์ชันค้นหาสถานที่
        function searchLocation(inputId, type) {
            event.preventDefault(); // ป้องกันการส่งฟอร์ม

            let locationName = document.getElementById(inputId).value;
            if (!locationName) {
                alert("กรุณากรอกชื่อสถานที่");
                return;
            }

            let url = `https://nominatim.openstreetmap.org/search?format=json&countrycodes=TH&q=${encodeURIComponent(locationName)}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let lat = parseFloat(data[0].lat);
                        let lon = parseFloat(data[0].lon);

                        if (type === 'event') {
                            map.setView([lat, lon], 15);
                            marker.setLatLng([lat, lon]);
                            updateEventLocation({
                                lat,
                                lng: lon
                            });
                        } else if (type === 'patient') {
                            patientMap.setView([lat, lon], 15);
                            patientMarker.setLatLng([lat, lon]);
                            updatePatientLocation({
                                lat,
                                lng: lon
                            });
                        }
                    } else {
                        alert("ไม่พบสถานที่ที่ค้นหา");
                    }
                })
                .catch(error => {
                    console.error("Error fetching location:", error);
                });
        }

        // เปลี่ยนแผนที่ตามฟอร์มที่เลือก
        document.getElementById('formSelect').addEventListener('change', function() {
            let selectedForm = this.value;
            if (selectedForm === 'form1') {
                document.getElementById('form1').classList.add('active');
                document.getElementById('form2').classList.remove('active');
                createEventMap();
            } else if (selectedForm === 'form2') {
                document.getElementById('form2').classList.add('active');
                document.getElementById('form1').classList.remove('active');
                createPatientMap();
            }
        });

        // เริ่มต้นให้แสดงแผนที่สำหรับ Event
        window.onload = function() {
            document.getElementById('form1').classList.add('active');
            createEventMap();
        };
    </script>


    <script>
        const bangkokCoordinates = [13.7563, 100.5018];
        const fuelCostPerKm = 3.5;

        const vehicleLevelPrices = {
            "1": 600,
            "2": 1000,
            "3": 1400
        };

        const nursePrice = 100;
        const eventMultiplier = 1.5;
        const vat = 0.07; // VAT 7%


        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function calculatePrice() {
            const eventLocation = document.getElementById('place_event_location').value;
            if (!eventLocation) {
                return;
            }

            const [lat, lon] = eventLocation.split(',').map(coord => parseFloat(coord));
            const distance = calculateDistance(bangkokCoordinates[0], bangkokCoordinates[1], lat, lon);
            const distanceCost = distance * fuelCostPerKm;

            const selectedVehicleLevel = document.querySelector('input[name="level"]:checked');
            if (!selectedVehicleLevel) {
                return;
            }

            const vehicleLevel = selectedVehicleLevel.value;
            const vehicleLevelCost = vehicleLevelPrices[vehicleLevel];

            const nurseCount = parseInt(document.getElementById("nurse_number").value) || 0;
            const ambulanceCount = parseInt(document.getElementById("ambulance_number").value) || 0;

            const ambulanceCost = ambulanceCount * vehicleLevelCost;
            const nurseCost = nurseCount * nursePrice;

            const extraCost = (nurseCount * nursePrice * ambulanceCount) + ambulanceCost;
            const subtotal = (distanceCost + extraCost) * eventMultiplier; // ราคารวมก่อน VAT
            const vatAmount = subtotal * vat; // คำนวณ VAT 7%
            const totalPrice = subtotal + vatAmount; // ราคารวมหลัง VAT
            // อัปเดตค่าระยะทางในฟิลด์ hidden
            document.getElementById("calculatedDistance1").value = distance.toFixed(2);
            // สร้างข้อความรายละเอียดการคำนวณ
            const details = `
                ระยะทาง: ${distance.toFixed(2)} กม. ค่าน้ำมัน: 5.25 บาท/กม.<br>
            `;

            // แสดงผลในหน้าเว็บ
            document.getElementById("priceDisplay1").innerHTML = `
                <small>${details}</small>
                ยอดชำระทั้งหมด: ${totalPrice.toFixed(2)} บาท<br>
            `;
            document.getElementById("calculatedPrice1").value = totalPrice.toFixed(2);


        }

        function updateMapByProvince(type) {
            let provinceDropdown = document.getElementById(type === 'event' ? 'province1' : 'province2');
            let province = provinceDropdown.value;

            if (provinceCoordinates[province]) {
                let newLatLng = provinceCoordinates[province];

                if (type === 'event') {
                    map.setView(newLatLng, 12);
                    marker.setLatLng(newLatLng);
                    document.getElementById('place_event_location').value = `${newLatLng[0]},${newLatLng[1]}`;
                    calculatePrice();
                } else if (type === 'patient') {
                    patientMap.setView(newLatLng, 12);
                    patientMarker.setLatLng(newLatLng);
                    document.getElementById('pickup-location').value = `${newLatLng[0]},${newLatLng[1]}`;
                    calculatePatientPrice();
                }
            }
        }

        function updateEventLocation(position) {
            const lat = position.lat.toFixed(6);
            const lon = position.lng.toFixed(6);
            document.getElementById('place_event_location').value = `${lat},${lon}`;
            calculatePrice();
        }

        document.addEventListener("DOMContentLoaded", function() {
            calculatePrice();
        });
    </script>

    <script>
        // ข้อมูลโรงพยาบาลพร้อมละติจูดและลองจิจูด
        const hospitalsByProvince = {
            "กรุงเทพมหานคร": [{
                    name: "โรงพยาบาลรามาธิบดี",
                    lat: 13.7659,
                    lon: 100.5250
                },
                {
                    name: "โรงพยาบาลกรุงเทพ",
                    lat: 13.7563,
                    lon: 100.5018
                },
                {
                    name: "โรงพยาบาลมะเร็งกรุงเทพ",
                    lat: 13.7804,
                    lon: 100.5460
                },
                {
                    name: "โรงพยาบาลจุฬาลงกรณ์",
                    lat: 13.7461,
                    lon: 100.5345
                },
                {
                    name: "โรงพยาบาลศิริราช",
                    lat: 13.7594,
                    lon: 100.4889
                }
            ],
            "เชียงใหม่": [{
                    name: "โรงพยาบาลมหาราชนครเชียงใหม่",
                    lat: 18.7883,
                    lon: 98.9853
                },
                {
                    name: "โรงพยาบาลลานนา",
                    lat: 18.7961,
                    lon: 98.9786
                },
                {
                    name: "โรงพยาบาลเชียงใหม่ราม",
                    lat: 18.7891,
                    lon: 98.9785
                },
                {
                    name: "โรงพยาบาลนครพิงค์",
                    lat: 18.8479,
                    lon: 98.9936
                },
                {
                    name: "โรงพยาบาลสวนดอก",
                    lat: 18.7896,
                    lon: 98.9743
                }
            ],
            "กาญจนบุรี": [{
                    name: "โรงพยาบาลกาญจนบุรี",
                    lat: 14.0227,
                    lon: 99.5320
                },
                {
                    name: "โรงพยาบาลพหลพลพยุหเสนา",
                    lat: 14.0221,
                    lon: 99.5411
                },
                {
                    name: "โรงพยาบาลมะการักษ์",
                    lat: 13.9866,
                    lon: 99.6157
                },
                {
                    name: "โรงพยาบาลท่าม่วง",
                    lat: 13.9739,
                    lon: 99.6100
                },
                {
                    name: "โรงพยาบาลทองผาภูมิ",
                    lat: 14.6855,
                    lon: 98.6375
                }
            ],
            "กาฬสินธุ์": [{
                    name: "โรงพยาบาลกาฬสินธุ์",
                    lat: 16.4322,
                    lon: 103.5068
                },
                {
                    name: "โรงพยาบาลสมเด็จพระยุพราชกุฉินารายณ์",
                    lat: 16.6637,
                    lon: 104.0073
                },
                {
                    name: "โรงพยาบาลฆ้องชัย",
                    lat: 16.2161,
                    lon: 103.5817
                },
                {
                    name: "โรงพยาบาลคำม่วง",
                    lat: 16.8381,
                    lon: 103.3360
                },
                {
                    name: "โรงพยาบาลยางตลาด",
                    lat: 16.3431,
                    lon: 103.2955
                }
            ],
            "กำแพงเพชร": [{
                    name: "โรงพยาบาลกำแพงเพชร",
                    lat: 16.4721,
                    lon: 99.5386
                },
                {
                    name: "โรงพยาบาลคลองขลุง",
                    lat: 16.4160,
                    lon: 99.6074
                },
                {
                    name: "โรงพยาบาลพรานกระต่าย",
                    lat: 16.7330,
                    lon: 99.6166
                },
                {
                    name: "โรงพยาบาลโกสัมพีนคร",
                    lat: 16.8111,
                    lon: 99.5253
                },
                {
                    name: "โรงพยาบาลลานกระบือ",
                    lat: 16.7512,
                    lon: 99.8575
                }
            ],
            "ขอนแก่น": [{
                    name: "โรงพยาบาลศรีนครินทร์",
                    lat: 16.4725,
                    lon: 102.8221
                },
                {
                    name: "โรงพยาบาลขอนแก่น",
                    lat: 16.4322,
                    lon: 102.8340
                },
                {
                    name: "โรงพยาบาลกรุงเทพขอนแก่น",
                    lat: 16.4422,
                    lon: 102.8618
                },
                {
                    name: "โรงพยาบาลน้ำพอง",
                    lat: 16.7063,
                    lon: 102.7913
                },
                {
                    name: "โรงพยาบาลชนบท",
                    lat: 16.4817,
                    lon: 102.4790
                }
            ],
            "จันทบุรี": [{
                    name: "โรงพยาบาลพระปกเกล้า",
                    lat: 12.6101,
                    lon: 102.1026
                },
                {
                    name: "โรงพยาบาลกรุงเทพจันทบุรี",
                    lat: 12.6035,
                    lon: 102.1067
                },
                {
                    name: "โรงพยาบาลแหลมสิงห์",
                    lat: 12.4572,
                    lon: 102.0772
                },
                {
                    name: "โรงพยาบาลมะขาม",
                    lat: 12.7293,
                    lon: 102.1521
                },
                {
                    name: "โรงพยาบาลขลุง",
                    lat: 12.5717,
                    lon: 102.2833
                }
            ],
            "ฉะเชิงเทรา": [{
                    name: "โรงพยาบาลพุทธโสธร",
                    lat: 13.6889,
                    lon: 101.0774
                },
                {
                    name: "โรงพยาบาลพนมสารคาม",
                    lat: 13.7730,
                    lon: 101.2864
                },
                {
                    name: "โรงพยาบาลแปลงยาว",
                    lat: 13.6164,
                    lon: 101.4174
                },
                {
                    name: "โรงพยาบาลบางคล้า",
                    lat: 13.6746,
                    lon: 101.1820
                },
                {
                    name: "โรงพยาบาลบางน้ำเปรี้ยว",
                    lat: 13.8315,
                    lon: 100.9751
                }
            ],
            "ชลบุรี": [{
                    name: "โรงพยาบาลชลบุรี",
                    lat: 13.3611,
                    lon: 100.9836
                },
                {
                    name: "โรงพยาบาลบางละมุง",
                    lat: 12.9466,
                    lon: 100.8912
                },
                {
                    name: "โรงพยาบาลพานทอง",
                    lat: 13.4482,
                    lon: 101.0911
                },
                {
                    name: "โรงพยาบาลแหลมฉบัง",
                    lat: 13.0935,
                    lon: 100.9153
                },
                {
                    name: "โรงพยาบาลสมิติเวชศรีราชา",
                    lat: 13.1725,
                    lon: 100.9308
                }
            ],
            "ชัยนาท": [{
                    name: "โรงพยาบาลชัยนาทนเรนทร",
                    lat: 15.1868,
                    lon: 100.1269
                },
                {
                    name: "โรงพยาบาลสรรคบุรี",
                    lat: 15.0375,
                    lon: 100.1921
                },
                {
                    name: "โรงพยาบาลมโนรมย์",
                    lat: 15.1146,
                    lon: 100.2458
                },
                {
                    name: "โรงพยาบาลหันคา",
                    lat: 15.0649,
                    lon: 99.9683
                },
                {
                    name: "โรงพยาบาลวัดสิงห์",
                    lat: 15.3022,
                    lon: 100.1215
                }
            ],
            "ชัยภูมิ": [{
                    name: "โรงพยาบาลชัยภูมิ",
                    lat: 15.7982,
                    lon: 102.0312
                },
                {
                    name: "โรงพยาบาลบ้านเขว้า",
                    lat: 15.6370,
                    lon: 102.0564
                },
                {
                    name: "โรงพยาบาลภูเขียวเฉลิมพระเกียรติ",
                    lat: 16.3795,
                    lon: 101.9536
                },
                {
                    name: "โรงพยาบาลจัตุรัส",
                    lat: 15.6061,
                    lon: 101.9603
                },
                {
                    name: "โรงพยาบาลแก้งคร้อ",
                    lat: 16.1502,
                    lon: 102.2719
                }
            ],
            "ชุมพร": [{
                    name: "โรงพยาบาลชุมพรเขตรอุดมศักดิ์",
                    lat: 10.4930,
                    lon: 99.1805
                },
                {
                    name: "โรงพยาบาลหลังสวน",
                    lat: 9.9520,
                    lon: 99.0744
                },
                {
                    name: "โรงพยาบาลท่าแซะ",
                    lat: 10.5203,
                    lon: 99.2034
                },
                {
                    name: "โรงพยาบาลละแม",
                    lat: 9.8237,
                    lon: 99.0935
                },
                {
                    name: "โรงพยาบาลพะโต๊ะ",
                    lat: 9.5650,
                    lon: 98.8086
                }
            ],
            "เชียงราย": [{
                    name: "โรงพยาบาลเชียงรายประชานุเคราะห์",
                    lat: 19.9034,
                    lon: 99.8331
                },
                {
                    name: "โรงพยาบาลแม่สาย",
                    lat: 20.4339,
                    lon: 99.8892
                },
                {
                    name: "โรงพยาบาลเทิง",
                    lat: 19.5956,
                    lon: 100.1613
                },
                {
                    name: "โรงพยาบาลพาน",
                    lat: 19.4677,
                    lon: 99.7713
                },
                {
                    name: "โรงพยาบาลเวียงแก่น",
                    lat: 19.7574,
                    lon: 100.4724
                }
            ],
            "ตรัง": [{
                    name: "โรงพยาบาลตรัง",
                    lat: 7.5564,
                    lon: 99.6081
                },
                {
                    name: "โรงพยาบาลวัฒนแพทย์ตรัง",
                    lat: 7.5545,
                    lon: 99.6123
                },
                {
                    name: "โรงพยาบาลกันตัง",
                    lat: 7.4081,
                    lon: 99.4952
                },
                {
                    name: "โรงพยาบาลย่านตาขาว",
                    lat: 7.3953,
                    lon: 99.6502
                },
                {
                    name: "โรงพยาบาลนาโยง",
                    lat: 7.6068,
                    lon: 99.6836
                }
            ],
            "ตราด": [{
                    name: "โรงพยาบาลตราด",
                    lat: 12.2423,
                    lon: 102.5106
                },
                {
                    name: "โรงพยาบาลแหลมงอบ",
                    lat: 11.8987,
                    lon: 102.0434
                },
                {
                    name: "โรงพยาบาลเขาสมิง",
                    lat: 12.3217,
                    lon: 102.3350
                },
                {
                    name: "โรงพยาบาลบ่อไร่",
                    lat: 12.5265,
                    lon: 102.4505
                },
                {
                    name: "โรงพยาบาลคลองใหญ่",
                    lat: 11.7824,
                    lon: 102.8640
                }
            ],
            "ตาก": [{
                    name: "โรงพยาบาลตาก",
                    lat: 16.8838,
                    lon: 98.5536
                },
                {
                    name: "โรงพยาบาลแม่สอด",
                    lat: 16.7152,
                    lon: 98.5703
                },
                {
                    name: "โรงพยาบาลบ้านตาก",
                    lat: 17.0411,
                    lon: 99.0336
                },
                {
                    name: "โรงพยาบาลสามเงา",
                    lat: 17.1194,
                    lon: 99.0226
                },
                {
                    name: "โรงพยาบาลอุ้มผาง",
                    lat: 15.9987,
                    lon: 98.8673
                }
            ],
            "นครนายก": [{
                    name: "โรงพยาบาลนครนายก",
                    lat: 14.2123,
                    lon: 101.2130
                },
                {
                    name: "โรงพยาบาลองครักษ์",
                    lat: 14.1160,
                    lon: 100.9765
                },
                {
                    name: "โรงพยาบาลบ้านนา",
                    lat: 14.2992,
                    lon: 101.0961
                },
                {
                    name: "โรงพยาบาลศรีนาวา",
                    lat: 14.2056,
                    lon: 101.2522
                },
                {
                    name: "โรงพยาบาลพระอาจารย์ฝั้น อาจาโร",
                    lat: 14.1839,
                    lon: 101.1923
                }
            ],
            "นครปฐม": [{
                    name: "โรงพยาบาลนครปฐม",
                    lat: 13.8203,
                    lon: 100.0619
                },
                {
                    name: "โรงพยาบาลศูนย์การแพทย์มหิดล",
                    lat: 13.7954,
                    lon: 100.0371
                },
                {
                    name: "โรงพยาบาลสามพราน",
                    lat: 13.7194,
                    lon: 100.2317
                },
                {
                    name: "โรงพยาบาลกำแพงแสน",
                    lat: 14.0202,
                    lon: 99.9584
                },
                {
                    name: "โรงพยาบาลดอนตูม",
                    lat: 13.9636,
                    lon: 99.9505
                }
            ],
            "นครพนม": [{
                    name: "โรงพยาบาลนครพนม",
                    lat: 17.3967,
                    lon: 104.7701
                },
                {
                    name: "โรงพยาบาลเรณูนคร",
                    lat: 17.0450,
                    lon: 104.6308
                },
                {
                    name: "โรงพยาบาลปลาปาก",
                    lat: 17.0358,
                    lon: 104.7057
                },
                {
                    name: "โรงพยาบาลท่าอุเทน",
                    lat: 17.6652,
                    lon: 104.6889
                },
                {
                    name: "โรงพยาบาลบ้านแพง",
                    lat: 17.9734,
                    lon: 104.2444
                }
            ],
            "นครราชสีมา": [{
                    name: "โรงพยาบาลมหาราชนครราชสีมา",
                    lat: 14.9737,
                    lon: 102.0830
                },
                {
                    name: "โรงพยาบาลเซนต์เมรี่",
                    lat: 14.9754,
                    lon: 102.0897
                },
                {
                    name: "โรงพยาบาลกรุงเทพราชสีมา",
                    lat: 14.9639,
                    lon: 102.0618
                },
                {
                    name: "โรงพยาบาลค่ายสุรนารี",
                    lat: 14.9546,
                    lon: 102.0387
                },
                {
                    name: "โรงพยาบาลปักธงชัย",
                    lat: 14.7093,
                    lon: 102.0047
                }
            ],
            "นครศรีธรรมราช": [{
                    name: "โรงพยาบาลมหาราชนครศรีธรรมราช",
                    lat: 8.4299,
                    lon: 99.9634
                },
                {
                    name: "โรงพยาบาลท่าศาลา",
                    lat: 8.6211,
                    lon: 99.9173
                },
                {
                    name: "โรงพยาบาลสิชล",
                    lat: 9.0015,
                    lon: 99.8230
                },
                {
                    name: "โรงพยาบาลปากพนัง",
                    lat: 8.3604,
                    lon: 100.1980
                },
                {
                    name: "โรงพยาบาลหัวไทร",
                    lat: 8.3964,
                    lon: 100.4787
                }
            ],
            "นครสวรรค์": [{
                    name: "โรงพยาบาลสวรรค์ประชารักษ์",
                    lat: 15.6985,
                    lon: 100.1175
                },
                {
                    name: "โรงพยาบาลพระนารายณ์มหาราช",
                    lat: 15.6867,
                    lon: 100.1022
                },
                {
                    name: "โรงพยาบาลชุมแสง",
                    lat: 15.9145,
                    lon: 100.2968
                },
                {
                    name: "โรงพยาบาลตาคลี",
                    lat: 15.2706,
                    lon: 100.3447
                },
                {
                    name: "โรงพยาบาลหนองบัว",
                    lat: 15.6873,
                    lon: 99.9730
                }
            ],
            "นนทบุรี": [{
                    name: "โรงพยาบาลพระนั่งเกล้า",
                    lat: 13.8566,
                    lon: 100.5012
                },
                {
                    name: "โรงพยาบาลบางกรวย",
                    lat: 13.8032,
                    lon: 100.4703
                },
                {
                    name: "โรงพยาบาลบางใหญ่",
                    lat: 13.8133,
                    lon: 100.3680
                },
                {
                    name: "โรงพยาบาลบางบัวทอง",
                    lat: 13.9022,
                    lon: 100.4090
                },
                {
                    name: "โรงพยาบาลเกษมราษฎร์ รัตนาธิเบศร์",
                    lat: 13.8754,
                    lon: 100.4649
                }
            ],
            "นราธิวาส": [{
                    name: "โรงพยาบาลนราธิวาสราชนครินทร์",
                    lat: 6.4267,
                    lon: 101.8232
                },
                {
                    name: "โรงพยาบาลสุไหงโก-ลก",
                    lat: 6.0265,
                    lon: 101.9708
                },
                {
                    name: "โรงพยาบาลระแงะ",
                    lat: 6.2947,
                    lon: 101.7332
                },
                {
                    name: "โรงพยาบาลรือเสาะ",
                    lat: 6.5377,
                    lon: 101.6423
                },
                {
                    name: "โรงพยาบาลบาเจาะ",
                    lat: 6.4463,
                    lon: 101.6314
                }
            ],
            "น่าน": [{
                    name: "โรงพยาบาลน่าน",
                    lat: 18.7848,
                    lon: 100.7803
                },
                {
                    name: "โรงพยาบาลเวียงสา",
                    lat: 18.6036,
                    lon: 100.7064
                },
                {
                    name: "โรงพยาบาลท่าวังผา",
                    lat: 19.1594,
                    lon: 100.8116
                },
                {
                    name: "โรงพยาบาลปัว",
                    lat: 19.1715,
                    lon: 100.9167
                },
                {
                    name: "โรงพยาบาลบ้านหลวง",
                    lat: 18.7626,
                    lon: 100.3731
                }
            ],
            "บึงกาฬ": [{
                    name: "โรงพยาบาลบึงกาฬ",
                    lat: 18.3609,
                    lon: 103.6466
                },
                {
                    name: "โรงพยาบาลพรเจริญ",
                    lat: 18.2311,
                    lon: 103.7096
                },
                {
                    name: "โรงพยาบาลปากคาด",
                    lat: 18.3026,
                    lon: 103.9174
                },
                {
                    name: "โรงพยาบาลเซกา",
                    lat: 17.9732,
                    lon: 103.8811
                },
                {
                    name: "โรงพยาบาลศรีวิไล",
                    lat: 18.0155,
                    lon: 103.6713
                }
            ],
            "บุรีรัมย์": [{
                    name: "โรงพยาบาลบุรีรัมย์",
                    lat: 14.9945,
                    lon: 103.1036
                },
                {
                    name: "โรงพยาบาลคูเมือง",
                    lat: 15.1083,
                    lon: 103.0629
                },
                {
                    name: "โรงพยาบาลกระสัง",
                    lat: 15.2312,
                    lon: 103.2687
                },
                {
                    name: "โรงพยาบาลนางรอง",
                    lat: 14.6483,
                    lon: 102.6980
                },
                {
                    name: "โรงพยาบาลละหานทราย",
                    lat: 14.3197,
                    lon: 102.9635
                }
            ],
            "ปทุมธานี": [{
                    name: "โรงพยาบาลปทุมธานี",
                    lat: 14.0215,
                    lon: 100.5251
                },
                {
                    name: "โรงพยาบาลลาดหลุมแก้ว",
                    lat: 14.0820,
                    lon: 100.3264
                },
                {
                    name: "โรงพยาบาลธัญบุรี",
                    lat: 14.0052,
                    lon: 100.7415
                },
                {
                    name: "โรงพยาบาลคลองหลวง",
                    lat: 14.0864,
                    lon: 100.6167
                },
                {
                    name: "โรงพยาบาลบางปะกอก รังสิต 2",
                    lat: 14.0366,
                    lon: 100.6710
                }
            ],
            "ประจวบคีรีขันธ์": [{
                    name: "โรงพยาบาลประจวบคีรีขันธ์",
                    lat: 11.8116,
                    lon: 99.7977
                },
                {
                    name: "โรงพยาบาลหัวหิน",
                    lat: 12.5691,
                    lon: 99.9577
                },
                {
                    name: "โรงพยาบาลบางสะพาน",
                    lat: 11.1966,
                    lon: 99.5052
                },
                {
                    name: "โรงพยาบาลกุยบุรี",
                    lat: 11.3509,
                    lon: 99.6734
                },
                {
                    name: "โรงพยาบาลทับสะแก",
                    lat: 11.3087,
                    lon: 99.4997
                }
            ],
            "ปราจีนบุรี": [{
                    name: "โรงพยาบาลเจ้าพระยาอภัยภูเบศร",
                    lat: 14.0457,
                    lon: 101.3711
                },
                {
                    name: "โรงพยาบาลปราจีนบุรี",
                    lat: 14.0424,
                    lon: 101.3641
                },
                {
                    name: "โรงพยาบาลกบินทร์บุรี",
                    lat: 13.9761,
                    lon: 101.7664
                },
                {
                    name: "โรงพยาบาลบ้านสร้าง",
                    lat: 13.9700,
                    lon: 101.2339
                },
                {
                    name: "โรงพยาบาลนาดี",
                    lat: 14.1374,
                    lon: 101.8990
                }
            ],
            "ปัตตานี": [{
                    name: "โรงพยาบาลปัตตานี",
                    lat: 6.8698,
                    lon: 101.2503
                },
                {
                    name: "โรงพยาบาลยะรัง",
                    lat: 6.6993,
                    lon: 101.3116
                },
                {
                    name: "โรงพยาบาลหนองจิก",
                    lat: 6.8627,
                    lon: 101.1394
                },
                {
                    name: "โรงพยาบาลโคกโพธิ์",
                    lat: 6.7153,
                    lon: 101.0174
                },
                {
                    name: "โรงพยาบาลสายบุรี",
                    lat: 6.7080,
                    lon: 101.6112
                }
            ],
            "พระนครศรีอยุธยา": [{
                    name: "โรงพยาบาลพระนครศรีอยุธยา",
                    lat: 14.3513,
                    lon: 100.5682
                },
                {
                    name: "โรงพยาบาลเสนา",
                    lat: 14.3169,
                    lon: 100.2773
                },
                {
                    name: "โรงพยาบาลบางปะอิน",
                    lat: 14.2151,
                    lon: 100.5910
                },
                {
                    name: "โรงพยาบาลบางบาล",
                    lat: 14.4791,
                    lon: 100.3871
                },
                {
                    name: "โรงพยาบาลมหาราช",
                    lat: 14.4800,
                    lon: 100.7656
                }
            ],
            "พะเยา": [{
                    name: "โรงพยาบาลพะเยา",
                    lat: 19.1766,
                    lon: 99.9042
                },
                {
                    name: "โรงพยาบาลเชียงคำ",
                    lat: 19.5373,
                    lon: 100.3010
                },
                {
                    name: "โรงพยาบาลจุน",
                    lat: 19.2617,
                    lon: 100.1226
                },
                {
                    name: "โรงพยาบาลดอกคำใต้",
                    lat: 19.1647,
                    lon: 99.9210
                },
                {
                    name: "โรงพยาบาลปง",
                    lat: 18.8289,
                    lon: 100.1602
                }
            ],
            "พังงา": [{
                    name: "โรงพยาบาลพังงา",
                    lat: 8.4482,
                    lon: 98.5381
                },
                {
                    name: "โรงพยาบาลท้ายเหมือง",
                    lat: 8.3062,
                    lon: 98.2684
                },
                {
                    name: "โรงพยาบาลตะกั่วป่า",
                    lat: 8.8645,
                    lon: 98.3586
                },
                {
                    name: "โรงพยาบาลคุระบุรี",
                    lat: 9.1881,
                    lon: 98.2782
                },
                {
                    name: "โรงพยาบาลเกาะยาว",
                    lat: 7.9800,
                    lon: 98.5823
                }
            ],
            "พัทลุง": [{
                    name: "โรงพยาบาลพัทลุง",
                    lat: 7.6180,
                    lon: 100.0774
                },
                {
                    name: "โรงพยาบาลควนขนุน",
                    lat: 7.7611,
                    lon: 100.0936
                },
                {
                    name: "โรงพยาบาลศรีบรรพต",
                    lat: 7.5320,
                    lon: 99.9737
                },
                {
                    name: "โรงพยาบาลบางแก้ว",
                    lat: 7.7072,
                    lon: 99.9264
                },
                {
                    name: "โรงพยาบาลศรีนครินทร์",
                    lat: 7.4821,
                    lon: 99.9624
                }
            ],
            "พิจิตร": [{
                    name: "โรงพยาบาลพิจิตร",
                    lat: 16.4335,
                    lon: 100.3507
                },
                {
                    name: "โรงพยาบาลสามง่าม",
                    lat: 16.5229,
                    lon: 100.3277
                },
                {
                    name: "โรงพยาบาลโพทะเล",
                    lat: 16.1477,
                    lon: 100.2945
                },
                {
                    name: "โรงพยาบาลบึงนาราง",
                    lat: 16.2707,
                    lon: 100.3802
                },
                {
                    name: "โรงพยาบาลวังทรายพูน",
                    lat: 16.2225,
                    lon: 100.2411
                }
            ],
            "พิษณุโลก": [{
                    name: "โรงพยาบาลพุทธชินราช",
                    lat: 16.8214,
                    lon: 100.2652
                },
                {
                    name: "โรงพยาบาลสมเด็จพระยุพราชนครไทย",
                    lat: 17.1166,
                    lon: 100.7711
                },
                {
                    name: "โรงพยาบาลบางกระทุ่ม",
                    lat: 16.5465,
                    lon: 100.1932
                },
                {
                    name: "โรงพยาบาลวังทอง",
                    lat: 16.6140,
                    lon: 100.3196
                },
                {
                    name: "โรงพยาบาลเนินมะปราง",
                    lat: 16.6490,
                    lon: 100.0266
                }
            ],
            "เพชรบุรี": [{
                    name: "โรงพยาบาลพระจอมเกล้า",
                    lat: 13.1074,
                    lon: 99.9406
                },
                {
                    name: "โรงพยาบาลบ้านลาด",
                    lat: 13.0481,
                    lon: 99.7915
                },
                {
                    name: "โรงพยาบาลเขาย้อย",
                    lat: 13.1989,
                    lon: 99.7713
                },
                {
                    name: "โรงพยาบาลชะอำ",
                    lat: 12.7997,
                    lon: 99.9700
                },
                {
                    name: "โรงพยาบาลหนองหญ้าปล้อง",
                    lat: 13.0430,
                    lon: 99.5234
                }
            ],
            "เพชรบูรณ์": [{
                    name: "โรงพยาบาลเพชรบูรณ์",
                    lat: 16.4191,
                    lon: 101.1586
                },
                {
                    name: "โรงพยาบาลหล่มสัก",
                    lat: 16.7924,
                    lon: 101.2474
                },
                {
                    name: "โรงพยาบาลวิเชียรบุรี",
                    lat: 15.6610,
                    lon: 101.1011
                },
                {
                    name: "โรงพยาบาลหล่มเก่า",
                    lat: 16.8973,
                    lon: 101.1409
                },
                {
                    name: "โรงพยาบาลบึงสามพัน",
                    lat: 15.7760,
                    lon: 101.0935
                }
            ],
            "แพร่": [{
                    name: "โรงพยาบาลแพร่",
                    lat: 18.1345,
                    lon: 100.1416
                },
                {
                    name: "โรงพยาบาลลอง",
                    lat: 18.1022,
                    lon: 99.8765
                },
                {
                    name: "โรงพยาบาลร้องกวาง",
                    lat: 18.2973,
                    lon: 100.1943
                },
                {
                    name: "โรงพยาบาลสอง",
                    lat: 18.3665,
                    lon: 100.2387
                },
                {
                    name: "โรงพยาบาลวังชิ้น",
                    lat: 17.8821,
                    lon: 99.9472
                }
            ],
            "ภูเก็ต": [{
                    name: "โรงพยาบาลวชิระภูเก็ต",
                    lat: 7.8804,
                    lon: 98.3856
                },
                {
                    name: "โรงพยาบาลถลาง",
                    lat: 8.0498,
                    lon: 98.3122
                },
                {
                    name: "โรงพยาบาลป่าตอง",
                    lat: 7.8966,
                    lon: 98.2950
                },
                {
                    name: "โรงพยาบาลมิชชั่นภูเก็ต",
                    lat: 7.8802,
                    lon: 98.3936
                },
                {
                    name: "โรงพยาบาลกรุงเทพภูเก็ต",
                    lat: 7.8881,
                    lon: 98.3746
                }
            ],
            "มหาสารคาม": [{
                    name: "โรงพยาบาลมหาสารคาม",
                    lat: 16.1840,
                    lon: 103.2980
                },
                {
                    name: "โรงพยาบาลกันทรวิชัย",
                    lat: 16.2575,
                    lon: 103.2071
                },
                {
                    name: "โรงพยาบาลบรบือ",
                    lat: 15.9962,
                    lon: 103.1244
                },
                {
                    name: "โรงพยาบาลเชียงยืน",
                    lat: 16.3963,
                    lon: 103.0674
                },
                {
                    name: "โรงพยาบาลโกสุมพิสัย",
                    lat: 16.2451,
                    lon: 103.0743
                }
            ],
            "มุกดาหาร": [{
                    name: "โรงพยาบาลมุกดาหาร",
                    lat: 16.5453,
                    lon: 104.7231
                },
                {
                    name: "โรงพยาบาลนิคมคำสร้อย",
                    lat: 16.6766,
                    lon: 104.5423
                },
                {
                    name: "โรงพยาบาลดอนตาล",
                    lat: 16.3262,
                    lon: 104.8604
                },
                {
                    name: "โรงพยาบาลคำชะอี",
                    lat: 16.5612,
                    lon: 104.4325
                },
                {
                    name: "โรงพยาบาลหว้านใหญ่",
                    lat: 16.7092,
                    lon: 104.8315
                }
            ],
            "แม่ฮ่องสอน": [{
                    name: "โรงพยาบาลศรีสังวาลย์",
                    lat: 19.3010,
                    lon: 97.9690
                },
                {
                    name: "โรงพยาบาลปาย",
                    lat: 19.3589,
                    lon: 98.4370
                },
                {
                    name: "โรงพยาบาลแม่สะเรียง",
                    lat: 18.1640,
                    lon: 97.9330
                },
                {
                    name: "โรงพยาบาลขุนยวม",
                    lat: 18.8242,
                    lon: 97.9326
                },
                {
                    name: "โรงพยาบาลสบเมย",
                    lat: 17.9185,
                    lon: 97.8573
                }
            ],
            "ยโสธร": [{
                    name: "โรงพยาบาลยโสธร",
                    lat: 15.7880,
                    lon: 104.1510
                },
                {
                    name: "โรงพยาบาลกุดชุม",
                    lat: 16.0062,
                    lon: 104.2403
                },
                {
                    name: "โรงพยาบาลคำเขื่อนแก้ว",
                    lat: 15.6986,
                    lon: 104.2643
                },
                {
                    name: "โรงพยาบาลมหาชนะชัย",
                    lat: 15.6337,
                    lon: 104.3205
                },
                {
                    name: "โรงพยาบาลเลิงนกทา",
                    lat: 16.0184,
                    lon: 104.5533
                }
            ],
            "ยะลา": [{
                    name: "โรงพยาบาลยะลา",
                    lat: 6.5505,
                    lon: 101.2851
                },
                {
                    name: "โรงพยาบาลเบตง",
                    lat: 5.7794,
                    lon: 101.0709
                },
                {
                    name: "โรงพยาบาลรามัน",
                    lat: 6.5477,
                    lon: 101.4476
                },
                {
                    name: "โรงพยาบาลบันนังสตา",
                    lat: 6.2689,
                    lon: 101.1827
                },
                {
                    name: "โรงพยาบาลธารโต",
                    lat: 6.0823,
                    lon: 101.1975
                }
            ],
            "ร้อยเอ็ด": [{
                    name: "โรงพยาบาลร้อยเอ็ด",
                    lat: 16.0510,
                    lon: 103.6550
                },
                {
                    name: "โรงพยาบาลโพนทอง",
                    lat: 16.3011,
                    lon: 103.9784
                },
                {
                    name: "โรงพยาบาลเสลภูมิ",
                    lat: 16.1617,
                    lon: 103.9560
                },
                {
                    name: "โรงพยาบาลสุวรรณภูมิ",
                    lat: 15.6827,
                    lon: 103.7546
                },
                {
                    name: "โรงพยาบาลอาจสามารถ",
                    lat: 15.7407,
                    lon: 103.7852
                }
            ],
            "ระนอง": [{
                    name: "โรงพยาบาลระนอง",
                    lat: 9.9620,
                    lon: 98.6380
                },
                {
                    name: "โรงพยาบาลกระบุรี",
                    lat: 10.4274,
                    lon: 98.9214
                },
                {
                    name: "โรงพยาบาลละอุ่น",
                    lat: 10.1115,
                    lon: 98.7802
                },
                {
                    name: "โรงพยาบาลกะเปอร์",
                    lat: 9.5767,
                    lon: 98.5652
                },
                {
                    name: "โรงพยาบาลสุขสำราญ",
                    lat: 9.4706,
                    lon: 98.5321
                }
            ],
            "ระยอง": [{
                    name: "โรงพยาบาลระยอง",
                    lat: 12.6718,
                    lon: 101.2815
                },
                {
                    name: "โรงพยาบาลบ้านฉาง",
                    lat: 12.7221,
                    lon: 101.0552
                },
                {
                    name: "โรงพยาบาลปลวกแดง",
                    lat: 12.9469,
                    lon: 101.1705
                },
                {
                    name: "โรงพยาบาลแกลง",
                    lat: 12.7779,
                    lon: 101.6517
                },
                {
                    name: "โรงพยาบาลวังจันทร์",
                    lat: 12.8927,
                    lon: 101.4303
                }
            ],
            "ราชบุรี": [{
                    name: "โรงพยาบาลราชบุรี",
                    lat: 13.5419,
                    lon: 99.8215
                },
                {
                    name: "โรงพยาบาลบ้านโป่ง",
                    lat: 13.8192,
                    lon: 99.8839
                },
                {
                    name: "โรงพยาบาลโพธาราม",
                    lat: 13.6935,
                    lon: 99.8493
                },
                {
                    name: "โรงพยาบาลดำเนินสะดวก",
                    lat: 13.5248,
                    lon: 99.8375
                },
                {
                    name: "โรงพยาบาลจอมบึง",
                    lat: 13.5331,
                    lon: 99.4423
                }
            ],
            "ลพบุรี": [{
                    name: "โรงพยาบาลลพบุรี",
                    lat: 14.8040,
                    lon: 100.6186
                },
                {
                    name: "โรงพยาบาลพัฒนานิคม",
                    lat: 14.5284,
                    lon: 100.7893
                },
                {
                    name: "โรงพยาบาลโคกสำโรง",
                    lat: 14.6520,
                    lon: 100.5402
                },
                {
                    name: "โรงพยาบาลท่าหลวง",
                    lat: 14.8053,
                    lon: 100.5860
                },
                {
                    name: "โรงพยาบาลชัยบาดาล",
                    lat: 14.7796,
                    lon: 100.7597
                }
            ],
            "ลำปาง": [{
                    name: "โรงพยาบาลลำปาง",
                    lat: 18.2916,
                    lon: 99.4813
                },
                {
                    name: "โรงพยาบาลเขลางค์นคร",
                    lat: 18.2862,
                    lon: 99.4788
                },
                {
                    name: "โรงพยาบาลเมืองลำปาง",
                    lat: 18.2900,
                    lon: 99.4901
                },
                {
                    name: "โรงพยาบาลงาว",
                    lat: 18.6423,
                    lon: 99.4866
                },
                {
                    name: "โรงพยาบาลห้างฉัตร",
                    lat: 18.3435,
                    lon: 99.4842
                }
            ],
            "ลำพูน": [{
                    name: "โรงพยาบาลลำพูน",
                    lat: 18.5030,
                    lon: 99.0740
                },
                {
                    name: "โรงพยาบาลบ้านธิ",
                    lat: 18.5983,
                    lon: 99.1214
                },
                {
                    name: "โรงพยาบาลทุ่งหัวช้าง",
                    lat: 18.4675,
                    lon: 99.0389
                },
                {
                    name: "โรงพยาบาลป่าซาง",
                    lat: 18.3980,
                    lon: 99.1378
                },
                {
                    name: "โรงพยาบาลห้างฉัตร",
                    lat: 18.3435,
                    lon: 99.4842
                }
            ],
            "เลย": [{
                    name: "โรงพยาบาลเลย",
                    lat: 17.4919,
                    lon: 101.7315
                },
                {
                    name: "โรงพยาบาลด่านซ้าย",
                    lat: 17.4453,
                    lon: 101.4447
                },
                {
                    name: "โรงพยาบาลภูเรือ",
                    lat: 17.3109,
                    lon: 101.2512
                },
                {
                    name: "โรงพยาบาลเชียงคาน",
                    lat: 17.8633,
                    lon: 101.5224
                },
                {
                    name: "โรงพยาบาลนาแห้ว",
                    lat: 17.7077,
                    lon: 101.8021
                }
            ],
            "ศรีสะเกษ": [{
                    name: "โรงพยาบาลศรีสะเกษ",
                    lat: 15.1203,
                    lon: 104.3298
                },
                {
                    name: "โรงพยาบาลขุนหาญ",
                    lat: 15.2057,
                    lon: 104.3613
                },
                {
                    name: "โรงพยาบาลราษีไศล",
                    lat: 15.2155,
                    lon: 104.4217
                },
                {
                    name: "โรงพยาบาลอุทุมพรพิสัย",
                    lat: 15.4460,
                    lon: 104.0804
                },
                {
                    name: "โรงพยาบาลกันทรลักษ์",
                    lat: 15.7012,
                    lon: 104.5074
                }
            ],
            "สกลนคร": [{
                    name: "โรงพยาบาลสกลนคร",
                    lat: 17.1679,
                    lon: 104.1479
                },
                {
                    name: "โรงพยาบาลคำตากล้า",
                    lat: 17.3572,
                    lon: 104.3407
                },
                {
                    name: "โรงพยาบาลกุสุมาลย์",
                    lat: 17.2320,
                    lon: 104.6158
                },
                {
                    name: "โรงพยาบาลพรรณานิคม",
                    lat: 17.1217,
                    lon: 104.1279
                },
                {
                    name: "โรงพยาบาลวารินชำราบ",
                    lat: 17.0584,
                    lon: 104.6431
                }
            ],
            "สงขลา": [{
                    name: "โรงพยาบาลสงขลา",
                    lat: 6.9964,
                    lon: 100.4714
                },
                {
                    name: "โรงพยาบาลหาดใหญ่",
                    lat: 7.0023,
                    lon: 100.4770
                },
                {
                    name: "โรงพยาบาลควนเนียง",
                    lat: 7.2037,
                    lon: 100.3031
                },
                {
                    name: "โรงพยาบาลสะเดา",
                    lat: 6.8550,
                    lon: 100.5985
                },
                {
                    name: "โรงพยาบาลบางกล่ำ",
                    lat: 7.0245,
                    lon: 100.4939
                }
            ],
            "สตูล": [{
                    name: "โรงพยาบาลสตูล",
                    lat: 6.6167,
                    lon: 100.0667
                },
                {
                    name: "โรงพยาบาลละงู",
                    lat: 6.5398,
                    lon: 100.0652
                },
                {
                    name: "โรงพยาบาลทุ่งหว้า",
                    lat: 6.5611,
                    lon: 100.1550
                },
                {
                    name: "โรงพยาบาลมะนาว",
                    lat: 6.6029,
                    lon: 100.1783
                },
                {
                    name: "โรงพยาบาลคลองหอยโข่ง",
                    lat: 6.7374,
                    lon: 100.1982
                }
            ],
            "สมุทรปราการ": [{
                    name: "โรงพยาบาลสมุทรปราการ",
                    lat: 13.6069,
                    lon: 100.6115
                },
                {
                    name: "โรงพยาบาลบางบ่อ",
                    lat: 13.4364,
                    lon: 100.8063
                },
                {
                    name: "โรงพยาบาลบางพลี",
                    lat: 13.5876,
                    lon: 100.6910
                },
                {
                    name: "โรงพยาบาลเทพารักษ์",
                    lat: 13.5885,
                    lon: 100.5920
                },
                {
                    name: "โรงพยาบาลศิครินทร์",
                    lat: 13.6499,
                    lon: 100.6070
                }
            ],
            "สมุทรสงคราม": [{
                    name: "โรงพยาบาลสมุทรสงคราม",
                    lat: 13.4130,
                    lon: 100.0010
                },
                {
                    name: "โรงพยาบาลอัมพวา",
                    lat: 13.4534,
                    lon: 100.0182
                },
                {
                    name: "โรงพยาบาลบางคนที",
                    lat: 13.4628,
                    lon: 99.9912
                },
                {
                    name: "โรงพยาบาลจอมทอง",
                    lat: 13.5003,
                    lon: 100.0694
                },
                {
                    name: "โรงพยาบาลบึงฉวาก",
                    lat: 13.4003,
                    lon: 100.0411
                }
            ],
            "สมุทรสาคร": [{
                    name: "โรงพยาบาลสมุทรสาคร",
                    lat: 13.5360,
                    lon: 100.2740
                },
                {
                    name: "โรงพยาบาลกระทุ่มแบน",
                    lat: 13.6209,
                    lon: 100.3534
                },
                {
                    name: "โรงพยาบาลพนม",
                    lat: 13.6393,
                    lon: 100.2072
                },
                {
                    name: "โรงพยาบาลบางกรวย",
                    lat: 13.6732,
                    lon: 100.2830
                },
                {
                    name: "โรงพยาบาลยางมะตูม",
                    lat: 13.6091,
                    lon: 100.2394
                }
            ],
            "สระแก้ว": [{
                    name: "โรงพยาบาลสระแก้ว",
                    lat: 13.6824,
                    lon: 102.4969
                },
                {
                    name: "โรงพยาบาลอรัญประเทศ",
                    lat: 13.7251,
                    lon: 102.6282
                },
                {
                    name: "โรงพยาบาลคลองหาด",
                    lat: 13.5324,
                    lon: 102.6292
                },
                {
                    name: "โรงพยาบาลตาพระ",
                    lat: 13.4753,
                    lon: 102.6932
                },
                {
                    name: "โรงพยาบาลวังน้ำเย็น",
                    lat: 13.8456,
                    lon: 102.6541
                }
            ],
            "สระบุรี": [{
                    name: "โรงพยาบาลสระบุรี",
                    lat: 14.5304,
                    lon: 100.8800
                },
                {
                    name: "โรงพยาบาลหนองแค",
                    lat: 14.5042,
                    lon: 100.7751
                },
                {
                    name: "โรงพยาบาลบ้านหมอ",
                    lat: 14.4752,
                    lon: 100.9032
                },
                {
                    name: "โรงพยาบาลแก่งคอย",
                    lat: 14.3707,
                    lon: 100.9291
                },
                {
                    name: "โรงพยาบาลพระพุทธบาท",
                    lat: 14.4703,
                    lon: 100.7247
                }
            ],
            "สิงห์บุรี": [{
                    name: "โรงพยาบาลสิงห์บุรี",
                    lat: 14.8870,
                    lon: 100.4010
                },
                {
                    name: "โรงพยาบาลค่ายบางระจัน",
                    lat: 14.8059,
                    lon: 100.4634
                },
                {
                    name: "โรงพยาบาลท่าช้าง",
                    lat: 14.8041,
                    lon: 100.5260
                },
                {
                    name: "โรงพยาบาลบางระจัน",
                    lat: 14.9157,
                    lon: 100.4895
                },
                {
                    name: "โรงพยาบาลพรหมบุรี",
                    lat: 14.8534,
                    lon: 100.2565
                }
            ],
            "สุโขทัย": [{
                    name: "โรงพยาบาลสุโขทัย",
                    lat: 17.0119,
                    lon: 99.7515
                },
                {
                    name: "โรงพยาบาลคีรีมาศ",
                    lat: 17.2082,
                    lon: 99.7094
                },
                {
                    name: "โรงพยาบาลศรีสัชนาลัย",
                    lat: 17.1992,
                    lon: 99.7669
                },
                {
                    name: "โรงพยาบาลทุ่งเสลี่ยม",
                    lat: 17.0141,
                    lon: 99.9063
                },
                {
                    name: "โรงพยาบาลบ้านดุง",
                    lat: 17.0660,
                    lon: 99.6775
                }
            ],
            "สุพรรณบุรี": [{
                    name: "โรงพยาบาลสุพรรณบุรี",
                    lat: 14.4710,
                    lon: 100.1290
                },
                {
                    name: "โรงพยาบาลดอนเจดีย์",
                    lat: 14.3337,
                    lon: 100.0427
                },
                {
                    name: "โรงพยาบาลอู่ทอง",
                    lat: 14.1982,
                    lon: 100.1989
                },
                {
                    name: "โรงพยาบาลศรีประจันต์",
                    lat: 14.4979,
                    lon: 100.1487
                },
                {
                    name: "โรงพยาบาลบางปลาม้า",
                    lat: 14.5159,
                    lon: 100.2540
                }
            ],
            "สุราษฎร์ธานี": [{
                    name: "โรงพยาบาลสุราษฎร์ธานี",
                    lat: 9.1501,
                    lon: 99.3401
                },
                {
                    name: "โรงพยาบาลเกาะสมุย",
                    lat: 9.5312,
                    lon: 100.0482
                },
                {
                    name: "โรงพยาบาลดอนสัก",
                    lat: 9.1031,
                    lon: 99.5630
                },
                {
                    name: "โรงพยาบาลพุนพิน",
                    lat: 9.1781,
                    lon: 99.3454
                },
                {
                    name: "โรงพยาบาลท่าฉาง",
                    lat: 9.2552,
                    lon: 99.5004
                }
            ],
            "สุรินทร์": [{
                    name: "โรงพยาบาลสุรินทร์",
                    lat: 14.8868,
                    lon: 103.4915
                },
                {
                    name: "โรงพยาบาลท่าตูม",
                    lat: 14.8973,
                    lon: 103.5449
                },
                {
                    name: "โรงพยาบาลชุมพลบุรี",
                    lat: 14.6642,
                    lon: 103.3711
                },
                {
                    name: "โรงพยาบาลจอมพระ",
                    lat: 14.4560,
                    lon: 103.3612
                },
                {
                    name: "โรงพยาบาลลำดวน",
                    lat: 14.5707,
                    lon: 103.3474
                }
            ],
            "หนองคาย": [{
                    name: "โรงพยาบาลหนองคาย",
                    lat: 17.8733,
                    lon: 102.7479
                },
                {
                    name: "โรงพยาบาลท่าบ่อ",
                    lat: 17.5705,
                    lon: 102.4062
                },
                {
                    name: "โรงพยาบาลโพธิ์ตาก",
                    lat: 17.7737,
                    lon: 102.7179
                },
                {
                    name: "โรงพยาบาลจุดไผ่",
                    lat: 17.9302,
                    lon: 102.6851
                },
                {
                    name: "โรงพยาบาลบ้านดุง",
                    lat: 17.7106,
                    lon: 102.5083
                }
            ],
            "หนองบัวลำภู": [{
                    name: "โรงพยาบาลหนองบัวลำภู",
                    lat: 17.2218,
                    lon: 102.4260
                },
                {
                    name: "โรงพยาบาลโนนสะอาด",
                    lat: 17.4207,
                    lon: 102.4692
                },
                {
                    name: "โรงพยาบาลบ้านค้อ",
                    lat: 17.0533,
                    lon: 102.6452
                },
                {
                    name: "โรงพยาบาลภูหลวง",
                    lat: 17.1505,
                    lon: 102.6131
                },
                {
                    name: "โรงพยาบาลนาเชือก",
                    lat: 17.3025,
                    lon: 102.4010
                }
            ],
            "อ่างทอง": [{
                    name: "โรงพยาบาลอ่างทอง",
                    lat: 14.5833,
                    lon: 100.4500
                },
                {
                    name: "โรงพยาบาลเมืองอ่างทอง",
                    lat: 14.5940,
                    lon: 100.4720
                },
                {
                    name: "โรงพยาบาลพาณิชย์",
                    lat: 14.6250,
                    lon: 100.4530
                },
                {
                    name: "โรงพยาบาลไชโย",
                    lat: 14.7312,
                    lon: 100.5568
                },
                {
                    name: "โรงพยาบาลบางแก้ว",
                    lat: 14.4698,
                    lon: 100.3752
                }
            ],
            "อำนาจเจริญ": [{
                    name: "โรงพยาบาลอำนาจเจริญ",
                    lat: 15.8600,
                    lon: 104.6300
                },
                {
                    name: "โรงพยาบาลชานุมาน",
                    lat: 15.7933,
                    lon: 104.6621
                },
                {
                    name: "โรงพยาบาลน้ำยืน",
                    lat: 15.7889,
                    lon: 104.6678
                },
                {
                    name: "โรงพยาบาลปทุมราชวงศา",
                    lat: 15.7631,
                    lon: 104.7714
                },
                {
                    name: "โรงพยาบาลพนา",
                    lat: 15.9132,
                    lon: 104.5847
                }
            ],
            "อุดรธานี": [{
                    name: "โรงพยาบาลอุดรธานี",
                    lat: 17.4048,
                    lon: 102.7893
                },
                {
                    name: "โรงพยาบาลหนองหาน",
                    lat: 17.5817,
                    lon: 102.8082
                },
                {
                    name: "โรงพยาบาลกุมภวาปี",
                    lat: 17.2625,
                    lon: 102.6366
                },
                {
                    name: "โรงพยาบาลประจักษ์",
                    lat: 17.3585,
                    lon: 102.8101
                },
                {
                    name: "โรงพยาบาลโนนสูง",
                    lat: 17.2332,
                    lon: 102.7232
                }
            ],
            "อุตรดิตถ์": [{
                    name: "โรงพยาบาลอุตรดิตถ์",
                    lat: 17.6316,
                    lon: 100.0972
                },
                {
                    name: "โรงพยาบาลคีรีมาศ",
                    lat: 17.5407,
                    lon: 100.0647
                },
                {
                    name: "โรงพยาบาลลับแล",
                    lat: 17.5736,
                    lon: 100.1565
                },
                {
                    name: "โรงพยาบาลตรอน",
                    lat: 17.5392,
                    lon: 100.0858
                },
                {
                    name: "โรงพยาบาลทองแสน",
                    lat: 17.5725,
                    lon: 100.1833
                }
            ],
            "อุทัยธานี": [{
                    name: "โรงพยาบาลอุทัยธานี",
                    lat: 15.3819,
                    lon: 100.0264
                },
                {
                    name: "โรงพยาบาลบ้านไร่",
                    lat: 15.2743,
                    lon: 100.1736
                },
                {
                    name: "โรงพยาบาลหนองฉาง",
                    lat: 15.4432,
                    lon: 99.9665
                },
                {
                    name: "โรงพยาบาลทัพทัน",
                    lat: 15.3116,
                    lon: 100.2040
                },
                {
                    name: "โรงพยาบาลลานสัก",
                    lat: 15.2659,
                    lon: 100.1349
                }
            ],
            "อุบลราชธานี": [{
                    name: "โรงพยาบาลอุบลราชธานี",
                    lat: 15.2500,
                    lon: 104.8300
                },
                {
                    name: "โรงพยาบาลวารินชำราบ",
                    lat: 15.2864,
                    lon: 104.7914
                },
                {
                    name: "โรงพยาบาลเขมราฐ",
                    lat: 15.3280,
                    lon: 104.4814
                },
                {
                    name: "โรงพยาบาลตระการพืชผล",
                    lat: 15.1532,
                    lon: 104.5656
                },
                {
                    name: "โรงพยาบาลพิบูลมังสาหาร",
                    lat: 15.2282,
                    lon: 104.5532
                }
            ]
        };


        // อัปเดต Dropdown โรงพยาบาลเมื่อเลือกจังหวัด
        document.getElementById('destinationProvince').addEventListener('change', function() {
            const selectedProvince = this.value;
            const hospitalDropdown = document.getElementById('hospital');
            const hospitalLatLon = document.getElementById('hospitalLatLon'); // Hidden input สำหรับเก็บ lat/lon

            // ล้างรายการโรงพยาบาลก่อน
            hospitalDropdown.innerHTML = '<option value="" selected hidden>เลือกโรงพยาบาล</option>';
            hospitalLatLon.value = ''; // รีเซ็ตค่าละติจูดและลองจิจูด

            if (selectedProvince && hospitalsByProvince[selectedProvince]) {
                // เพิ่มรายการโรงพยาบาลของจังหวัดที่เลือก
                hospitalsByProvince[selectedProvince].forEach(hospital => {
                    const option = document.createElement('option');
                    option.value = hospital.name; // เก็บชื่อโรงพยาบาลใน value
                    option.dataset.lat = hospital.lat; // เก็บละติจูดใน dataset
                    option.dataset.lon = hospital.lon; // เก็บลองจิจูดใน dataset
                    option.textContent = hospital.name;
                    hospitalDropdown.appendChild(option);
                });

                // เปิดใช้งาน Dropdown โรงพยาบาล
                hospitalDropdown.disabled = false;
            } else {
                // ปิดการใช้งาน Dropdown โรงพยาบาลหากไม่มีจังหวัดที่เลือก
                hospitalDropdown.disabled = true;
            }
        });

        // เพิ่ม Event Listener ให้กับ Dropdown โรงพยาบาล
        document.getElementById('hospital').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const hospitalLatLon = document.getElementById('hospitalLatLon'); // Hidden input สำหรับเก็บ lat/lon

            if (selectedOption) {
                // เก็บค่าละติจูดและลองจิจูดใน hidden input
                hospitalLatLon.value = `${selectedOption.dataset.lat},${selectedOption.dataset.lon}`;
            }

            calculatePatientPrice(); // คำนวณราคาใหม่เมื่อเลือกโรงพยาบาล
        });

        // ฟังก์ชันคำนวณระยะทางระหว่าง Marker และโรงพยาบาล
        function calculatePatientDistance() {
            const pickupLocation = document.getElementById('pickup-location').value;
            const selectedHospital = document.getElementById('hospitalLatLon').value;

            if (!pickupLocation) {
                // alert("กรุณาเลือกตำแหน่งบนแผนที่");
                return;
            }

            if (!selectedHospital) {
                // alert("กรุณาเลือกโรงพยาบาล");
                return;
            }

            // คำนวณระยะทางระหว่างจุดเริ่มต้นและโรงพยาบาล
            const [lat1, lon1] = pickupLocation.split(',').map(coord => parseFloat(coord));
            const [lat2, lon2] = selectedHospital.split(',').map(coord => parseFloat(coord));
            const distance = calculateDistance(lat1, lon1, lat2, lon2);

            return distance; // ส่งค่าระยะทางกลับไปใช้ในฟังก์ชันอื่น
        }

        // ฟังก์ชันคำนวณราคา (ปรับสำหรับฟอร์มจองรถผู้ป่วย)
        function calculatePatientPrice() {
            const pickupLocation = document.getElementById('pickup-location').value;
            const selectedHospital = document.getElementById('hospitalLatLon').value;

            if (!pickupLocation || !selectedHospital) {
                return;
            }

            const distance = calculatePatientDistance();
            if (!distance) return;

            const distanceCost = distance * fuelCostPerKm;

            const selectedVehicleLevel = document.querySelector('input[name="level"]:checked');
            if (!selectedVehicleLevel) {
                return;
            }
            const vehicleLevelCost = vehicleLevelPrices[selectedVehicleLevel.value];

            const nurseCount = parseInt(document.getElementById("nurse_number").value) || 0;
            const ambulanceCount = parseInt(document.getElementById("ambulance_number").value) || 1;

            const ambulanceCost = ambulanceCount * vehicleLevelCost;
            const nurseCost = nurseCount * nursePrice;


            const extraCost = (nurseCount * nursePrice * ambulanceCount) + ambulanceCost;
            const subtotal = distanceCost + extraCost; // ราคารวมก่อน VAT
            const vatAmount = subtotal * vat; // คำนวณ VAT 7%
            const totalPrice = subtotal + vatAmount; // ราคารวมหลัง VAT

            // อัปเดตค่าระยะทางในฟิลด์ hidden
            document.getElementById("calculatedDistance2").value = distance.toFixed(2);
            // สร้างข้อความรายละเอียดการคำนวณ
            const details = `
                 ระยะทาง: ${distance.toFixed(2)} กม. ค่าน้ำมัน: ${fuelCostPerKm} บาท/กม. <br>
            `;

            // แสดงผลในหน้าเว็บ
            document.getElementById("priceDisplay2").innerHTML = `
                <small>${details}</small>
                ยอดชำระทั้งหมด: ${totalPrice.toFixed(2)} บาท<br>
            `;
            document.getElementById("calculatedPrice2").value = totalPrice.toFixed(2);
        }
        // เพิ่ม Event Listener ให้กับ Dropdown ของโรงพยาบาล
        document.getElementById('hospital').addEventListener('change', function() {
            calculatePatientPrice(); // คำนวณราคาใหม่เมื่อเลือกโรงพยาบาล
        });

        // เพิ่ม Event Listener ให้กับ Radio Button ของระดับรถ
        document.querySelectorAll('input[name="level"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                calculatePatientPrice(); // คำนวณราคาใหม่เมื่อเปลี่ยนระดับรถ
            });
        });
    </script>
    <script>
        // ฟังก์ชันรีเซ็ตค่าของฟอร์ม
        function resetFormValues(form) {
            // รีเซ็ตค่าของทุก input และ select ในฟอร์มที่เลือก
            const inputElements = form.querySelectorAll('input, select, textarea');

            inputElements.forEach(input => {
                // ข้ามการรีเซ็ตค่าของ booking_date และ booking_start_time
                if (input.id === 'bookingDate' || input.id === 'bookingTime') {
                    return;
                }

                if (input.type === 'radio' || input.type === 'checkbox') {
                    input.checked = false; // รีเซ็ตค่าของ radio หรือ checkbox
                } else if (input.type === 'number' || input.type === 'text' || input.type === 'hidden') {
                    input.value = ''; // รีเซ็ตค่าของ input ที่เป็นตัวเลขหรือข้อความ
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0; // รีเซ็ตค่าของ select เป็นตัวเลือกแรก
                } else if (input.tagName.toLowerCase() === 'textarea') {
                    input.value = ''; // รีเซ็ตค่าของ textarea
                }
            });

            // รีเซ็ตค่าพิเศษสำหรับ form2
            if (form.id === "form2") {
                document.getElementById("nurse_number").value = 0; // ค่าเริ่มต้นของพยาบาล
                document.getElementById("ambulance_number").value = 1; // ค่าเริ่มต้นของรถพยาบาล
            }

            // รีเซ็ตค่าของฟิลด์แสดงราคา
            const priceDisplay = form.querySelector('[id^="priceDisplay"]');
            const calculatedPrice = form.querySelector('[id^="calculatedPrice"]');
            if (priceDisplay) {
                priceDisplay.innerText = "ราคาค่าบริการ: 0 บาท"; // รีเซ็ตข้อความแสดงราคา
            }
            if (calculatedPrice) {
                calculatedPrice.value = ""; // รีเซ็ตค่าที่ซ่อนอยู่
            }

            // รีเซ็ตสถานะของปุ่มชำระเงิน
            const paymentButtons = form.querySelectorAll('.payment-button');
            paymentButtons.forEach(button => {
                button.style.border = "none"; // ลบเส้นขอบของปุ่ม
            });

            // รีเซ็ตค่าของ input ซ่อนที่เก็บวิธีการชำระเงิน
            const paymentMethodInput = form.querySelector('[id^="payment_method"]');
            if (paymentMethodInput) {
                paymentMethodInput.value = ""; // รีเซ็ตค่าที่ซ่อนอยู่
            }
        }

        // ฟังก์ชันสลับฟอร์ม
        function switchForm() {
            const formSelect = document.getElementById('formSelect');
            const selectedForm = formSelect.value;

            // ซ่อนฟอร์มทั้งหมด
            const forms = document.querySelectorAll('.form-container');
            forms.forEach(form => {
                form.classList.remove('active');
                resetFormValues(form); // รีเซ็ตค่าของฟอร์มที่ไม่ได้ใช้งาน
            });

            // แสดงฟอร์มที่เลือก
            const activeForm = document.getElementById(selectedForm);
            if (activeForm) {
                activeForm.classList.add('active');
            }

            // สร้างแผนที่ใหม่ตามฟอร์มที่เลือก
            if (selectedForm === 'form1') {
                createEventMap();
            } else if (selectedForm === 'form2') {
                createPatientMap();
            }
        }

        // เรียกใช้งานเมื่อหน้าโหลดครั้งแรก
        document.addEventListener('DOMContentLoaded', function() {
            switchForm(); // เรียกใช้งาน switchForm เมื่อหน้าโหลด
        });

        // เรียกใช้งาน switchForm เมื่อเปลี่ยนฟอร์ม
        document.getElementById('formSelect').addEventListener('change', switchForm);
    </script>

</body>

</html>