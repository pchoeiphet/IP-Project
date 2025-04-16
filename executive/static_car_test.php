<?php
include('username.php');

// เริ่มต้นรับค่าจาก filter
$province = isset($_POST['province']) ? $_POST['province'] : '';  // รับค่าจังหวัด
if (is_array($province)) {
    $province = implode(',', $province);  // แปลงเป็น string ถ้าเป็นอาร์เรย์
}

$gender = isset($_POST['gender']) ? $_POST['gender'] : '';  // รับค่าเพศ
if (is_array($gender)) {
    $gender = implode(',', $gender);  // แปลงเป็น string ถ้าเป็นอาร์เรย์
}

$min_age = isset($_POST['min_age']) ? $_POST['min_age'] : 0;  // รับค่าช่วงอายุ (ขั้นต่ำ)
$max_age = isset($_POST['max_age']) ? $_POST['max_age'] : 120;  // รับค่าช่วงอายุ (สูงสุด)

$region = isset($_POST['region']) ? $_POST['region'] : '';  // รับค่าภูมิภาค
if (is_array($region)) {
    $region = implode(',', $region);  // แปลงเป็น string ถ้าเป็นอาร์เรย์
}

$month_year = isset($_POST['month_year']) ? $_POST['month_year'] : '';  // รับค่าปี/เดือน


// สร้าง SQL ตามค่าที่กรองจากฟอร์ม
$sql = "
SELECT 
    m.member_firstname, 
    m.member_birthdate,
    m.member_gender,
    merged.member_id,
    merged.ambulance_id,
    merged.booking_date,
    merged.province,
    merged.region,
    merged.source,
    IF(merged.source = 'emergency', 
        merged.emergency_case_patient_gender,  -- ดึงเพศจาก emergency_case_report_patient_gender
        m.member_gender) AS gender,  -- ดึงเพศจาก member
    IF(merged.source = 'emergency', 
        merged.emergency_case_patient_age,  -- ดึงอายุจาก emergency_case_patient_age
        TIMESTAMPDIFF(YEAR, m.member_birthdate, CURDATE())) AS age,
    a.ambulance_level -- เพิ่มข้อมูลจากตาราง ambulance
FROM (
    SELECT ab.member_id, ab.ambulance_id, ab.ambulance_booking_date AS booking_date, 
           ab.ambulance_booking_province AS province, ab.ambulance_booking_region AS region, 
           'ambulance' AS source, NULL AS emergency_case_patient_age, NULL AS emergency_case_patient_gender
    FROM ambulance_booking AS ab
    UNION
    SELECT eb.member_id, eb.ambulance_id, eb.event_booking_date AS booking_date, 
           eb.event_booking_province AS province, eb.event_booking_region AS region, 
           'event' AS source, NULL AS emergency_case_patient_age, NULL AS emergency_case_patient_gender
    FROM event_booking AS eb
    UNION
    SELECT ecr.emergency_case_report_id, ecr.ambulance_id, ecr.emergency_case_report_date AS booking_date, 
           'กรุงเทพมหานคร' AS province, 'ภาคกลาง' AS region, 
           'emergency' AS source, ecr.emergency_case_report_patient_age, ecr.emergency_case_report_patient_gender
    FROM emergency_case_report AS ecr
) AS merged
LEFT JOIN member AS m ON merged.member_id = m.member_id
LEFT JOIN ambulance AS a ON merged.ambulance_id = a.ambulance_id -- เชื่อมกับตาราง ambulance
WHERE 1=1
";
if (!empty($province) && $province != 'ทั้งหมด') {
    $sql .= " AND merged.province IN ('$province') ";
}

if (!empty($gender) && $gender != 'ทั้งหมด') {
    $sql .= " AND m.member_gender IN ('$gender') ";
}

if ($min_age > 0 || $max_age < 120) {
    $sql .= " AND TIMESTAMPDIFF(YEAR, m.member_birthdate, CURDATE()) BETWEEN $min_age AND $max_age ";
}

if (!empty($region)) {
    $sql .= " AND merged.region IN ('$region') ";
}

if (!empty($month_year)) {
    $sql .= " AND DATE_FORMAT(merged.booking_date, '%Y-%m') = '$month_year' ";
}



$result = $conn->query($sql);
$conn->close();

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <!-- เพิ่มเนื้อหา HTML ตามปกติ -->
</head>

