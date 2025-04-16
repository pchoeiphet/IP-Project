   // ฟังก์ชันสำหรับแสดงข้อมูลสินค้าตาม URL Query Parameters
   function loadProductDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const product = urlParams.get('product');

    const productDetails = {
        bed: {
            name: "เตียงไฟฟ้า (ICU Hospital Bed ST-EHDA2)",
            image: "image/เตียงไฟฟ้า.jpg"
        },
        wheelchair: {
            name: "รถเข็นผู้ป่วย (ST-YK9063 Double Cross Wheelchair)",
            image: "image/รถเข็น.jpg"
        },
        oxygen: {
            name: "ชุดถังออกซิเจนทางการแพทย์ (แบบอลูมิเนียม USA 0.5Q/MD)",
            image: "image/ถังอ๊อก.jpg"
        }
    };
    

    if (productDetails[product]) {
        document.getElementById("product-name").textContent = productDetails[product].name;
        document.getElementById("product-image").src = productDetails[product].image;
    }
}

// เรียกใช้ฟังก์ชันเมื่อโหลดหน้าเว็บ
window.onload = loadProductDetails;

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