<?php
// ตั้งค่า Content-Type ให้รองรับภาษาไทยและ UTF-8
header('Content-Type: application/text; charset=utf-8');

// ฟังก์ชันตรวจสอบว่า string ที่รับมาเป็น JSON ที่ถูกต้องหรือไม่
function isValidJSON($str)
{
    json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
}

// รับ JSON จาก `php://input` (ใช้กรณี fetch หรือ axios ส่งข้อมูลมาแบบ raw JSON)
$json_params = file_get_contents("php://input");
?>


<?php
include 'con_repair.php'; // เชื่อมต่อฐานข้อมูล

// ถ้ามีข้อมูล JSON และเป็น JSON ที่ถูกต้อง
if (strlen($json_params) > 0 && isValidJSON($json_params)) {
    // แปลง JSON เป็น array
    $json_data = json_decode($json_params, true);
}

// เตรียม array สำหรับเก็บเงื่อนไข WHERE
$whereClauses = array();

// เพิ่มเงื่อนไขตามข้อมูลที่กรอกเข้ามา (ถ้ามี)
if (! empty($json_data['date'])) {
    $whereClauses[] = "repair_date = '$json_data[date]'";
}
if (! empty($json_data['ambuID'])) {
    $whereClauses[] = "ambulance_id='$json_data[ambuID]'";
}
if (! empty($json_data['status'])) {
    $whereClauses[] = "repair_status='$json_data[status]'";
}

// เงื่อนไขเพิ่มเติม: ต้องมีการกรอกสาเหตุ และต้องเป็นรายการที่ยังไม่เสร็จสิ้น
$whereClauses[] = "repair_reason IS NOT NULL AND repair_reason <> ''";
$whereClauses[] = "repair_status IN ('รอดำเนินการ', 'กำลังดำเนินการ')";

// รวมเงื่อนไขทั้งหมดด้วย AND เป็นคำสั่ง WHERE
$where = '';
if (count($whereClauses) > 0) {
    $where = ' WHERE ' . implode(' AND ', $whereClauses);
}

// query ข้อมูลจากฐานข้อมูลตามเงื่อนไขที่กำหนด
$query_result = mysqli_query($conn, "SELECT * FROM repair $where");

// เก็บผลลัพธ์ทั้งหมดไว้ในตัวแปร $repair_data เป็น array
$repair_data = mysqli_fetch_all($query_result, MYSQLI_ASSOC);
?>


<table>
    <thead>
        <tr>
            <th>วันที่รับซ่อม</th>
            <th>ID รถพยาบาล</th>
            <th>ประเภทการซ่อม</th>
            <th>อุปกรณ์/อะไหล่</th>
            <th>สาเหตุ</th>
            <th>วันที่เสร็จสิ้น</th>
            <th>ราคาซ่อม</th>
            <th>ID ผู้รายงาน</th>
            <th>สถานะการซ่อม</th>
        </tr>
    </thead>
    <tbody id="repair-table-body">
        <!-- วนลูปแสดงข้อมูลแต่ละแถว -->
        <?php foreach ($repair_data as $rs_result) { ?>
            <tr>
                <td><?php echo $rs_result['repair_date']; ?></td>
                <td><?php echo $rs_result['ambulance_id']; ?></td>
                <td><?php echo $rs_result['repair_type']; ?></td>
                <td><?php echo $rs_result['repair_repairing']; ?></td>
                <td><?php echo $rs_result['repair_reason']; ?></td>
                <td>
                    <input type="date"
                        value="<?= $rs_result['repair_success_datetime'] ? substr($rs_result['repair_success_datetime'], 0, 10) : '' ?>"
                        min="<?= $rs_result['repair_date']; ?>"
                        data-repair-date="<?= $rs_result['repair_date']; ?>"
                        onchange="validateAndUpdateRepairDate(this, <?= $rs_result['repair_id']; ?>)">
                </td>
                <td>
                    <input type="number" min="0"
                        value="<?= $rs_result['repair_cost'] ?>"
                        onchange="updateRepair(<?= $rs_result['repair_id'] ?>, this.value, 'cost')"> ฿
                </td>
                <td><?php echo $rs_result['repair_staff_id']; ?></td>
                <td>
                    <!-- เลือกสถานะตามสถานะปัจจุบัน -->
                    <?php if ($rs_result['repair_status'] == 'เสร็จสิ้น') { ?>
                        <!-- ถ้าซ่อมเสร็จแล้ว ห้ามแก้ไข -->
                        <select disabled>
                            <option value="เสร็จสิ้น" selected>เสร็จสิ้น</option>
                        </select>
                    <?php } elseif ($rs_result['repair_status'] == 'กำลังดำเนินการ') { ?>
                        <!-- ถ้ากำลังซ่อม อนุญาตให้เปลี่ยนเป็นเสร็จสิ้นเท่านั้น -->
                        <select onchange="updateRepair(<?php echo $rs_result['repair_id']; ?>, this.value, 'status')">
                            <option disabled value="กำลังดำเนินการ" selected>กำลังดำเนินการ</option>
                            <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                        </select>
                    <?php } else { ?>
                        <!-- ถ้าเป็น "รอดำเนินการ" ให้เลือกได้ทุกสถานะ -->
                        <select onchange="updateRepair(<?php echo $rs_result['repair_id']; ?>, this.value, 'status')">
                            <option value="รอดำเนินการ" <?php echo ($rs_result['repair_status'] == 'รอดำเนินการ') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="กำลังดำเนินการ" <?php echo ($rs_result['repair_status'] == 'กำลังดำเนินการ') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                            <option value="เสร็จสิ้น" <?php echo ($rs_result['repair_status'] == 'เสร็จสิ้น') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                        </select>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>