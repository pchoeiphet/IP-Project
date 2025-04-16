document.addEventListener("DOMContentLoaded", () => {
    const dropdown = document.querySelector(".dropdown");
    const menu = document.querySelector(".dropdown-menu");

    dropdown.addEventListener("click", (e) => {
        e.stopPropagation(); // หยุดการกระจายอีเวนต์
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });

    // ซ่อนเมนูเมื่อคลิกที่อื่น
    document.addEventListener("click", () => {
        menu.style.display = "none";
    });
});

// การเลือกวิธีการชำระเงิน
let selectedPaymentMethod = "";

document.getElementById('payment-qr').addEventListener('click', function () {
    selectedPaymentMethod = 'QR Promptpay';
    document.getElementById('payment-qr').style.border = "2px solid #2D5696";
    document.getElementById('payment-credit').style.border = "none";
});

document.getElementById('payment-credit').addEventListener('click', function () {
    selectedPaymentMethod = 'บัตรเครดิต';
    document.getElementById('payment-credit').style.border = "2px solid #2D5696";
    document.getElementById('payment-qr').style.border = "none";
});

// ยกเลิกการจอง
document.getElementById('cancel-button').addEventListener('click', function () {
    // รีเซ็ตฟอร์มเมื่อคลิกปุ่มยกเลิก
    document.querySelector("form").reset();
    document.getElementById('payment-qr').style.border = "none";
    document.getElementById('payment-credit').style.border = "none";
});

// ฟังก์ชันตรวจสอบฟอร์ม
function validateForm() {
    const requiredFields = [
        'patient-name', 'birthdate', 'pickup-location', 'hospital', 'symptom', 'allergy'
    ];

    for (let field of requiredFields) {
        const inputElement = document.getElementById(field);
        if (!inputElement.value || (inputElement.tagName === "SELECT" && inputElement.value === "")) {
            alert("กรุณากรอกข้อมูลให้ครบถ้วน");
            return false;
        }
    }

    if (selectedPaymentMethod === "") {
        alert("กรุณาเลือกวิธีการชำระเงิน");
        return false;
    }

    return true;
}

// เมื่อกดปุ่มยืนยัน
document.getElementById('submit-button').addEventListener('click', function (e) {
    e.preventDefault(); // หยุดการส่งฟอร์ม
    if (validateForm()) {
        if (selectedPaymentMethod === 'QR Promptpay') {
            window.location.href = "QRpayment.html"; // ไปหน้าชำระเงินด้วย QR
        } else if (selectedPaymentMethod === 'บัตรเครดิต') {
            window.location.href = "payment_credit.html"; // ไปหน้าชำระเงินด้วยบัตรเครดิต
        }
    }
});