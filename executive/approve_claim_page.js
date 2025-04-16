document.addEventListener("DOMContentLoaded", function() {
    console.log("หน้าเว็บโหลดเสร็จแล้ว");
    
    // === 1. ส่วนตั้งค่า Filter Sidebar ===
    setupFilterSidebar();
    
    // === 2. ส่วนเลือกทั้งหมด/ยืนยัน ===
    setupApprovalFunctions();
    
    // === 3. ส่วนกรองทั้งหมด ===
    setupAllFilters();
    
    // ทดสอบการทำงาน
    console.log("ตั้งค่าฟังก์ชันทั้งหมดเรียบร้อยแล้ว");
});

// ฟังก์ชันตั้งค่า Filter Sidebar
function setupFilterSidebar() {
    const filterIcon = document.querySelector('.filter-icon');
    const filterSidebar = document.getElementById('filterSidebar');
    const closeSidebar = document.querySelector('.close-sidebar');
    
    console.log("ตั้งค่า Filter Sidebar");
    console.log("พบปุ่ม Filter:", filterIcon ? "ใช่" : "ไม่");
    console.log("พบ Sidebar:", filterSidebar ? "ใช่" : "ไม่");
    
    // เพิ่ม Event สำหรับปุ่ม Filter
    if (filterIcon) {
        filterIcon.addEventListener('click', function(event) {
            console.log("คลิกปุ่ม Filter");
            if (filterSidebar) {
                filterSidebar.classList.toggle('active');
                console.log("สถานะ Sidebar:", filterSidebar.classList.contains('active') ? "เปิด" : "ปิด");
            }
            // ป้องกันไม่ให้การคลิกบน filterIcon ส่งผลต่อคลิกนอกพื้นที่
            event.stopPropagation();
        });
    }
    
    // เพิ่ม Event สำหรับปุ่มปิด
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function(event) {
            console.log("คลิกปุ่มปิด");
            if (filterSidebar) {
                filterSidebar.classList.remove('active');
                console.log("ปิด Sidebar");
            }
            // ป้องกันไม่ให้การคลิกบนปุ่มปิดส่งผลต่อคลิกนอกพื้นที่
            event.stopPropagation();
        });
    }
    
    // เพิ่ม Event สำหรับการคลิกนอกพื้นที่ Sidebar
    document.addEventListener('click', function(event) {
        // ตรวจสอบว่า clicked element ไม่ได้อยู่ใน Sidebar หรือ filterIcon
        if (!filterSidebar.contains(event.target) && !filterIcon.contains(event.target)) {
            if (filterSidebar.classList.contains('active')) {
                filterSidebar.classList.remove('active');
                console.log("ปิด Sidebar เพราะคลิกนอกพื้นที่");
            }
        }
    });
}


// ฟังก์ชันตั้งค่าการอนุมัติ/เลือกทั้งหมด
function setupApprovalFunctions() {
    // ฟังก์ชันเลือกทั้งหมด
    window.selectAll = function(status) {
        console.log("เลือกทั้งหมด:", status);
        const radios = document.querySelectorAll(status === 'อนุมัติแล้ว' ? '.approve-radio' : '.reject-radio');
        radios.forEach(radio => {
            radio.checked = true;
        });
    };
    
    // ฟังก์ชันยืนยันการอัปเดต
    window.confirmUpdate = function() {
        return confirm('คุณต้องการยืนยันการอัปเดตสถานะเคลมใช่หรือไม่?');
    };
}

