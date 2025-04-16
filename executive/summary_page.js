// สคริปต์สำหรับเปิด-ปิด Sidebar
document.addEventListener("DOMContentLoaded", () => {
    const filterIcon = document.querySelector(".filter-icon");
    const sidebar = document.getElementById("filterSidebar");
    const closeSidebar = document.querySelector(".close-sidebar");

    // เปิด Sidebar
    filterIcon.addEventListener("click", () => {
        sidebar.classList.add("open");
    });

    // ปิด Sidebar
    closeSidebar.addEventListener("click", () => {
        sidebar.classList.remove("open");
    });

    // ปิด Sidebar เมื่อคลิกนอก Sidebar
    document.addEventListener("click", (e) => {
        if (!sidebar.contains(e.target) && !filterIcon.contains(e.target)) {
            sidebar.classList.remove("open");
        }
    });


});
// ตั้งค่าปฏิทิน Flatpickr
flatpickr("#calendarSelect", {
    dateFormat: "Y-m", // รูปแบบวันที่เป็น YYYY-MM
    defaultDate: new Date(), // กำหนดค่าเริ่มต้นเป็นเดือนและปีปัจจุบัน
    plugins: [
        new monthSelectPlugin({
            shorthand: true, // ใช้ชื่อย่อของเดือน
            dateFormat: "Y-m", // รูปแบบวันที่
            altFormat: "F Y" // รูปแบบการแสดงผลเป็น Full Month และ Year
        })
    ],
    onReady: function(selectedDates, dateStr, instance) {
        let currentDate = instance.formatDate(new Date(), "Y-m"); // ดึงค่าปัจจุบัน
        instance.setDate(currentDate, true); // ตั้งค่าวันที่ให้ตรงกับปัจจุบัน
        updateChart(currentDate); // อัปเดตกราฟทันทีเมื่อโหลดหน้าเว็บ
    },
    onChange: function(selectedDates, dateStr, instance) {
        updateChart(dateStr); // อัปเดตกราฟเมื่อมีการเลือกวันที่
    }
});

function updateMinPrice() {
    var minPriceRange = document.getElementById('minPriceRange');
    var minPriceInput = document.getElementById('minPrice');
    var maxPriceRange = document.getElementById('maxPriceRange');

    minPriceInput.value = minPriceRange.value;
    if (parseInt(minPriceRange.value) > parseInt(maxPriceRange.value)) {
        maxPriceRange.value = minPriceRange.value;
        document.getElementById('maxPrice').value = minPriceRange.value;
    }
}

function updateMaxPrice() {
    var maxPriceRange = document.getElementById('maxPriceRange');
    var maxPriceInput = document.getElementById('maxPrice');
    var minPriceRange = document.getElementById('minPriceRange');

    maxPriceInput.value = maxPriceRange.value;
    if (parseInt(maxPriceRange.value) < parseInt(minPriceRange.value)) {
        minPriceRange.value = maxPriceRange.value;
        document.getElementById('minPrice').value = maxPriceRange.value;
    }
}