<body>
    <!-- ฟอร์ม Filter -->
    <form method="post" action="">
        <label for="province">จังหวัด:</label>
        <select name="province">
            <option value="ทั้งหมด">ทั้งหมด</option>
            <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
            <option value="กระบี่">กระบี่</option>
            <option value="กาญจนบุรี">กาญจนบุรี</option>
            <option value="กาฬสินธุ์">กาฬสินธุ์</option>
            <option value="กำแพงเพชร">กำแพงเพชร</option>
            <option value="ขอนแก่น">ขอนแก่น</option>
            <option value="จันทบุรี">จันทบุรี</option>
            <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
            <option value="ชลบุรี">ชลบุรี</option>
            <option value="ชัยนาท">ชัยนาท</option>
            <option value="ชัยภูมิ">ชัยภูมิ</option>
            <option value="ชุมพร">ชุมพร</option>
            <option value="เชียงราย">เชียงราย</option>
            <option value="เชียงใหม่">เชียงใหม่</option>
            <option value="ตรัง">ตรัง</option>
            <option value="ตราด">ตราด</option>
            <option value="ตาก">ตาก</option>
            <option value="นครนายก">นครนายก</option>
            <option value="นครปฐม">นครปฐม</option>
            <option value="นครพนม">นครพนม</option>
            <option value="นครราชสีมา">นครราชสีมา</option>
            <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
            <option value="นครสวรรค์">นครสวรรค์</option>
            <option value="นนทบุรี">นนทบุรี</option>
            <option value="นราธิวาส">นราธิวาส</option>
            <option value="น่าน">น่าน</option>
            <option value="บึงกาฬ">บึงกาฬ</option>
            <option value="บุรีรัมย์">บุรีรัมย์</option>
            <option value="ปทุมธานี">ปทุมธานี</option>
            <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
            <option value="ปราจีนบุรี">ปราจีนบุรี</option>
            <option value="ปัตตานี">ปัตตานี</option>
            <option value="พะเยา">พะเยา</option>
            <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
            <option value="พังงา">พังงา</option>
            <option value="พัทลุง">พัทลุง</option>
            <option value="พิจิตร">พิจิตร</option>
            <option value="พิษณุโลก">พิษณุโลก</option>
            <option value="เพชรบุรี">เพชรบุรี</option>
            <option value="เพชรบูรณ์">เพชรบูรณ์</option>
            <option value="แพร่">แพร่</option>
            <option value="ภูเก็ต">ภูเก็ต</option>
            <option value="มหาสารคาม">มหาสารคาม</option>
            <option value="มุกดาหาร">มุกดาหาร</option>
            <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
            <option value="ยโสธร">ยโสธร</option>
            <option value="ยะลา">ยะลา</option>
            <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
            <option value="ระนอง">ระนอง</option>
            <option value="ระยอง">ระยอง</option>
            <option value="ราชบุรี">ราชบุรี</option>
            <option value="ลพบุรี">ลพบุรี</option>
            <option value="ลำปาง">ลำปาง</option>
            <option value="ลำพูน">ลำพูน</option>
            <option value="เลย">เลย</option>
            <option value="ศรีสะเกษ">ศรีสะเกษ</option>
            <option value="สกลนคร">สกลนคร</option>
            <option value="สงขลา">สงขลา</option>
            <option value="สตูล">สตูล</option>
            <option value="สมุทรปราการ">สมุทรปราการ</option>
            <option value="สมุทรสงคราม">สมุทรสงคราม</option>
            <option value="สมุทรสาคร">สมุทรสาคร</option>
            <option value="สระแก้ว">สระแก้ว</option>
            <option value="สระบุรี">สระบุรี</option>
            <option value="สิงห์บุรี">สิงห์บุรี</option>
            <option value="สุโขทัย">สุโขทัย</option>
            <option value="สุพรรณบุรี">สุพรรณบุรี</option>
            <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
            <option value="สุรินทร์">สุรินทร์</option>
            <option value="หนองคาย">หนองคาย</option>
            <option value="หนองบัวลำภู">หนองบัวลำภู</option>
            <option value="อ่างทอง">อ่างทอง</option>
            <option value="อุดรธานี">อุดรธานี</option>
            <option value="อุตรดิตถ์">อุตรดิตถ์</option>
            <option value="อุทัยธานี">อุทัยธานี</option>
            <option value="อุบลราชธานี">อุบลราชธานี</option>
            <option value="อำนาจเจริญ">อำนาจเจริญ</option>
            <!-- เพิ่มตัวเลือกอื่น ๆ ตามต้องการ -->
        </select>

        <label for="gender">เพศ:</label>
        <select name="gender">
            <option value="ทั้งหมด">ทั้งหมด</option>
            <option value="ชาย">ชาย</option>
            <option value="หญิง">หญิง</option>
        </select>

        <label for="min_age">อายุ (ต่ำสุด):</label>
        <input type="number" name="min_age" value="1" min="1" max="120">

        <label for="max_age">อายุ (สูงสุด):</label>
        <input type="number" name="max_age" value="120" min="1" max="120">

        <label for="region">ภูมิภาค:</label>
        <input type="checkbox" name="region[]" value="ภาคเหนือ"> ภาคเหนือ
        <input type="checkbox" name="region[]" value="ภาคกลาง"> ภาคกลาง
        <input type="checkbox" name="region[]" value="ภาคใต้"> ภาคใต้

        <!-- เพิ่มตัวเลือกภูมิภาคอื่น ๆ ตามต้องการ -->

        <label for="month_year">ปี/เดือน:</label>
        <input type="month" name="month_year" value="2025-01">

        <input type="submit" value="กรองข้อมูล">
    </form>

    <div class="content">
        <?php
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Member ID</th><th>เพศ</th><th>Age</th><th>Source</th><th>Ambulance ID</th><th>ระดับรถ</th><th>Booking Date</th><th>Province</th><th>Region</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . ($row['member_id'] ?? 'N/A') . "</td>
                        <td>" . ($row['gender'] ?? 'Unknown') . "</td>
                        <td>" . ($row['age'] ?? 'N/A') . "</td>
                        <td>{$row['source']}</td>
                        <td>" . ($row['ambulance_id'] ?? 'N/A') . "</td>
                        <td>" . ($row['ambulance_level'] ?? 'N/A') . "</td>
                        <td>{$row['booking_date']}</td>
                        <td>{$row['province']}</td>
                        <td>{$row['region']}</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "No results found.";
        }
        ?>
    </div>
</body>

</html>