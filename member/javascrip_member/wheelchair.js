document.addEventListener("DOMContentLoaded", () => {
    const decreaseButton = document.querySelector(".btn-decrease");
    const increaseButton = document.querySelector(".btn-increase");
    const quantityInput = document.querySelector(".quantity-input");
    const maxQuantity = parseInt(quantityInput.max, 10); // ค่าสูงสุด (12)
    const minQuantity = parseInt(quantityInput.min, 10); // ค่าต่ำสุด (1)

    // ฟังก์ชันลดจำนวน
    decreaseButton.addEventListener("click", () => {
        let currentValue = parseInt(quantityInput.value, 10);
        if (currentValue > minQuantity) {
            quantityInput.value = currentValue - 1;
        }
    });

    // ฟังก์ชันเพิ่มจำนวน
    increaseButton.addEventListener("click", () => {
        let currentValue = parseInt(quantityInput.value, 10);
        if (currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
        }
    });

    // ป้องกันการกรอกเกิน max/min ด้วยมือ
    quantityInput.addEventListener("input", () => {
        let currentValue = parseInt(quantityInput.value, 10);
        if (currentValue > maxQuantity) {
            quantityInput.value = maxQuantity;
        } else if (currentValue < minQuantity) {
            quantityInput.value = minQuantity;
        }
    });
});