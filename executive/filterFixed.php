<?php
header('Content-Type: application/json; charset=utf-8');

//ตรวจสอบว่า JSON ที่รับมาถูกต้องหรือไม่
function isValidJSON($str)
{
    json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
}

//รับ JSON จากการกรอก input ในหน้า repair.php
$json_params = file_get_contents("php://input");

?>

<?php

include 'username.php';

//แปลง JSON ที่รับมาให้กลายเป็น array
if (strlen($json_params) > 0 && isValidJSON($json_params))
$json_data = json_decode($json_params,true);

//สร้างไว้เก็บประโยค WHERE
//โดยมีเงื่อนไขคือ ถ้า input ไม่เป็นค่าว่าง ให้เพิ่มเงื่อนไขนั้น ๆ เข้าไปใน WHERE
//ถ้า input เป็นค่าว่าง ไม่ต้องทำอะไร
$whereClauses = array();
if (! empty($json_data['date1']) && ! empty($json_data['date2'])) {
    $whereClauses[0] = "repair_date BETWEEN '$json_data[date1]' AND '$json_data[date2]'";
}

$levelClauses = array();
$level = '';
if (! empty($json_data['level1']) || ! empty($json_data['level2']) || ! empty($json_data['level3'])) {
    if (! empty($json_data['level1'])) {
        $levelClauses[0] = "ambulance_level = '$json_data[level1]'";
    }
    if (! empty($json_data['level2'])) {
        $levelClauses[1] = "ambulance_level = '$json_data[level2]'";
    }
    if (! empty($json_data['level3'])) {
        $levelClauses[2] = "ambulance_level = '$json_data[level3]'";
    }
} 

if (count($levelClauses) > 0) {
    $level = ' ( ' . implode(' OR ', $levelClauses) . ' ) ';
}

if (! empty($json_data['type'])) {
    $whereClauses[4] = "repair_type = '$json_data[type]'";
}
if (! empty($json_data['reason'])) {
    $whereClauses[5] = "repair_reason='$json_data[reason]'";
}
if (! empty($json_data['repairing'])) {
    $whereClauses[6] = "repair_repairing='$json_data[repairing]'";
}
if (! empty($json_data['status'])) {
    $whereClauses[7] = "repair_status='$json_data[status]'";
}
if (! empty($json_data['cost'])) {
    $whereClauses[8] = "( repair_cost $json_data[cost] )";
}

//สร้าง string เก็บประโยค WHERE ตัวเต็มที่ได้จากการรวม $whereClauses แล้วเชื่อมด้วย AND
$where = '';
if (!empty($whereClauses) && !empty($level)) {
    $where = ' WHERE ' . implode(' AND ', $whereClauses) . ' AND ' . $level;
} elseif (!empty($whereClauses)) {
    $where = ' WHERE ' . implode(' AND ', $whereClauses);
} elseif (!empty($level)) {
    $where = ' WHERE ' . $level;
}

$query_all = mysqli_query(
    $conn,
    "SELECT * , 
        SUM(CASE WHEN ambulance_level = '1' THEN 1 ELSE 0 END) AS ambulance_level1,
        SUM(CASE WHEN ambulance_level = '2' THEN 1 ELSE 0 END) AS ambulance_level2,
        SUM(CASE WHEN ambulance_level = '3' THEN 1 ELSE 0 END) AS ambulance_level3
        from repair 
        INNER JOIN ambulance on ambulance.ambulance_id = repair.ambulance_id
        INNER JOIN repair_staff on repair.repair_staff_id = repair_staff.repair_staff_id
        $where
        GROUP BY repair_type"
);

$all_data = mysqli_fetch_all($query_all, MYSQLI_ASSOC);

// ---------------------------------------------------------------------------------
// เตรียมข้อมูลสำหรับแสดงผลในกราฟ
$labels = [];
$level1Data = [];
$level2Data = [];
$level3Data = [];

foreach ($all_data as $row) {
    $labels[] = $row['repair_type'];
    $level1Data[] = $row['ambulance_level1'];
    $level2Data[] = $row['ambulance_level2'];
    $level3Data[] = $row['ambulance_level3'];
}

echo json_encode([
    'labels' => $labels,
    'level1Data' => $level1Data,
    'level2Data' => $level2Data,
    'level3Data' => $level3Data,
]);

// ---------------------------------------------------------------------------------

?>

