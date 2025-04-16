async function applyFilters(){
    // กำหนด div ที่จะแสดงข้อมูล
    const contentDiv = document.getElementById("prodContian");
    
    // ดึงค่าที่ได้จากตัวกรองด้วย ID
    const category = document.getElementById("category").value;
    const priceSort = document.getElementById("priceSort").value;
    const minPrice = document.getElementById("minPrice").value;
    const maxPrice = document.getElementById("maxPrice").value;
    const searchQuery = document.querySelector('.search-input').value; // ดึงค่าจากช่องค้นหา

    let data = {
        "category": category,
        "priceSort": priceSort,
        "minPrice": minPrice,
        "maxPrice": maxPrice,
        "q": searchQuery // ส่งคำค้นหาด้วย
    }

    console.log(data);

    // ส่งข้อมูลไปที่ filter.php ด้วย fetch
    await fetch("filter.php", {
        method: "POST",
        body: JSON.stringify(data),
        headers: {
          "Content-type": "application/json; charset=UTF-8"
        }
      })
        //รับข้อมูลที่ส่งกลับมาเป็น text
        .then((response) => response.text())
        //แสดงข้อมูลที่ได้ใน div ที่กำหนดไว้
        .then((text) => contentDiv.innerHTML = text)

}

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

function updateMaxPrice() {
    var maxPriceRange = document.getElementById('maxPriceRange');
    var maxPriceInput = document.getElementById('maxPrice');
    var minPriceRange = document.getElementById('minPriceRange');
    var minPriceInput = document.getElementById('minPrice');

    let maxValue = parseFloat(maxPriceRange.value);
    let minValue = parseFloat(minPriceRange.value);

    maxPriceInput.value = maxValue;

    if (maxValue < minValue) {
        minPriceRange.value = maxValue;
        minPriceInput.value = maxValue;
    }
}

function updateMinPrice() {
    var minPriceRange = document.getElementById('minPriceRange');
    var minPriceInput = document.getElementById('minPrice');

    minPriceInput.value = minPriceRange.value;
}