// ฟังก์ชันตั้งค่าการกรองทั้งหมด
function setupAllFilters() {
    // รับตัวกรองทั้งหมด
    const filterQuantity = document.getElementById("filter-quantity-list");
    const filterRequestType = document.getElementById("filter-request-type");
    const filterEquipment = document.getElementById("equipment-filter-list");
    const filterDate1 = document.getElementById("date1");
    const filterDate2 = document.getElementById("date2");
    const sortDate = document.getElementById("sort-date");
    const filterClaimStatus = document.getElementById("claim-status");
    
    // เก็บข้อมูลแถวตั้งต้น
    const originalRows = Array.from(document.querySelectorAll("tbody tr"));
    console.log("จำนวนแถวทั้งหมด:", originalRows.length);
    
    // ฟังก์ชันรีเซ็ตตาราง
    function resetTable() {
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = "";
        originalRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
    }
    
    // ฟังก์ชันประมวลผลตัวกรอง
    function applyFilters() {
        console.log("กำลังใช้ตัวกรอง...");
        
        // คัดลอกข้อมูลเพื่อไม่ให้กระทบข้อมูลต้นฉบับ
        let filteredRows = [...originalRows];
        
        // 1. กรองตามประเภทคำขอ
        if (filterRequestType && filterRequestType.value !== "all") {
            console.log("กรองตามประเภทคำขอ:", filterRequestType.value);
            filteredRows = filteredRows.filter(row => {
                const requestType = row.cells[5].textContent.trim();
                return requestType === filterRequestType.value;
            });
        }
        
        // 2. กรองตามประเภทอุปกรณ์
        if (filterEquipment && filterEquipment.value !== "") {
            console.log("กรองตามประเภทอุปกรณ์:", filterEquipment.value);
            filteredRows = filteredRows.filter(row => {
                const equipment = row.cells[7].textContent.trim();
                return equipment.includes(filterEquipment.value);
            });
        }

        
        // 3. กรองตามวันที่
        if (filterDate1 && filterDate1.value) {
            const startDate = new Date(filterDate1.value);
            console.log("กรองตั้งแต่วันที่:", startDate);
            filteredRows = filteredRows.filter(row => {
                const dateStr = row.cells[4].textContent.trim();
                const rowDate = new Date(dateStr);
                return rowDate >= startDate;
            });
        }
        
        if (filterDate2 && filterDate2.value) {
            const endDate = new Date(filterDate2.value);
            console.log("กรองถึงวันที่:", endDate);
            filteredRows = filteredRows.filter(row => {
                const dateStr = row.cells[4].textContent.trim();
                const rowDate = new Date(dateStr);
                return rowDate <= endDate;
            });
        }
        
        // 4. เรียงลำดับตามวันที่
        if (sortDate && sortDate.value) {
            console.log("เรียงลำดับตามวันที่:", sortDate.value);
            filteredRows.sort((a, b) => {
                const dateA = new Date(a.cells[4].textContent.trim());
                const dateB = new Date(b.cells[4].textContent.trim());
                
                return sortDate.value === "latest" ? dateB - dateA : dateA - dateB;
            });
        }
        
        // 5. เรียงลำดับตามจำนวน (มีผลสูงสุด)
        if (filterQuantity && filterQuantity.value !== "all") {
            console.log("เรียงลำดับตามจำนวน:", filterQuantity.value);
            filteredRows.sort((a, b) => {
                const qtyA = parseInt(a.cells[6].textContent.trim()) || 0;
                const qtyB = parseInt(b.cells[6].textContent.trim()) || 0;
                
                return filterQuantity.value === "asc" ? qtyA - qtyB : qtyB - qtyA;
            });
        }
        
        // แสดงผลลัพธ์
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = "";
        
        if (filteredRows.length === 0) {
            const emptyRow = document.createElement("tr");
            const emptyCell = document.createElement("td");
            emptyCell.colSpan = 9;
            emptyCell.textContent = "ไม่พบข้อมูลที่ตรงกับเงื่อนไข";
            emptyCell.style.textAlign = "center";
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        } else {
            filteredRows.forEach(row => {
                // ต้องสร้าง clone เพื่อให้ event listener ยังทำงานได้
                tbody.appendChild(row.cloneNode(true));
            });
        }
        
        console.log("แสดงผล", filteredRows.length, "รายการ");
        
        // คืนค่า event listener ให้กับปุ่มเลือกทั้งหมด
        document.getElementById("approve-all").onclick = function() { selectAll('อนุมัติแล้ว'); };
        document.getElementById("reject-all").onclick = function() { selectAll('ถูกปฏิเสธ'); };
    }
    
    // เพิ่ม event listener ให้กับตัวกรองทั้งหมด
    if (filterQuantity) filterQuantity.addEventListener("change", applyFilters);
    if (filterRequestType) filterRequestType.addEventListener("change", applyFilters);
    if (filterEquipment) filterEquipment.addEventListener("change", applyFilters);
    if (filterDate1) filterDate1.addEventListener("change", applyFilters);
    if (filterDate2) filterDate2.addEventListener("change", applyFilters);
    if (sortDate) sortDate.addEventListener("change", applyFilters);
    if (filterClaimStatus) filterClaimStatus.addEventListener("change", applyFilters);
    
    // เพิ่มปุ่มรีเซ็ต
     /* const resetButton = document.createElement("button");
     resetButton.textContent = "รีเซ็ตตัวกรอง";
     resetButton.onclick = resetTable;
     document.querySelector(".sidebar-content").appendChild(resetButton); */
}

// เปิด console ในตอนแรกเพื่อดูข้อมูล
console.log("โหลดสคริปต์เรียบร้อยแล้ว");



