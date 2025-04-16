
document.addEventListener('DOMContentLoaded', () => {
    loadHospitals();
    const form = document.querySelector('.box');
    const cancelButton = document.querySelector('.cancel-button');
    const reasonField = document.getElementById('cause');
    const otherCauseRow = document.getElementById('other-cause-row');
    const otherCauseField = document.getElementById('other-cause');
    const districtSelect = document.getElementById("filter-zone-list");
    const costInput = document.getElementById("cost");

    // ดึงค่าใช้จ่ายจากตัวแปรที่ส่งจาก PHP
    const costPerDistrict = window.costPerDistrict;

    // โหลดค่าใช้จ่ายเมื่อเปลี่ยนเขต
    districtSelect.addEventListener('change', updateCost);
    updateCost(); // โหลดค่าเริ่มต้นเมื่อเปิดหน้า

    // ฟังก์ชันอัปเดตค่าใช้จ่าย
    function updateCost() {
        const selectedDistrict = districtSelect.value;
        if (costPerDistrict[selectedDistrict]) {
            costInput.value = costPerDistrict[selectedDistrict] + " บาท";
        } else {
            costInput.value = "";
        }
    }

    cancelButton.addEventListener('click', () => {
        form.reset();
        updateCost(); // รีเซ็ตค่าใช้จ่าย
    });

    reasonField.addEventListener('change', () => {
        if (reasonField.value === 'other') {
            otherCauseRow.style.display = 'block';
            otherCauseField.required = true; // ทำให้ฟิลด์ข้อความเป็น required
        } else {
            otherCauseRow.style.display = 'none';
            otherCauseField.required = false; // ทำให้ฟิลด์ข้อความไม่เป็น required
            otherCauseField.value = ''; // ล้างค่าฟิลด์ข้อความ
        }
    });
});

