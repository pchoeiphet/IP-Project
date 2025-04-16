// สร้างอ็อบเจ็กต์ results เพื่อเก็บค่าผลลัพธ์จากฟอร์ม
const results = {};

// รอให้ DOM โหลดเสร็จก่อนทำงาน
document.addEventListener('DOMContentLoaded', () => {
    // ดึงอ้างอิงไปยังฟอร์ม
    const form = document.getElementById('carReportForm');

    // ดึงอ้างอิงไปยังช่องเลือกระดับ (level) และหมายเลขทะเบียน (number)
    const levelField = form.querySelector('#level');
    const numberField = form.querySelector('#number');

    // กำหนด event listener ให้ทำงานเมื่อมีการเปลี่ยนค่าในช่องเลือกระดับ
    levelField.addEventListener('change', () => {
        const level = levelField.value; // ดึงค่าระดับที่ถูกเลือก
        let numberOptions = ''; // สร้างตัวแปรสำหรับเก็บตัวเลือกของหมายเลขทะเบียน

        // ตรวจสอบระดับที่เลือก และกำหนดตัวเลือกของหมายเลขทะเบียนตามระดับ
        if (level === 'ระดับ 1') {
            numberOptions = `
                <option value="2">ขค5678</option>
                <option value="4">ตฎ1142</option>
            `;
        } else if (level === 'ระดับ 2') {
            numberOptions = `
                <option value="1">กข1234</option>
                <option value="5">ลนณ886</option>
            `;    
        } else if (level === 'ระดับ 3') {
            numberOptions = `
                <option value="3">ฉช378</option>
            `;
        }

        // อัปเดตตัวเลือกของหมายเลขทะเบียนใน dropdown
        numberField.innerHTML = `<option value="" disabled selected>ระบุทะเบียนรถ</option>${numberOptions}`;
    });

    // หมายเหตุ: โค้ดนี้ไม่มี event listener สำหรับการ submit ฟอร์ม เพราะถูกคอมเมนต์ไว้
    // form.addEventListener('submit', submitForm);
});

/**
 * ฟังก์ชัน toggleDropdown ใช้เปิด/ปิด dropdown และช่องกรอกข้อมูลเพิ่มเติม
 * @param {string} name - ชื่อของ input radio group
 */
function toggleDropdown(name) {
    // ดึงอ้างอิงไปยัง radio ที่มีค่า "ไม่พร้อม"
    const radioNo = document.querySelector(`input[name="${name}"][value="ไม่พร้อม"]`);
    
    // ดึงอ้างอิงไปยัง dropdown และช่อง input อื่นๆ ที่เกี่ยวข้อง
    const dropdown = document.getElementById(`${name}-dropdown`);
    const otherField = document.getElementById(`${name}-other`);

    // ถ้า radio "ไม่พร้อม" ถูกเลือก ให้เปิด dropdown
    // ถ้าไม่ถูกเลือก ให้ปิด dropdown และล้างค่าช่องกรอกข้อมูลอื่นๆ
    dropdown.disabled = !radioNo.checked;

    if (!radioNo.checked) {
        otherField.disabled = true;
        otherField.value = "";
    }

    // ดึงค่าที่ถูกเลือกจาก radio และอัปเดตค่าใน results object
    const selectedValue = document.querySelector(`input[name="${name}"]:checked`).value;
    results[name] = { status: selectedValue, dropdown: dropdown.value, other: otherField.value };

    // แสดงค่าที่ถูกอัปเดตใน console เพื่อ debug
    console.log(results);
}

/**
 * ฟังก์ชัน updateDropdown ใช้อัปเดตค่า dropdown และเปิด/ปิดช่องกรอกข้อมูลเพิ่มเติม
 * @param {string} name - ชื่อของ dropdown ที่ต้องการอัปเดต
 */
function updateDropdown(name) {
    // ดึงอ้างอิงไปยัง dropdown และช่องกรอกข้อมูลเพิ่มเติม
    const dropdown = document.getElementById(`${name}-dropdown`);
    const otherField = document.getElementById(`${name}-other`);

    // ถ้าผู้ใช้เลือก "other" ให้เปิดช่องกรอกข้อมูลเพิ่มเติม
    if (dropdown.value === "other") {
        otherField.disabled = false;
    } else {
        otherField.disabled = true;
        otherField.value = ""; // ล้างค่าช่องกรอกข้อมูลเพิ่มเติมถ้าไม่ได้เลือก "other"
    }

    // อัปเดตค่าใน results object
    results[name].dropdown = dropdown.value;
    results[name].other = otherField.value;

    // แสดงค่าที่ถูกอัปเดตใน console เพื่อ debug
    console.log(results);
}

/**
 * ฟังก์ชัน resetForm ใช้สำหรับรีเซ็ตค่าในฟอร์มกลับเป็นค่าเริ่มต้น
 */
function resetForm() {
    document.getElementById('carReportForm').reset();
}
