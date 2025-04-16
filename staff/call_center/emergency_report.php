<?php
include 'connect.php';
session_start();
//เลือกเฉพาะรถที่มีระดับ 2 หรือ 3
$query_ambulance = mysqli_query($conn, "SELECT * FROM ambulance WHERE ambulance_level = '2' OR ambulance_level = '3'");
$ambulance_data = mysqli_fetch_all($query_ambulance, MYSQLI_ASSOC);

$cost_levels = [
    700 => ["บางรัก", "สาทร", "ราชเทวี", "ปทุมวัน", "คลองสาน", "พระนคร", "ป้อมปราบศัตรูพ่าย", "สัมพันธวงศ์", "พญาไท", "ดินแดง", "ห้วยขวาง", "วัฒนา", "คลองเตย", "ดุสิต", "บางคอมแหลม", "ยานนาวา"],
    850 => ["บางพลัด", "บางกอกน้อย", "บางกอกใหญ่", "ธนบุรี", "ราษฎร์บูรณะ", "ทุ่งครุ", "จอมทอง", "ภาษีเจริญ", "ตลิ่งชัน", "บางซื่อ", "จตุจักร", "ลาดพร้าว", "วังทองหลาง", "บางกะปี", "สวนหลวง", "พระโขนง", "บางนา", "ประเวศ", "สะพานสูง", "บึงกุ่ม", "คันนายาว"],
    1000 => ["ทวีวัฒนา", "บางแค", "หนองแขม", "บางบอน", "บางขุนเทียน", "ดอนเมือง", "หลักสี่", "สายไหม", "บางเขน", "คลองสามวา", "มีนบุรี", "ลาดกระบัง", "หนองจอก"]
];

// สร้างอาร์เรย์แม็ปเขต -> ราคา
$district_cost_map = [];
foreach ($cost_levels as $cost => $districts) {
    foreach ($districts as $district) {
        $district_cost_map[$district] = $cost;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style-emergency_report.css">
    <script>
        window.costPerDistrict = <?php echo json_encode($district_cost_map, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="script-emergency_report.js" defer></script>
    <title>รายงานเคสฉุกเฉิน</title>
</head>

<body>

    <div class="title">
        <h1>รายงานเคสฉุกเฉิน</h1>
    </div>
    <form action="process.php" method="post" class="box">
        <div class="row">
            <label for="contact">ผู้ติดต่อ</label>
            <input type="text" name="contact" id="contact" required>

            <label for="contact_number">เบอร์โทรติดต่อ</label>
            <input type="text" name="contact_number" id="contact_number" required>
        </div>

        <div class="row">
            <label for="patient_name">ชื่อผู้ป่วย</label>
            <input type="text" name="patient_name" id="patient_name" required>

            <label for="patient_age">อายุผู้ป่วย</label>
            <input type="number" name="patient_age" id="patient_age" required min="1">
        </div>
        <div class="row">
            <label for="gender">เพศผู้ป่วย</label>
            <select id="gender" name="gender" required>
                <option value="" disabled selected>ระบุเพศ</option>
                <option value="ชาย">ชาย</option>
                <option value="หญิง">หญิง</option>
            </select>
        </div>

        <div class="row">
            <label for="cause">สาเหตุ/อาการป่วย</label>
            <select id="cause" name="cause" required>
                <option value="" disabled selected>ระบุสาเหตุ</option>
                <option value="อุบัติเหตุ">อุบัติเหตุ</option>
                <option value="อาการป่วย">อาการป่วย</option>
                <option value="other">อื่นๆ</option>
            </select>
        </div>
        <div class="row" id="other-cause-row" style="display: none;">
            <label for="other-cause">ระบุรายละเอียด</label>
            <input type="text" name="other-cause" id="other-cause">
        </div>

        <div class="row">
            <label for="filter-zone-list">เขตที่เกิดเหตุ</label>
            <select id="filter-zone-list" name="district" class="filter-select" required>
                <option value="" selected hidden>กรุณาเลือกเขต</option>
                <?php foreach ($district_cost_map as $district => $cost) { ?>
                    <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="row">
            <label for="start-point">สถานที่ต้นทาง</label>
            <input type="text" name="start-point" id="start-point" placeholder="ระบุรายละเอียดเพิ่มเติม" required>
        </div>
        <div class="row">
            <label for="end-point">สถานที่ปลายทาง</label>
            <select id="hospital" name="hospital" required>
                <option value="" disabled selected>ระบุโรงพยาบาล</option>
            </select>
        </div>
        <div class="row">
            <label for="ambulance_id">รถพยาบาลที่ออกปฏิบัติงาน</label>
            <select id="ambulance_id" name="ambulance_id" required>
                <option value="" selected hidden>กรุณาเลือกรถ</option>
                <!-- แสดงตัวเลือกแค่รถระดับ 2 หรือ 3 ตามที่ query มา-->
                <?php foreach ($ambulance_data as $row) { ?>
                    <option value="<?php echo $row["ambulance_id"]; ?>">
                        <?php echo $row["ambulance_id"]; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row">
            <label for="cost">ค่าบริการ</label>
            <input type="text" id="cost" name="cost" readonly>
        </div>
        <div class="button">
            <button type="submit" class="submit-button">บันทึก</button>
            <button type="button" class="cancel-button">ยกเลิก</button>
        </div>
    </form>
</body>

</html>