// ฟังก์ชันเปิด/ปิด sidebar และจำ state
document.addEventListener("DOMContentLoaded", () => {
    const filterIcon = document.querySelector(".filter-icon");
    const sidebar = document.getElementById("filterSidebar");
    const closeSidebar = document.querySelector(".close-sidebar");

    // เปิด sidebar ถ้าเคยเปิดไว้
    if (localStorage.getItem("sidebarOpen") === "true") {
        sidebar.classList.add("open");
    }

    filterIcon.addEventListener("click", () => {
        sidebar.classList.add("open");
        localStorage.setItem("sidebarOpen", "true");
    });

    closeSidebar.addEventListener("click", () => {
        sidebar.classList.remove("open");
        localStorage.removeItem("sidebarOpen");
    });

    const form = document.getElementById("filterForm");
    const inputs = form.querySelectorAll("input, select");

    inputs.forEach(input => {
        input.addEventListener("change", () => {
            localStorage.setItem("sidebarOpen", "true");
            updateChart(); // ✅ AJAX ไม่ reload หน้า
        });
    });
});



document.addEventListener("DOMContentLoaded", function () {
    const provinceSelect = document.getElementById("province_selected");
    const regionCheckboxes = document.querySelectorAll("input[name='region[]']");

    function toggleSelection() {
        if (provinceSelect.value && provinceSelect.value !== "ทั้งหมด") {
            // ถ้าเลือกจังหวัดที่ไม่ใช่ "ทั้งหมด" ให้ปิดการเลือกภูมิภาค
            regionCheckboxes.forEach(checkbox => {
                checkbox.disabled = true;
            });
        } else {
            // ถ้าเลือก "ทั้งหมด" หรือไม่ได้เลือกจังหวัด ให้เปิดภูมิภาค
            regionCheckboxes.forEach(checkbox => {
                checkbox.disabled = false;
            });
        }

        if ([...regionCheckboxes].some(checkbox => checkbox.checked)) {
            // ถ้าเลือกภูมิภาคและไม่ได้เลือก "ทั้งหมด" ให้ปิดจังหวัด
            if (provinceSelect.value !== "ทั้งหมด") {
                provinceSelect.disabled = false;
            }
        } else {
            // ถ้าไม่มีการเลือกภูมิภาค ให้เปิดจังหวัดได้
            provinceSelect.disabled = false;
        }
    }

    provinceSelect.addEventListener("change", toggleSelection);
    regionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener("change", toggleSelection);
    });
});