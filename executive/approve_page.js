document.addEventListener("DOMContentLoaded", () => {

    let arrApproved = [];


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

//filter ต่างๆ เขียนโค้ดในนนี้



document.addEventListener("DOMContentLoaded", () => {


    const slider = document.getElementById("priceRange");
    const priceInput = document.getElementById("priceInput");
    const productList = document.getElementById("productList");

    // อัปเดต Input เมื่อเลื่อน Slider
    slider?.addEventListener("input", () => {
        priceInput.value = slider.value; // อัปเดตค่าช่องกรอกให้ตรงกับ Slider
    });

    // อัปเดต Slider เมื่อกรอกค่าใน Input
    priceInput?.addEventListener("input", () => {
        const inputValue = parseInt(priceInput.value) || 100000;
        slider.value = Math.min(Math.max(inputValue, slider.min), slider.max); // จำกัดค่าให้อยู่ในช่วง
    });



   //ตัวรับค่าปุ่มของ php
    
    const objfilterQuantityList = document.getElementById('filter-quantity-list');
    const objStartDate = document.getElementById('start_date');
    const objEndDate = document.getElementById('end_date');
    const objFilterPriceList = document.getElementById('filter-price-list');
    const objMinPriceRange = document.getElementById('minPriceRange');
    const objMaxPriceRange = document.getElementById('maxPriceRange');
    const objEquipMentFilterList = document.getElementById('equipment-filter-list');
   
    btnApplyFilter.addEventListener('click', (e) => {       
        reloadPage();
    }); 

    btnReset.addEventListener('click', (e) => {
        location.href = "approve_page.php";
    });

    objMinPriceRange.addEventListener('input', (e) => {
        console.clear();
        console.log("minPriceRange", objMinPriceRange.value);
           
    });

    objMaxPriceRange.addEventListener('input', (e) => {
        console.clear();
        console.log("maxPriceRange", objMaxPriceRange.value);
           
    });

    objEquipMentFilterList.addEventListener('change', (e) => {
        console.clear();
        console.log("equipment-filter-list", objEquipMentFilterList.value);

    });

        const reloadPage = ()=>{
      
            // let filterValue = objfilterQuantityList.value;
            let filterPrice = objFilterPriceList.value;
            let minPriceRange = objMinPriceRange.value;
            let maxPriceRange = objMaxPriceRange.value;
            let equipmentFilter = objEquipMentFilterList.value;
            let startDate = objStartDate.value;
            let endDate = objEndDate.value;

            
            //ไว้ debug
            console.log(minPriceRange, maxPriceRange);
            
            // alt + 96  `
            window.location.href = 'approve_page.php?' + 
            `start_date=${startDate}` + 
            `&end_date=${endDate}` +
            // `&order_quantity=${filterValue}` +
            `&order_equipment_total=${filterPrice}`+
            `&min_price=${minPriceRange}` +
            `&max_price=${maxPriceRange}`+
            `&equipment_type=${equipmentFilter}`; 
        }
});


/**  */
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


document.addEventListener("DOMContentLoaded", () => {
    // ตั้งค่าปฏิทิน Flatpickr สำหรับฟิลด์วันที่แรก
    flatpickr("#calendarSelect", {
        dateFormat: "d/m/Y",  // รูปแบบวันที่เป็น วัน/เดือน/ปี (เช่น 01/01/2024)
        onChange: function (selectedDates, dateStr, instance) {
            updateChart(dateStr);
        }
    });

    // ตั้งค่าปฏิทิน Flatpickr สำหรับฟิลด์วันที่ที่สอง
    flatpickr("#calendarSelect2", {
        dateFormat: "d/m/Y",  // รูปแบบวันที่เป็น วัน/เดือน/ปี (เช่น 01/01/2024)
        onChange: function (selectedDates, dateStr, instance) {
            updateChart(dateStr);
        }
    
    });

});

let arrApproved = [];
let selectedAllStatus = null; // เก็บสถานะ selectAll ล่าสุด

function checkTest(orderId, status) {
    // ถ้าเคยเลือกอันนี้แล้ว และกำลังคลิกซ้ำ ให้ยกเลิก
    const existingIndex = arrApproved.findIndex(item => item.orderId === orderId);

    if (existingIndex !== -1 && arrApproved[existingIndex].approval === status) {
        // คลิกซ้ำ = ยกเลิก
        arrApproved.splice(existingIndex, 1);

        // เอา radio ออก
        const radios = document.querySelectorAll(`input[name="approval_${orderId}"]`);
        radios.forEach(r => r.checked = false);
    } else {
        // ลบรายการเก่าออกก่อน
        arrApproved = arrApproved.filter(item => item.orderId !== orderId);
        arrApproved.push({ orderId: orderId, approval: status });
    }

    console.log("arrApproved", arrApproved);
}



//ปุ่มยืนยันตัวที่ติ๊กทั้งหมด
function toggleSelectAll(status) {
    // ตรวจว่าคลิกซ้ำหรือไม่
    const isToggle = selectedAllStatus === status;
    selectedAllStatus = isToggle ? null : status;

    // หา radio buttons ทั้งหมดที่ตรงกับ value
    const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);

    radios.forEach(radio => {
        const name = radio.name;
        const orderId = name.split('_')[1]; // แยกจากชื่อ name="approval_123"
        if (!orderId) return;

        if (isToggle) {
            // ยกเลิกทั้งหมด
            radio.checked = false;
            arrApproved = arrApproved.filter(item => item.orderId != orderId);
        } else {
            // ตั้งค่าใหม่
            radio.checked = true;
            arrApproved = arrApproved.filter(item => item.orderId != orderId);
            arrApproved.push({ orderId: parseInt(orderId), approval: status });
        }
    });

    console.log("arrApproved", arrApproved);
}


// ตัวที่ส่งข้อมูลไปยัง save_order PHP

function submitApproval() {
    let selectedOrders = [];
    document.querySelectorAll(".item-radio:checked").forEach((radio) => {
        let orderId = parseInt(radio.name.replace("approval_", "").trim(), 10);
        let status = radio.value.trim();

        if (orderId > 0) {
            selectedOrders.push({
                order_equipment_id: orderId,
                approval_status: status
            });
        }
    });

    if (selectedOrders.length === 0) {
        alert("กรุณาเลือกอย่างน้อย 1 รายการ");
        return;
    }

    console.log("📌 ส่งข้อมูลไปยัง PHP:", JSON.stringify({ orders: selectedOrders }));

    fetch("save_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ orders: selectedOrders }),
    })
    .then(response => response.json())
    .then(data => {
        console.log("📌 PHP ตอบกลับ:", data);
        
        if (data.status === "success") {
            alert(data.message);
            location.reload(); // ✅ รีเฟรชหน้า
        } else {
            alert("เกิดข้อผิดพลาด: " + data.message);
            console.error("Errors:", data.errors);
        }
    })
    .catch(error => console.error("Error:", error));
}




















