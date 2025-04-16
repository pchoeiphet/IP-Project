<?php
include 'connect.php';
?>

<?php
session_start();

//เลือกให้ผู้กรอกข้อมูลเป็น callcenter_id = 1
$callcenter_query = mysqli_query($conn, "SELECT callcenter_staff_id FROM callcenter_staff WHERE callcenter_staff_id = '1'");
$callcenter_row = mysqli_fetch_assoc($callcenter_query); //เก็บเป็น array 
$callcenter_id = $_SESSION['user_id']; //เลือก callcenter_id จาก array 

$ambulance_id = $_POST['ambulance_id'];

$accident_location = $_POST['start-point'];
$report_reason = $_POST['cause'];
$order_status ='ยังไม่ชำระเงิน';
//ถ้าเลือกอื่นๆ ให้ใช้ค่าจากช่อง input มาเก็บใน report_reason
if ($report_reason == "other") {
    $report_reason = $_POST['other-cause'];
}
$hospital_waypoint = $_POST['hospital'];

date_default_timezone_set('Asia/Bangkok'); //ตั้งให้เป็นเวลาไทย
$report_date = date('Y-m-d'); //ปี เดือน วัน
$report_time = date('H:i:s'); //ชั่วโมง นาที วินาที

$emergency_case_zone = $_POST['district'];
$report_communicant = $_POST['contact'];
$report_communicant_phone = $_POST['contact_number'];
$report_patient_name = $_POST['patient_name'];
$report_patient_age = $_POST['patient_age'];
$report_patient_gender = $_POST['gender'];
$order_emergency_case_price = $_POST['cost'];

if (!mysqli_query(
    $conn,
    "INSERT INTO order_emergency_case (ambulance_id,callcenter_staff_id ,order_emergency_case_price,order_emergency_case_status, order_emergency_case_accident_location, order_emergency_case_reason, order_emergency_case_hospital_waypoint, order_emergency_case_date, order_emergency_case_time, order_emergency_case_zone, order_emergency_case_communicant, order_emergency_case_communicant_phone, order_emergency_case_patient_name, order_emergency_case_patient_gender, order_emergency_case_patient_age) 
    VALUES ('$ambulance_id', '$callcenter_id','$order_emergency_case_price','$order_status', '$accident_location', '$report_reason', '$hospital_waypoint', '$report_date', '$report_time', '$emergency_case_zone', '$report_communicant', '$report_communicant_phone', '$report_patient_name','$report_patient_gender', '$report_patient_age')"
)) {
    echo ("Error description: " . mysqli_error($conn)); //ส่งข้อมูลไม่สำเร็จให้แสดง error
} else {
    require_once('emergency_report_success.php'); //ส่งข้อมูลสำเร็จให้เปิดหน้า emergency_report_success.html
}

?>
