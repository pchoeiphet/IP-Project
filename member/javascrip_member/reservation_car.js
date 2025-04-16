document.addEventListener("DOMContentLoaded", () => {
    const calendarBody = document.getElementById("calendar-body");
    const selectedMonth = document.getElementById("selected-month");
    const selectedYear = document.getElementById("selected-year");
    const prevMonthBtn = document.getElementById("prev-month");
    const nextMonthBtn = document.getElementById("next-month");

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
        selectedYear.innerText = year + 543; // แปลงเป็นปี พ.ศ.
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

// ปิด modal เมื่อคลิกปุ่มปิด
const closeModal = document.querySelector(".close-event-modal");
closeModal.addEventListener("click", () => {
    document.getElementById("calendar-event-modal").style.display = "none";
});

// ปิด modal เมื่อคลิกข้างนอก modal
window.addEventListener("click", (e) => {
    if (e.target == document.getElementById("calendar-event-modal")) {
        document.getElementById("calendar-event-modal").style.display = "none";
    }
});

document.addEventListener("DOMContentLoaded", () => {
    // ตรวจสอบว่าหน้าเพจถูกโหลดใหม่โดยสมบูรณ์
    if (performance.getEntriesByType("navigation")[0].type === "back_forward") {
        location.reload(); // รีเฟรชหน้าเพจเมื่อย้อนกลับ
        return;
    }

    localStorage.removeItem("bookingType");
    localStorage.removeItem("level");

    // รีเซ็ตการเลือกของปุ่มวิทยุทั้งหมด
    const allRadios = document.querySelectorAll('input[type="radio"]');
    allRadios.forEach(radio => {
        radio.checked = false; // ยกเลิกการเลือกทุกปุ่ม
    });

    // ตัวแปรสำหรับตรวจสอบการเลือก
    let isBookingTypeSelected = false;
    let isLevelSelected = false;

    const bookingTypeRadios = document.querySelectorAll('input[name="booking-type"]');
    const levelRadios = document.querySelectorAll('input[name="level"]');
    const timeSlots = document.querySelectorAll(".time-slot");
    const confirmBtn = document.getElementById("confirm-selection");

    // ตรวจสอบการเลือกประเภทการจอง
    bookingTypeRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            isBookingTypeSelected = true;
            enableTimeSlots();
        });
    });

    // ตรวจสอบการเลือกระดับรถ
    levelRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            isLevelSelected = true;
            enableTimeSlots();
        });
    });

    // ฟังก์ชันเปิดใช้งานการเลือกเวลาหากครบเงื่อนไข
    function enableTimeSlots() {
        if (isBookingTypeSelected && isLevelSelected) {
            timeSlots.forEach(slot => {
                slot.classList.remove("disabled"); // เปิดใช้งาน
                slot.addEventListener("click", handleTimeSlotClick);
            });
        }
    }

    // ฟังก์ชันจัดการการเลือกเวลา
    function handleTimeSlotClick(event) {
        const slot = event.currentTarget;
        slot.classList.toggle("selected");

        // เช็คว่ามีการเลือกเวลาอย่างน้อย 1 ช่วง
        const selectedSlots = document.querySelectorAll(".time-slot.selected");
        if (selectedSlots.length > 1) {
            confirmBtn.style.display = "inline-block";
        } else {
            confirmBtn.style.display = "none";
        }
    }

    // เพิ่มคลาส disabled กับช่วงเวลาเริ่มต้น
    timeSlots.forEach(slot => {
        slot.classList.add("disabled"); // ปิดการใช้งาน
        slot.removeEventListener("click", handleTimeSlotClick); // ลบ event listener
    });

    // ปุ่มยืนยัน
    confirmBtn.addEventListener("click", function () {
        const bookingType = document.querySelector('input[name="booking-type"]:checked');
        const level = document.querySelector('input[name="level"]:checked');

        if (!bookingType || !level) {
            alert("กรุณาเลือกทั้งประเภทการจองและระดับ!");
            return;
        }

        // เก็บค่าที่เลือกไว้ใน localStorage
        localStorage.setItem("bookingType", bookingType.id);
        localStorage.setItem("level", level.id);

        // ย้ายไปยังหน้าถัดไปตามค่าที่เลือก
        if (bookingType.id === "ambulance") {
            window.location.href = "form_ambulance.html";
        } else if (bookingType.id === "event") {
            window.location.href = "form_event.html";
        }
    });
});