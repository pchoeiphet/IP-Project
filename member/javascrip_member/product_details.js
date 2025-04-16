        // ฟังก์ชันสำหรับแสดงข้อมูลสินค้าตาม URL Query Parameters
        function loadProductDetails() {
            const urlParams = new URLSearchParams(window.location.search);
            const product = urlParams.get('product');

            const productDetails = {
                bed: {
                    name: "เตียงไฟฟ้า (ICU Hospital Bed ST-EHDA2)",
                    cost: "฿ 13,000",
                    image: "image/เตียงไฟฟ้า.jpg",
                    quantity: 11 // จำนวนสินค้าทั้งหมด
                },
                wheelchair: {
                    name: "รถเข็นผู้ป่วย (ST-YK9063 Double Cross Wheelchair)",
                    cost: "฿ 5,900",
                    image: "image/รถเข็น.jpg",
                    quantity: 8 // จำนวนสินค้าทั้งหมด
                },
                oxygen: {
                    name: "ชุดถังอ๊อกซิเจนทางการแพทย์ แบบอลูมิเนียม USA 0.5Q/MD",
                    cost: "฿ 5,500",
                    image: "image/ถังอ๊อก.jpg",
                    quantity: 12 // จำนวนสินค้าทั้งหมด
                }
            };

            if (productDetails[product]) {
                document.getElementById("product-name").textContent = productDetails[product].name;
                document.getElementById("product-cost").textContent = productDetails[product].cost;
                document.getElementById("product-image").src = productDetails[product].image;
                document.getElementById("product-quantity").textContent = `มีทั้งหมด ${productDetails[product].quantity} ชิ้น`;
                 // อัปเดต max ของ input
                document.querySelector(".quantity-input").max = productDetails[product].quantity;
                document.querySelector(".quantity-input").value = 1; // กำหนดค่าเริ่มต้น
            }
        }
        document.addEventListener("DOMContentLoaded", () => {
            loadProductDetails(); // เรียกฟังก์ชันเพื่อโหลดข้อมูลสินค้า
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