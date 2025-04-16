document.addEventListener("DOMContentLoaded", () => {
    const carNumber = Math.floor(Math.random() * 20) + 1; // สุ่มตัวเลขระหว่าง 1-20
    document.getElementById("car-number").textContent = carNumber;

    const dropdown = document.querySelector(".dropdown");
    const menu = document.querySelector(".dropdown-menu");

    dropdown.addEventListener("click", (e) => {
        e.stopPropagation();
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", () => {
        menu.style.display = "none";
    });

    let selectedPaymentMethod = null;

    document.getElementById('payment-qr').addEventListener('click', () => {
        selectedPaymentMethod = 'QR';
        document.getElementById('payment-qr').style.border = "2px solid #2D5696";
        document.getElementById('payment-credit').style.border = "none";
    });

    document.getElementById('payment-credit').addEventListener('click', () => {
        selectedPaymentMethod = 'Credit';
        document.getElementById('payment-credit').style.border = "2px solid #2D5696";
        document.getElementById('payment-qr').style.border = "none";
    });

    document.getElementById('cancel-button').addEventListener('click', () => {
        document.querySelector("form").reset();
        document.getElementById('payment-qr').style.border = "none";
        document.getElementById('payment-credit').style.border = "none";
        selectedPaymentMethod = null;
    });

    // เมื่อกดปุ่มยืนยัน
    document.querySelector('form').addEventListener('submit', (event) => {
        event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

        const placeEvent = document.getElementById('place_event_detail').value;
        const type = document.getElementById('type').value;
        const nurseNumber = document.getElementById('nurse_number').value;
        const ambulanceNumber = document.getElementById('ambulance_number').value;

        // ตรวจสอบการกรอกข้อมูล
        if (!placeEvent || !type || !nurseNumber || !ambulanceNumber) {
            alert("กรุณากรอกข้อมูลให้ครบถ้วน");
            return;
        }

        // ตรวจสอบการเลือกวิธีการชำระเงิน
        if (!selectedPaymentMethod) {
            alert("กรุณาเลือกวิธีการชำระเงิน");
            return;
        }

        // เปลี่ยนหน้าไปตามวิธีชำระเงินที่เลือก
        if (selectedPaymentMethod === 'QR') {
            window.location.href = "QRpayment.html"; // ไปหน้าชำระเงินด้วย QR
        } else if (selectedPaymentMethod === 'Credit') {
            window.location.href = "payment_credit.html"; // ไปหน้าชำระเงินด้วยบัตรเครดิต
        }
    });
});