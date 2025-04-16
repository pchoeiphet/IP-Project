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
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style_reservation_cars.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>จองคิวรถ</title>
    <!-- <script src="javascrip_member/reservation_car.js" defer></script> -->
</head>

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
        <div><a href="reservation_car.php" style="color: #FFB898;">จองคิวรถ</a></div>
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

<div class="search-section">
    <div class="search-container">
        <input type="text" placeholder="ค้นหา..." class="search-input">
        <button class="search-button">
            <i class="fas fa-search"></i> <!-- ไอคอนแว่นขยาย -->
        </button>
    </div>
</div>

<body>
    <!-- ปฏิทิน -->
    <div class="calendar-container">
        <div class="calendar-header">
            <button id="prev-month">&lt;</button>
            <div class="month-year-display">
                <span id="selected-month">
                    <h3>มกราคม</h3>
                </span>
                <span id="selected-year">
                    <h3>2567</h3>
                </span>
            </div>
            <button id="next-month">&gt;</button>
        </div>
        <table class="calendar-table">
            <thead>
                <tr>
                    <th class="header-day">อา</th>
                    <th class="header-day">จ</th>
                    <th class="header-day">อ</th>
                    <th class="header-day">พ</th>
                    <th class="header-day">พฤ</th>
                    <th class="header-day">ศ</th>
                    <th class="header-day">ส</th>
                </tr>
            </thead>
            <tbody id="calendar-body">
                <!-- ตารางวันที่ -->
            </tbody>
        </table>

        <!-- Modal สำหรับแสดงตารางเวลาเริ่มต้น -->
        <div id="calendar-event-modal" class="event-modal">
            <div class="event-modal-content">
                <span class="close-event-modal">&times;</span>
                <h3 id="modal-date-title">ตารางเวลา</h3>

                <div class="event-schedule">
                    <div class="schedule-column">
                        <input type="hidden" name="selected_time" id="selected-time" value="">
                        <div class="time-slot" data-time="07:00">07:00</div>
                        <div class="time-slot" data-time="08:00">08:00</div>
                        <div class="time-slot" data-time="09:00">09:00</div>
                        <div class="time-slot" data-time="10:00">10:00</div>
                        <div class="time-slot" data-time="11:00">11:00</div>
                        <div class="time-slot" data-time="12:00">12:00</div>
                        <div class="time-slot" data-time="13:00">13:00</div>
                        <div class="time-slot" data-time="14:00">14:00</div>
                        <div class="time-slot" data-time="15:00">15:00</div>
                    </div>
                    <div class="schedule-column">
                        <div class="time-slot" data-time="16:00">16:00</div>
                        <div class="time-slot" data-time="17:00">17:00</div>
                        <div class="time-slot" data-time="18:00">18:00</div>
                        <div class="time-slot" data-time="19:00">19:00</div>
                        <div class="time-slot" data-time="20:00">20:00</div>
                        <div class="time-slot" data-time="21:00">21:00</div>
                        <div class="time-slot" data-time="22:00">22:00</div>
                        <div class="time-slot" data-time="23:00">23:00</div>
                        <div class="time-slot" data-time="00:00">00:00</div>
                    </div>
                </div>
                <div class="button-container">
                    <button id="confirm-selection" class="confirm-btn">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const calendarBody = document.getElementById("calendar-body");
        const selectedMonth = document.getElementById("selected-month");
        const selectedYear = document.getElementById("selected-year");
        const prevMonthBtn = document.getElementById("prev-month");
        const nextMonthBtn = document.getElementById("next-month");

        // ประกาศ months ที่ด้านบนสุดเพื่อให้ทุกฟังก์ชันเข้าถึงได้
        const months = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม",
            "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        function renderCalendar(month, year) {
            calendarBody.innerHTML = "";
            const firstDay = new Date(year, month).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            let date = 1;

            for (let i = 0; i < 6; i++) {
                const row = document.createElement("tr");

                for (let j = 0; j < 7; j++) {
                    const cell = document.createElement("td");

                    if (i === 0 && j < firstDay) {
                        cell.innerHTML = "";
                    } else if (date > daysInMonth) {
                        break;
                    } else {
                        const today = new Date();
                        const isPastDate = new Date(year, month, date) < today.setHours(0, 0, 0, 0);

                        cell.innerHTML = date;
                        if (isPastDate) {
                            cell.classList.add("disabled");
                        } else {
                            cell.addEventListener("click", () => {
                                document.querySelectorAll(".calendar-table td.active").forEach(td => {
                                    td.classList.remove("active");
                                });
                                cell.classList.add("active");

                                // อัปเดต modal ให้แสดงตารางเวลา
                                const modal = document.getElementById("calendar-event-modal");
                                const modalTitle = document.getElementById("modal-date-title");
                                modalTitle.innerText = `ตารางเวลา วันที่ ${cell.innerText} ${months[month]} ${year + 543}`;
                                modal.style.display = "flex";

                                // รีเซ็ทการเลือกเวลาใน Modal
                                const timeSlots = document.querySelectorAll(".time-slot");
                                timeSlots.forEach(slot => {
                                    slot.classList.remove("selected");
                                });

                                // ล้างการเลือกเวลาจากปุ่มยืนยัน
                                const confirmBtn = document.getElementById("confirm-selection");
                                confirmBtn.style.display = "none";

                                // const modal = document.getElementById("calendar-event-modal");
                                // if (modal) {
                                //     modal.style.display = "none"; // ปิด modal ก่อนการ redirect
                                // }
                                // window.location.replace("form_copy.php");

                                // เก็บวันที่ที่เลือก
                                const selectedDate = `${cell.innerText} ${months[month]} ${year + 543}`;
                                localStorage.setItem("selectedDate", selectedDate);
                            });
                        }
                        date++;
                    }
                    row.appendChild(cell);
                }
                calendarBody.appendChild(row);
            }

            selectedMonth.innerText = months[month];
            selectedYear.innerText = year + 0; // แปลงเป็นปี พ.ศ.
        }

        prevMonthBtn.addEventListener("click", () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar(currentMonth, currentYear);
        });

        nextMonthBtn.addEventListener("click", () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        });

        renderCalendar(currentMonth, currentYear);
    });

    const timeSlots = document.querySelectorAll(".time-slot");
    timeSlots.forEach(slot => {
        slot.addEventListener("click", function() {
            timeSlots.forEach(s => s.classList.remove("selected")); // เอาคลาส selected ออกจากช่องเวลาทั้งหมด
            this.classList.add("selected"); // เพิ่มคลาส selected ให้ช่องเวลาที่กด

            // แสดงปุ่มยืนยัน
            // confirmBtn.style.display = "block";
        });
    });

    document.addEventListener("DOMContentLoaded", () => {
        const confirmBtn = document.getElementById("confirm-selection");
        const timeSlots = document.querySelectorAll(".time-slot");

        timeSlots.forEach(slot => {
            slot.addEventListener("click", function() {
                timeSlots.forEach(s => s.classList.remove("selected"));
                this.classList.add("selected");
                confirmBtn.style.display = "block";
            });
        });

        // ฟังก์ชันแปลงวันที่จาก "วัน เดือน ปี พ.ศ." เป็น "YYYY-MM-DD"
        function convertThaiDateToISO(day, month, year) {
            const monthsMap = {
                "มกราคม": "01",
                "กุมภาพันธ์": "02",
                "มีนาคม": "03",
                "เมษายน": "04",
                "พฤษภาคม": "05",
                "มิถุนายน": "06",
                "กรกฎาคม": "07",
                "สิงหาคม": "08",
                "กันยายน": "09",
                "ตุลาคม": "10",
                "พฤศจิกายน": "11",
                "ธันวาคม": "12"
            };

            let yearCE = year + 543; // แปลงปี พ.ศ. เป็น ค.ศ.
            let monthNumber = monthsMap[month]; // หาเดือนที่ตรงกับตัวเลข

            return `${yearCE}-${monthNumber}-${String(day).padStart(2, '0')}`; // คืนค่ารูปแบบ YYYY-MM-DD
        }

        // รวม event listener ของปุ่ม confirm เป็นอันเดียว
        confirmBtn.addEventListener("click", async function(e) {
            e.preventDefault(); // ป้องกันการ redirect

            let booking_date = document.querySelector(".calendar-table td.active")?.textContent || "No date selected";
            let currentMonth = document.getElementById("selected-month").innerText;
            let currentYear = parseInt(document.getElementById("selected-year").innerText) - 543; // ใช้ปี พ.ศ.
            const selectedTime = document.querySelector(".time-slot.selected")?.getAttribute("data-time");

            if (booking_date === "No date selected" || !selectedTime) {
                alert("กรุณาเลือกวันที่และเวลาที่ต้องการ");
                return; // ไม่ส่งข้อมูลหากยังไม่ได้เลือกวันที่หรือเวลา
            }

            // แปลงวันที่ให้อยู่ในรูปแบบ YYYY-MM-DD
            let formattedDate = convertThaiDateToISO(booking_date, currentMonth, currentYear);

            let formData = {
                "booking_date": formattedDate, // ส่งในรูปแบบ YYYY-MM-DD
                "booking_start_time": selectedTime
            };

            // ส่งข้อมูลผ่าน AJAX ไปยัง form.php
            $.ajax({
                url: 'form.php', // เปลี่ยน URL ไปยังไฟล์ form.php
                type: 'POST',
                data: formData,


                // ** การตอบกลับจาก server
                success: function(data, status) {
                    console.log(data);
                    console.log("Type of data:", typeof data);

                    // หลังจากข้อมูลถูกส่งสำเร็จ redirect ไปหน้าอื่นหรือล้างฟอร์ม
                    // alert("จองสำเร็จแล้ว!"); // หรือแสดงข้อความอื่นๆ ตามต้องการ
                    window.location.href = `form.php?booking_date=${formattedDate}&booking_start_time=${selectedTime}`; // หลังจากส่งเสร็จ, redirect ไปหน้าอื่น (ถ้ามี)
                },

                // ** ถ้ามี error จาก server
                error: function(e) {
                    alert('Error: ' + e.statusText);
                }
            });
        });
    });
</script>

</html>