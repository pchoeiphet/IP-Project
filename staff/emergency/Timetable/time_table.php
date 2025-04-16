<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ตารางเวลารถพยาบาล</title>

    <!-- โหลด jQuery จาก CDN สำหรับใช้เขียนโค้ด JavaScript ที่ยืดหยุ่นและสะดวก -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- โหลดไลบรารี FullCalendar สำหรับแสดงปฏิทิน -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>

    <!-- โหลดไฟล์ JavaScript ภายนอกสำหรับจัดการตรรกะของตาราง -->
    <script src="timeTableScript.js"></script>

    <!-- โหลดไฟล์ CSS ภายนอกสำหรับตกแต่งหน้าเว็บ -->
    <link rel="stylesheet" href="style_test.css">
</head>

<body>
    <div class="title">
        <h1>ตารางเวลารถพยาบาล</h1>
    </div>

    <!-- ส่วนแสดงคำอธิบายสีในตาราง (Legend) -->
    <div class="legend">
        <!-- แสดงสีหรือรูปแบบสำหรับการจองรถพยาบาล -->
        <span class="amBooking"></span> Ambulance Booking

        <!-- แสดงสีหรือรูปแบบสำหรับการจองอีเวนต์ -->
        <span class="evBooking"></span> Event Booking

        <span class="fnBooking"></span> Finished Ambulance Booking
    </div>

    <!-- ส่วนแสดงปฏิทินสำหรับการจอง -->
    <div id="calendar"></div>


</body>

</html>