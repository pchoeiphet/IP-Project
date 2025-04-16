<?php
session_start();
include 'username.php';
// ฟังก์ชันระบุภูมิภาค
function getRegion($province)
{
    $regions = [
        "ภาคเหนือ" => ["เชียงใหม่", "เชียงราย", "ลำปาง", "ลำพูน", "แพร่", "น่าน", "พะเยา", "แม่ฮ่องสอน", "อุตรดิตถ์", "สุโขทัย", "พิษณุโลก", "ตาก", "เพชรบูรณ์", "นครสวรรค์", "กำแพงเพชร", "พิจิตร", "อุทัยธานี"],
        "ภาคกลาง" => [
            "กรุงเทพมหานคร",
            "สมุทรปราการ",
            "นนทบุรี",
            "ปทุมธานี",
            "พระนครศรีอยุธยา",
            "สระบุรี",
            "ลพบุรี",
            "อ่างทอง",
            "ชัยนาท",
            "สิงห์บุรี",
            "นครนายก",
            "นครปฐม",
            "สุพรรณบุรี",
            "สมุทรสาคร",
            "สมุทรสงคราม",
            "เพชรบุรี",
            "ประจวบคีรีขันธ์",
            "ราชบุรี",
            "กาญจนบุรี",
            // รวมภาคตะวันออก
            "ชลบุรี",
            "ระยอง",
            "จันทบุรี",
            "ตราด",
            "ฉะเชิงเทรา",
            "ปราจีนบุรี",
            "สระแก้ว"
        ],
        "ภาคตะวันออกเฉียงเหนือ" => ["ขอนแก่น", "นครราชสีมา", "อุดรธานี", "อุบลราชธานี", "หนองคาย", "มหาสารคาม", "ร้อยเอ็ด", "สุรินทร์", "บุรีรัมย์", "ศรีสะเกษ", "กาฬสินธุ์", "ชัยภูมิ", "ยโสธร", "สกลนคร", "หนองบัวลำภู", "นครพนม", "บึงกาฬ", "มุกดาหาร", "อำนาจเจริญ"],
        "ภาคใต้" => ["ภูเก็ต", "สุราษฎร์ธานี", "สงขลา", "นราธิวาส", "ยะลา", "ปัตตานี", "พังงา", "กระบี่", "ตรัง", "นครศรีธรรมราช", "พัทลุง", "ชุมพร", "ระนอง", "สตูล"]
    ];



    foreach ($regions as $region => $provinces) {
        if (in_array($province, $provinces)) {
            return $region;
        }
    }
    return "ไม่พบภูมิภาค";
}



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the booking date and time passed via query or POST (for AJAX use)
    $booking_date = $_POST['booking_date'] ?? $_GET['booking_date'];
    $booking_start_time = $_POST['booking_start_time'] ?? $_GET['booking_start_time'];

    if (isset($_POST['submit_event'])) {
        // ประมวลผลฟอร์มงาน Event


        // รับค่าระดับรถจากฟอร์ม
        if (isset($_POST['level'])) {
            $level = $_POST['level']; // รับค่าระดับที่เลือก
            echo "ระดับที่เลือก: " . $level;  // แสดงค่าระดับที่เลือก
        } else {
            echo "กรุณาเลือกระดับรถ";
            exit;
        }
        // ดึง ambulance_id ที่ตรงกับระดับและไม่ได้ถูกจองในวันที่เลือก
        $sql = "SELECT DISTINCT a.ambulance_id 
                FROM ambulance a 
                WHERE a.ambulance_level = '$level' 
                AND a.ambulance_status = 'พร้อม'
                AND a.ambulance_id NOT IN (
                    SELECT ambulance_id 
                    FROM event_booking 
                    WHERE event_booking_date = '$booking_date'
                    AND event_booking_start_time = '$booking_start_time'
                    UNION
                    SELECT ambulance_id 
                    FROM ambulance_booking 
                    WHERE ambulance_booking_date = '$booking_date'
                    AND ambulance_booking_start_time = '$booking_start_time'
                )";
        $result = $conn->query($sql);

        // Debug statements เพื่อตรวจสอบค่า
        echo "<script>console.log('Date: " . $booking_date . "');</script>";
        echo "<script>console.log('Time: " . $booking_start_time . "');</script>";
        echo "<script>console.log('SQL: " . $sql . "');</script>";

        if ($result->num_rows > 0) {
            // สร้าง array สำหรับ ambulance_ids
            $ambulance_ids = [];
            while ($row = $result->fetch_assoc()) {
                $ambulance_ids[] = $row['ambulance_id'];
            }
            // เลือก ambulance_id แบบสุ่มจาก array
            $ambulance_id = $ambulance_ids[array_rand($ambulance_ids)];
            echo "ambulance_id ที่เลือก: " . $ambulance_id; // แสดง ambulance_id ที่สุ่ม
        } else {
            echo "<script>
                    alert('ไม่พบรถพยาบาลระดับ " . $level . " ที่ว่างในวันที่ " . $booking_date . " เวลา " . $booking_start_time . "');
                    window.history.back();
                  </script>";
            exit;
        }

        $province = $_POST['province1'];
        $region = getRegion($province); // ตรวจสอบภูมิภาคจากจังหวัด
        $price = $_POST['calculatedPrice1'];
        $distance = $_POST['calculatedDistance1'];
        $place_event_location = $_POST['searchEventLocation'];
        $place_event_detail = $_POST['place_event_detail'];
        $type = $_POST['event_type'];
        $nurse_number = $_POST['nurse_number'];
        $ambulance_number = $_POST['ambulance_number'];
        $payment_method = $_POST['payment_method_event'];
        $member_id = $_SESSION['user_id']; // ได้ค่า member_id จริงที่ล็อกอิน

        $sql = "INSERT INTO event_booking (member_id,ambulance_id,event_booking_date,event_booking_start_time,event_booking_province,event_booking_region,event_booking_location, event_booking_detail, event_booking_type,event_booking_amount_nurse, event_booking_amount_ambulance,event_booking_buy_type,event_booking_price,event_booking_distance) 
                VALUES ('$member_id','$ambulance_id','$booking_date','$booking_start_time','$province','$region','$place_event_location','$place_event_detail', '$type', '$nurse_number', '$ambulance_number', '$payment_method','$price','$distance')";


        if ($conn->query($sql) === TRUE) {
            echo "ข้อมูลถูกบันทึกเรียบร้อยแล้ว";
            $price_total = $price; // หรือคำนวณเพิ่มถ้ามีส่วนลด
            header("Location: QRPayment.php?price_total=" . urlencode($price_total));
        } else {
            echo "เกิดข้อผิดพลาด: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['submit_ambulance'])) {
        //รับค่าจากฟอร์ม

        // รับค่าระดับรถจากฟอร์ม
        if (isset($_POST['level'])) {
            $level = $_POST['level']; // รับค่าระดับที่เลือก
            echo "ระดับที่เลือก: " . $level;  // แสดงค่าระดับที่เลือก
        } else {
            echo "กรุณาเลือกระดับรถ";
            exit;
        }
        // ดึง ambulance_id ที่ตรงกับระดับและไม่ได้ถูกจองในวันที่เลือก
        $sql = "SELECT DISTINCT a.ambulance_id 
                FROM ambulance a 
                WHERE a.ambulance_level = '$level' 
                AND a.ambulance_status = 'พร้อม'
                AND a.ambulance_id NOT IN (
                    SELECT ambulance_id 
                    FROM event_booking 
                    WHERE event_booking_date = '$booking_date'
                    AND event_booking_start_time = '$booking_start_time'
                    UNION
                    SELECT ambulance_id 
                    FROM ambulance_booking 
                    WHERE ambulance_booking_date = '$booking_date'
                    AND ambulance_booking_start_time = '$booking_start_time'
                )";
        $result = $conn->query($sql);

        // Debug statements เพื่อตรวจสอบค่า
        echo "<script>console.log('Date: " . $booking_date . "');</script>";
        echo "<script>console.log('Time: " . $booking_start_time . "');</script>";
        echo "<script>console.log('SQL: " . $sql . "');</script>";

        if ($result->num_rows > 0) {
            // สร้าง array สำหรับ ambulance_ids
            $ambulance_ids = [];
            while ($row = $result->fetch_assoc()) {
                $ambulance_ids[] = $row['ambulance_id'];
            }
            // เลือก ambulance_id แบบสุ่มจาก array
            $ambulance_id = $ambulance_ids[array_rand($ambulance_ids)];
            echo "ambulance_id ที่เลือก: " . $ambulance_id; // แสดง ambulance_id ที่สุ่ม
        } else {
            echo "<script>
                    alert('ไม่พบรถพยาบาลระดับ " . $level . " ที่ว่างในวันที่ " . $booking_date . " เวลา " . $booking_start_time . "');
                    window.history.back();
                  </script>";
            exit;
        }

        $pickup_location = $_POST['searchPatientLocation'];
        $place_ambulance_detail = $_POST['place_ambulance_detail'];
        $hospital = $_POST["hospital"];
        $province = $_POST["province2"];
        $symptom = $_POST["symptom"];
        $allergy = $_POST["allergy"];
        $payment_method = $_POST['payment_method_hospital'];
        $region = getRegion($province); // ตรวจสอบภูมิภาคจากจังหวัด
        $price = $_POST['calculatedPrice2'];
        $distance = $_POST['calculatedDistance2'];
        $member_id = $_SESSION['user_id']; // ✅ เพิ่มบรรทัดนี้
        $ambulance_status = "รอปฏิบัติหน้าที่";
        // Mapping hospital codes to names
        $hospitalMap = [
            "hospital1" => "โรงพยาบาลมหาวิทยาลัยนเรศวร",
            "hospital2" => "โรงพยาบาลจุฬาลงกรณ์",
            "hospital3" => "โรงพยาบาลกรุงเทพมหานคร",
            "hospital4" => "โรงพยาบาลพระมงกุฎเกล้า"
        ];
        // Mapping symtom codes to names
        $symptomMap = [
            "symptom1" => "เกี่ยวกับระบบทางเดินหายใจ",
            "symptom2" => "เกี่ยวกับระบบไหลเวียนเลือด",
            "symptom3" => "เกี่ยวกับกล้ามเนื้อและกระดูก",
            "symptom4" => "โรคเรื้อรัง",
            "symptom5" => "สุขภาพจิต"
        ];
        $allergyMap = [
            "allergy1" => "อาหารทะเล",
            "allergy2" => "นมวัว",
            "allergy3" => "ถั่วลิสง",
            "allergy4" => "ไข่",
            "allergy5" => "ยาปฏิชีวนะ",
            "allergy6" => "ยาชา"
        ];
        $provinceMap = [
            "province1" => "กรุงเทพมหานคร",
            "province2" => "กระบี่",
            "province3" => "กาญจนบุรี",
            "province4" => "กาฬสินธุ์",
            "province5" => "กำแพงเพชร",
            "province6" => "ขอนแก่น",
            "province7" => "จันทบุรี",
            "province8" => "ฉะเชิงเทรา",
            "province9" => "ชลบุรี",
            "province10" => "ชัยนาท",
            "province11" => "ชัยภูมิ",
            "province12" => "ชุมพร",
            "province13" => "เชียงราย",
            "province14" => "เชียงใหม่",
            "province15" => "ตรัง",
            "province16" => "ตราด",
            "province17" => "ตาก",
            "province18" => "นครนายก",
            "province19" => "นครปฐม",
            "province20" => "นครพนม",
            "province21" => "นครราชสีมา",
            "province22" => "นครศรีธรรมราช",
            "province23" => "นครสวรรค์",
            "province24" => "นนทบุรี",
            "province25" => "นราธิวาส",
            "province26" => "น่าน",
            "province27" => "บึงกาฬ",
            "province28" => "บุรีรัมย์",
            "province29" => "ปทุมธานี",
            "province30" => "ประจวบคีรีขันธ์",
            "province31" => "ปราจีนบุรี",
            "province32" => "ปัตตานี",
            "province33" => "พะเยา",
            "province34" => "พระนครศรีอยุธยา",
            "province35" => "พังงา",
            "province36" => "พัทลุง",
            "province37" => "พิจิตร",
            "province38" => "พิษณุโลก",
            "province39" => "เพชรบุรี",
            "province40" => "เพชรบูรณ์",
            "province41" => "แพร่",
            "province42" => "ภูเก็ต",
            "province43" => "มหาสารคาม",
            "province44" => "มุกดาหาร",
            "province45" => "แม่ฮ่องสอน",
            "province46" => "ยโสธร",
            "province47" => "ยะลา",
            "province48" => "ร้อยเอ็ด",
            "province49" => "ระนอง",
            "province50" => "ระยอง",
            "province51" => "ราชบุรี",
            "province52" => "ลพบุรี",
            "province53" => "ลำปาง",
            "province54" => "ลำพูน",
            "province55" => "เลย",
            "province56" => "ศรีสะเกษ",
            "province57" => "สกลนคร",
            "province58" => "สงขลา",
            "province59" => "สตูล",
            "province60" => "สมุทรปราการ",
            "province61" => "สมุทรสงคราม",
            "province62" => "สมุทรสาคร",
            "province63" => "สระแก้ว",
            "province64" => "สระบุรี",
            "province65" => "สิงห์บุรี",
            "province66" => "สุโขทัย",
            "province67" => "สุพรรณบุรี",
            "province68" => "สุราษฎร์ธานี",
            "province69" => "สุรินทร์",
            "province70" => "หนองคาย",
            "province71" => "หนองบัวลำภู",
            "province72" => "อ่างทอง",
            "province73" => "อำนาจเจริญ",
            "province74" => "อุดรธานี",
            "province75" => "อุตรดิตถ์",
            "province76" => "อุทัยธานี",
            "province77" => "อุบลราชธานี"
        ];
        // ตรวจสอบและแทนค่าชื่อโรงพยาบาล
        $hospital = $hospitalMap[$hospital] ?? $hospital;
        //ตรวจสอบและแทนค่าชื่อโรค
        $symptom = $symptomMap[$symptom] ?? $symptom;
        //ตรวจสอบและแทนชื่อการแพ้ยา/อาหาร
        $allergy = $allergyMap[$allergy] ?? $allergy;
        //ตรวจสอบและแทนชื่อจังหวัด
        $province = $provinceMap[$province] ?? $province;
        // สร้างคำสั่ง SQL

        $member_id = $_SESSION['user_id']; // ✅ เพิ่มบรรทัดนี้

        $sql = "INSERT INTO ambulance_booking 
                    (member_id,ambulance_id,ambulance_booking_date,ambulance_booking_start_time,ambulance_booking_location, ambulance_booking_hospital_waypoint, ambulance_booking_province,ambulance_booking_region, ambulance_booking_disease, ambulance_booking_allergy_medicine,ambulance_booking_buy_type,ambulance_booking_price,ambulance_booking_detail,ambulance_booking_distance,ambulance_booking_status) 
                    VALUES ('$member_id','$ambulance_id','$booking_date','$booking_start_time','$pickup_location', '$hospital', '$province','$region', '$symptom', '$allergy','$payment_method','$price','$place_ambulance_detail','$distance','$ambulance_status')";
        // บันทึกข้อมูล
        if ($conn->query($sql) === TRUE) {
            echo "ข้อมูลถูกบันทึกเรียบร้อยแล้ว";
            $price_total = $price; // หรือคำนวณเพิ่มถ้ามีส่วนลด
            header("Location: QRPayment.php?price_total=" . urlencode($price_total));
        } else {
            echo "เกิดข้อผิดพลาด" . $sql . "<br>" . $conn->error;
        }
        // ปิดการเชื่อมต่อ
        $conn->close();
    }
}
