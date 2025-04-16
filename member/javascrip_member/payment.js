    // 5. ฟังก์ชันจัดการการเลือก "เช่า" หรือ "ซื้อ"
    document.getElementById('purchase-rent').addEventListener('click', function () {
        document.getElementById('purchase-rent').style.border = "2px solid #2D5696";
        document.getElementById('purchase-buy').style.border = "none";
        document.querySelector('.installment-input').disabled = false; // เปิดการใช้งานระยะเวลาเช่า
        decreaseMonthButton.disabled = false;
        increaseMonthButton.disabled = false;

        // รีเซ็ตให้เป็นค่าว่างเมื่อเลือก "เช่า"
        installmentInput.value = "1"; // รีเซ็ตเป็นค่าว่าง

        // คำนวณราคาหลังจากเลือก "เช่า"
        updateTotalPrice();
    });

    document.getElementById('purchase-buy').addEventListener('click', function () {
        document.getElementById('purchase-buy').style.border = "2px solid #2D5696";
        document.getElementById('purchase-rent').style.border = "none";
        document.querySelector('.installment-input').disabled = true; // ปิดการใช้งานระยะเวลาเช่า
        decreaseMonthButton.disabled = true;
        increaseMonthButton.disabled = true;

        // รีเซ็ตเป็นค่าว่างเมื่อเลือก "ซื้อ"
        installmentInput.value = ""; // รีเซ็ตเป็นค่าว่าง

        // คำนวณราคาหลังจากเลือก "ซื้อ"
        updateTotalPrice();
    });


    // ฟังก์ชันจัดการการเพิ่ม/ลดระยะเวลาเช่า
const decreaseMonthButton = document.querySelector('.decrease-month');
const increaseMonthButton = document.querySelector('.increase-month');
const installmentInput = document.querySelector('.installment-input');

// เมื่อกดเพิ่ม
increaseMonthButton.addEventListener('click', function () {
    let currentMonth = parseInt(installmentInput.value || 0);
    if (currentMonth < 12) { // ตรวจสอบไม่ให้เกิน 12 เดือน
        installmentInput.value = currentMonth + 1;
    }
});

// เมื่อกดลด
decreaseMonthButton.addEventListener('click', function () {
    let currentMonth = parseInt(installmentInput.value || 0);
    if (currentMonth > 1) { // ตรวจสอบไม่ให้ต่ำกว่า 1 เดือน
        installmentInput.value = currentMonth - 1;
    }
});

// ฟังก์ชันจัดการการเลือก "QR Promptpay" และ "บัตรเครดิต"
document.getElementById('payment-qr').addEventListener('click', function () {
    document.getElementById('payment-qr').style.border = "2px solid #2D5696"; // เปลี่ยนสีขอบเมื่อเลือก
    document.getElementById('payment-credit').style.border = "none"; // รีเซ็ตขอบของบัตรเครดิต
});

// ฟังก์ชันจัดการการเลือก "บัตรเครดิต"
document.getElementById('payment-credit').addEventListener('click', function () {
    document.getElementById('payment-credit').style.border = "2px solid #2D5696"; // เปลี่ยนสีขอบเมื่อเลือก
    document.getElementById('payment-qr').style.border = "none"; // รีเซ็ตขอบของ QR Promptpay
});