const hospitals = [
    {
        "hospital_name": "โรงพยาบาลศูนย์มะเร็งกรุงเทพฯ"
    },
    {
        "hospital_name": "โรงพยาบาลมนารมย์"
    },
    {
        "hospital_name": "โรงพยาบาลปิยะเวท"
    },
    {
        "hospital_name": "โรงพยาบาลเสรีรักษ์"
    },
    {
        "hospital_name": "โรงพยาบาลวิชัยเวช อินเตอร์เนชั่นแนล แยกไฟฉาย"
    },
    {
        "hospital_name": "โรงพยาบาลจักษุ รัตนิน"
    },
    {
        "hospital_name": "โรงพยาบาลสถาบันโรคไตภูมิราชนครินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลเบทเทอร์บีอิ้ง"
    },
    {
        "hospital_name": "โรงพยาบาลยันฮี"
    },
    {
        "hospital_name": "โรงพยาบาลผู้สูงอายุกล้วยน้ำไท 2"
    },
    {
        "hospital_name": "โรงพยาบาลคามิลเลียน"
    },
    {
        "hospital_name": "โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล"
    },
    {
        "hospital_name": "โรงพยาบาลเกษมราษฎร์ รามคำแหง"
    },
    {
        "hospital_name": "โรงพยาบาลบางขุนเทียน 1"
    },
    {
        "hospital_name": "โรงพยาบาลธนบุรี บำรุงเมือง"
    },
    {
        "hospital_name": "โรงพยาบาลวิมุต"
    },
    {
        "hospital_name": "โรงพยาบาลตำรวจ"
    },
    {
        "hospital_name": "โรงพยาบาลเลิดสิน"
    },
    {
        "hospital_name": "โรงพยาบาลนพรัตนราชธานี"
    },
    {
        "hospital_name": "โรงพยาบาลราชวิถี"
    },
    {
        "hospital_name": "โรงพยาบาลสงฆ์"
    },
    {
        "hospital_name": "โรงพยาบาลพระมงกุฎเกล้า"
    },
    {
        "hospital_name": "โรงพยาบาลภูมิพลอดุลยเดช"
    },
    {
        "hospital_name": "โรงพยาบาลทหารผ่านศึก"
    },
    {
        "hospital_name": "โรงพยาบาลศิริราช"
    },
    {
        "hospital_name": "โรงพยาบาลสมเด็จพระปิ่นเกล้า"
    },
    {
        "hospital_name": "โรงพยาบาลจุฬาลงกรณ์"
    },
    {
        "hospital_name": "โรงพยาบาลรามาธิบดี"
    },
    {
        "hospital_name": "คณะแพทยศาสตร์วชิรพยาบาล มหาวิทยาลัยนวมินทราธิราช"
    },
    {
        "hospital_name": "โรงพยาบาลลาดกระบังกรุงเทพมหานคร"
    },
    {
        "hospital_name": "โรงพยาบาลคลองสามวา"
    },
    {
        "hospital_name": "โรงพยาบาลบางนากรุงเทพมหานคร"
    },
    {
        "hospital_name": "โรงพยาบาลเวชศาสตร์เขตร้อน"
    },
    {
        "hospital_name": "โรงพยาบาลนวุติสมเด็จย่า"
    },
    {
        "hospital_name": "โรงพยาบาลประสานมิตร"
    },
    {
        "hospital_name": "โรงพยาบาลทหารเรือกรุงเทพ"
    },
    {
        "hospital_name": "โรงพยาบาลมูลนิธิมิราเคิล ออฟไลฟ์"
    },
    {
        "hospital_name": "ทัณฑสถานโรงพยาบาลราชทัณฑ์"
    },
    {
        "hospital_name": "โรงพยาบาลจุฬาภรณ์"
    },
    {
        "hospital_name": "สถาบันสุขภาพเด็กแห่งชาติมหาราชินี"
    },
    {
        "hospital_name": "โรงพยาบาลศิริราช ปิยมหาราชการุณย์"
    },
    {
        "hospital_name": "โรงพยาบาลพระจอมเกล้าเจ้าคุณทหาร"
    },
    {
        "hospital_name": "โรงพยาบาลการไฟฟ้านครหลวง"
    },
    {
        "hospital_name": "โรงพยาบาลสวนเบญจกิติเฉลิมพระเกียรติ 84 พรรษา"
    },
    {
        "hospital_name": "โรงพยาบาลเจริญกรุงประชารักษ์"
    },
    {
        "hospital_name": "โรงพยาบาลกลาง"
    },
    {
        "hospital_name": "โรงพยาบาลตากสิน"
    },
    {
        "hospital_name": "โรงพยาบาลสิรินธร"
    },
    {
        "hospital_name": "โรงพยาบาลราชพิพัฒน์"
    },
    {
        "hospital_name": "โรงพยาบาลหลวงพ่อทวีศักดิ์ ชุตินฺธโร อุทิศ"
    },
    {
        "hospital_name": "โรงพยาบาลเวชการุณย์รัศมิ์"
    },
    {
        "hospital_name": "โรงพยาบาลนคราภิบาล"
    },
    {
        "hospital_name": "โรงพยาบาลผู้สูงอายุบางขุนเทียน"
    },
    {
        "hospital_name": "โรงพยาบาลรัตนประชารักษ์"
    },
    {
        "hospital_name": "โรงพยาบาลบางนากรุงเทพมหานคร"
    },
    {
        "hospital_name": "โรงพยาบาลกรุงเทพ"
    },
    {
        "hospital_name": "โรงพยาบาล กรุงเทพคริสเตียน"
    },
    {
        "hospital_name": "โรงพยาบาลกล้วยน้ำไท"
    },
    {
        "hospital_name": "โรงพยาบาลเกษมราษฎร์ บางแค"
    },
    {
        "hospital_name": "โรงพยาบาลเกษมราษฎร์ ประชาชื่น"
    },
    {
        "hospital_name": "โรงพยาบาลวิชัยเวช อินเตอร์เนชั่นแนล หนองแขม"
    },
    {
        "hospital_name": "โรงพยาบาลเจ้าพระยา"
    },
    {
        "hospital_name": "โรงพยาบาลซีจีเอช"
    },
    {
        "hospital_name": "โรงพยาบาลซีจีเอช สายไหม"
    },
    {
        "hospital_name": "โรงพยาบาลเซนต์หลุยส์"
    },
    {
        "hospital_name": "โรงพยาบาลเทพธารินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลไทยนครินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลธนบุรี 1"
    },
    {
        "hospital_name": "โรงพยาบาลธนบุรี 2"
    },
    {
        "hospital_name": "โรงพยาบาลนครธน"
    },
    {
        "hospital_name": "โรงพยาบาลนวมินทร์ 9"
    },
    {
        "hospital_name": "โรงพยาบาลนวมินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลบางนา 1"
    },
    {
        "hospital_name": "โรงพยาบาลบางปะกอก 1"
    },
    {
        "hospital_name": "โรงพยาบาลบางปะกอก 8"
    },
    {
        "hospital_name": "โรงพยาบาลบางไผ่"
    },
    {
        "hospital_name": "โรงพยาบาลบางโพ"
    },
    {
        "hospital_name": "โรงพยาบาลบางมด"
    },
    {
        "hospital_name": "โรงพยาบาลบํารุงราษฎร์"
    },
    {
        "hospital_name": "โรงพยาบาลบี.แคร์ เมดิคอลเซ็นเตอร์"
    },
    {
        "hospital_name": "โรงพยาบาลบีเอ็นเอช"
    },
    {
        "hospital_name": "โรงพยาบาลบุญญาเวช"
    },
    {
        "hospital_name": "โรงพยาบาลประชาพัฒน์"
    },
    {
        "hospital_name": "โรงพยาบาลเปาโล เกษตร"
    },
    {
        "hospital_name": "โรงพยาบาลเปาโล เมโมเรียล"
    },
    {
        "hospital_name": "โรงพยาบาลเปาโล เมโมเรียล โชคชัย4"
    },
    {
        "hospital_name": "โรงพยาบาลพญาไท 1"
    },
    {
        "hospital_name": "โรงพยาบาลพญาไท 2"
    },
    {
        "hospital_name": "โรงพยาบาลพญาไท 3"
    },
    {
        "hospital_name": "โรงพยาบาลพญาไท นวมินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลพระรามเก้า"
    },
    {
        "hospital_name": "โรงพยาบาลพีเอ็มจี"
    },
    {
        "hospital_name": "โรงพยาบาลเพชรเวช"
    },
    {
        "hospital_name": "โรงพยาบาลแพทย์ปัญญา"
    },
    {
        "hospital_name": "โรงพยาบาลมงกุฎวัฒนะ"
    },
    {
        "hospital_name": "โรงพยาบาลมเหสักข์"
    },
    {
        "hospital_name": "โรงพยาบาลมิชชั่น"
    },
    {
        "hospital_name": "โรงพยาบาลมิตรประชา"
    },
    {
        "hospital_name": "โรงพยาบาลเมดพาร์ค"
    },
    {
        "hospital_name": "โรงพยาบาลรามคำแหง"
    },
    {
        "hospital_name": "โรงพยาบาลราษฏร์บูรณะ"
    },
    {
        "hospital_name": "โรงพยาบาลลาดพร้าว"
    },
    {
        "hospital_name": "โรงพยาบาลวิชัยยุทธ"
    },
    {
        "hospital_name": "โรงพยาบาลวิชัยเวช แยกไฟฉาย"
    },
    {
        "hospital_name": "โรงพยาบาลวิภาราม"
    },
    {
        "hospital_name": "โรงพยาบาลวิภาวดี"
    },
    {
        "hospital_name": "โรงพยาบาลเวชธานี"
    },
    {
        "hospital_name": "โรงพยาบาลศิครินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลศิริราชปิยมหาราชการุณย์"
    },
    {
        "hospital_name": "โรงพยาบาลสมิติเวช ไชน่าทาวน์"
    },
    {
        "hospital_name": "โรงพยาบาลสมิติเวช ธนบุรี (กรุงธน)"
    },
    {
        "hospital_name": "โรงพยาบาลสมิติเวช ศรีนครินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลสมิติเวช สุขุมวิท"
    },
    {
        "hospital_name": "โรงพยาบาลสหวิทยาการมะลิ"
    },
    {
        "hospital_name": "โรงพยาบาลสินแพทย์"
    },
    {
        "hospital_name": "โรงพยาบาลสินแพทย์ ศรีนครินทร์"
    },
    {
        "hospital_name": "โรงพยาบาลสุขุมวิท"
    },
    {
        "hospital_name": "โรงพยาบาลหัวเฉียว"
    },
    {
        "hospital_name": "โรงพยาบาลอินทรารัตน์"
    },
    {
        "hospital_name": "โรงพยาบาลอินทราพิทยาคม"
    },
    {
        "hospital_name": "Kamol Cosmetic Hospital"
    },
    {
        "hospital_name": "โรงพยาบาลกล้วยน้ำไท 1"
    },
    {
        "hospital_name": "โรงพยาบาลจุฬารัตน์ 7"
    },
    {
        "hospital_name": "โรงพยาบาลสุขสวัสดิ์"
    },
    {
        "hospital_name": "รพ.พรมงคลเทพมุนี"
    }
];

function loadHospitals() {
    const select = document.getElementById("hospital");
    if (!select) return;
  
    hospitals.forEach(hospital => {
      const option = document.createElement("option");
      option.value = hospital.hospital_name;
      option.textContent = hospital.hospital_name;
      select.appendChild(option);
    });
  }
