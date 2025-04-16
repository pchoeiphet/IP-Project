<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style_car_report.css">
    <script src="./script_car_report.js"></script>
    <title>รายงานสภาพรถ</title>

</head>
<body>
<nav>
    <ul class="menu">
        <li><a href="car_report.php">รายงานสภาพรถพยาบาล</a></li>
        <li><a href="../repair/repair.php">การซ่อมอุปกรณ์และรถพยาบาล</a></li>
    </ul>
</nav>

    <div class="header">
        <h1 class="title">รายงานสภาพรถพยาบาล</h1>
    </div>

    <form id="carReportForm" action="con_report.php" method="post">
        <div class="car-info">
            <div class="form">
                <label for="level">ระดับรถ</label>
                <select id="level" name="level_car" required>
                    <option value="" disabled selected>ระบุระดับรถ</option>
                    <option value="ระดับ 1">ระดับ 1</option>
                    <option value="ระดับ 2">ระดับ 2</option>
                    <option value="ระดับ 3">ระดับ 3</option>
                </select>
            </div>
            <div class="form">
                <label for="number">ทะเบียนรถ</label>
                <select id="number" name="registration_car" required>
                    <option value="" disabled selected>ระบุทะเบียนรถ</option>
                </select>
            </div>
        </div>

        <!-- รายงานเกี่ยวกับรถ -->
        <div class="car_topic">
            <h3>รถพยาบาล</h3>
            <div class="car_report">
                <div class="form-group">
                    <label for="clean">ความสะอาด</label>
                    <input type="radio" name="status[ความสะอาด]" value="พร้อม" id="clean-yes" onclick="toggleDropdown('status[ความสะอาด]')"
                        required> พร้อม
                    <input type="radio" name="status[ความสะอาด]" value="ไม่พร้อม" id="clean-no" onclick="toggleDropdown('status[ความสะอาด]')"
                        required> ไม่พร้อม
                    <select id="status[ความสะอาด]-dropdown" name="reason[ความสะอาด]" disabled onchange="updateDropdown('status[ความสะอาด]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="มีกลิ่นอับ">มีกลิ่นอับ</option>
                        <option value="มีคราบสกปรก">มีคราบสกปรก</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[ความสะอาด]-other" name="reason[ความสะอาด]" placeholder="ระบุ" disabled oninput="updateOtherField('status[ความสะอาด]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="engine">เครื่องยนต์</label>
                    <input type="radio" name="status[เครื่องยนต์]" value="พร้อม" id="engine-yes" onclick="toggleDropdown('status[เครื่องยนต์]')"
                        required> พร้อม
                    <input type="radio" name="status[เครื่องยนต์]" value="ไม่พร้อม" id="engine-no" onclick="toggleDropdown('status[เครื่องยนต์]')"
                        required> ไม่พร้อม
                    <select id="status[เครื่องยนต์]-dropdown" name="reason[เครื่องยนต์]" disabled onchange="updateDropdown('status[เครื่องยนต์]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="เสีย">เสีย</option>
                        <option value="ไหม้">ไหม้</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เครื่องยนต์]-other" name="reason[เครื่องยนต์]" placeholder="ระบุ" disabled
                        oninput="updateOtherField('status[เครื่องยนต์]')" required>
                </div>

                <div class="form-group">
                    <label for="wheel">ล้อรถ</label>
                    <input type="radio" name="status[ล้อรถ]" value="พร้อม" id="wheel-yes" onclick="toggleDropdown('status[ล้อรถ]')"
                        required> พร้อม
                    <input type="radio" name="status[ล้อรถ]" value="ไม่พร้อม" id="wheel-no" onclick="toggleDropdown('status[ล้อรถ]')"
                        required> ไม่พร้อม
                    <select id="status[ล้อรถ]-dropdown" name="reason[ล้อรถ]" disabled onchange="updateDropdown('status[ล้อรถ]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ยางรั่ว">ยางรั่ว</option>
                        <option value="ดอกยางหมด">ดอกยางหมด</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[ล้อรถ]-other" name="reason[ล้อรถ]" placeholder="ระบุ" disabled oninput="updateOtherField('status[ล้อรถ]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="door">ประตูรถ</label>
                    <input type="radio" name="status[ประตูรถ]" value="พร้อม" id="door-yes" onclick="toggleDropdown('status[ประตูรถ]')" required>
                    พร้อม
                    <input type="radio" name="status[ประตูรถ]" value="ไม่พร้อม" id="door-no" onclick="toggleDropdown('status[ประตูรถ]')" required>
                    ไม่พร้อม
                    <select id="status[ประตูรถ]-dropdown" name="reason[ประตูรถ]" disabled onchange="updateDropdown('status[ประตูรถ]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="เปิดไม่ได้">เปิดไม่ได้</option>
                        <option value="ประตูหลุด">ประตูหลุด</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[ประตูรถ]-other" name="reason[ประตูรถ]" placeholder="ระบุ" disabled oninput="updateOtherField('status[ประตูรถ]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="brake">เบรก</label>
                    <input type="radio" name="status[เบรก]" value="พร้อม" id="brake-yes" onclick="toggleDropdown('status[เบรก]')"
                        required> พร้อม
                    <input type="radio" name="status[เบรก]" value="ไม่พร้อม" id="brake-no" onclick="toggleDropdown('status[เบรก]')"
                        required> ไม่พร้อม
                    <select id="status[เบรก]-dropdown" name="reason[เบรก]" disabled onchange="updateDropdown('status[เบรก]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="สายเบรคขาด">สายเบรคขาด</option>
                        <option value="เบรคไหม้">เบรคไหม้</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เบรก]-other" name="reason[เบรก]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เบรก]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="light">ไฟรถ</label>
                    <input type="radio" name="status[ไฟรถ]" value="พร้อม" id="light-yes" onclick="toggleDropdown('status[ไฟรถ]')"
                        required> พร้อม
                    <input type="radio" name="status[ไฟรถ]" value="ไม่พร้อม" id="light-no" onclick="toggleDropdown('status[ไฟรถ]')"
                        required> ไม่พร้อม
                    <select id="status[ไฟรถ]-dropdown" name="reason[ไฟรถ]" disabled onchange="updateDropdown('status[ไฟรถ]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="เปิดไม่ติด">เปิดไม่ติด</option>
                        <option value="หลอดไฟแตก">หลอดไฟแตก</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[ไฟรถ]-other" name="reason[ไฟรถ]" placeholder="ระบุ" disabled oninput="updateOtherField('status[ไฟรถ]')"
                        required>

                </div>
            </div>
        </div>

        <!-- รายงานเกี่ยวกับอุปกรณ์บนรถ -->
        <div class="kit_topic">
            <h3>อุปกรณ์การแพทย์</h3>
            <div class="car_report">
                <div class="form-group">
                    <label for="AED">เครื่องAED</label>
                    <input type="radio" name="status[เครื่องAED]" value="พร้อม" id="AED-yes" onclick="toggleDropdown('status[เครื่องAED]')" required>
                    พร้อม
                    <input type="radio" name="status[เครื่องAED]" value="ไม่พร้อม" id="AED-no" onclick="toggleDropdown('status[เครื่องAED]')" required>
                    ไม่พร้อม
                    <select id="status[เครื่องAED]-dropdown" name="reason[เครื่องAED]" disabled onchange="updateDropdown('status[เครื่องAED]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ชำรุด">ชำรุด</option>
                        <option value="หมดอายุ">หมดอายุ</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เครื่องAED]-other" name="reason[เครื่องAED]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เครื่องAED]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="ven">เครื่องช่วยหายใจ</label>
                    <input type="radio" name="status[เครื่องช่วยหายใจ]" value="พร้อม" id="ven-yes" onclick="toggleDropdown('status[เครื่องช่วยหายใจ]')" required>
                    พร้อม
                    <input type="radio" name="status[เครื่องช่วยหายใจ]" value="ไม่พร้อม" id="ven-no" onclick="toggleDropdown('status[เครื่องช่วยหายใจ]')" required>
                    ไม่พร้อม
                    <select id="status[เครื่องช่วยหายใจ]-dropdown" name="reason[เครื่องช่วยหายใจ]" disabled onchange="updateDropdown('status[เครื่องช่วยหายใจ]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="สายไฟขาด">สายไฟขาด</option>
                        <option value="หน้าจอเสีย">หน้าจอเสีย</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เครื่องช่วยหายใจ]-other" name="reason[เครื่องช่วยหายใจ]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เครื่องช่วยหายใจ]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="O2">ถังออกซิเจน</label>
                    <input type="radio" name="status[ถังออกซิเจน]" value="พร้อม" id="O2-yes" onclick="toggleDropdown('status[ถังออกซิเจน]')" required> พร้อม
                    <input type="radio" name="status[ถังออกซิเจน]" value="ไม่พร้อม" id="O2-no" onclick="toggleDropdown('status[ถังออกซิเจน]')" required>
                    ไม่พร้อม
                    <select id="status[ถังออกซิเจน]-dropdown" name="reason[ถังออกซิเจน]" disabled onchange="updateDropdown('status[ถังออกซิเจน]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ถังรั่ว">ถังรั่ว</option>
                        <option value="หมดอายุ">หมดอายุ</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[ถังออกซิเจน]-other" name="reason[ถังออกซิเจน]" placeholder="ระบุ" disabled oninput="updateOtherField('status[ถังออกซิเจน]')"
                        required>
                </div>

                <div class="form-group">
                    <label for="pressure">เครื่องวัดความดัน</label>
                    <input type="radio" name="status[เครื่องวัดความดัน]" value="พร้อม" id="pressure-yes" onclick="toggleDropdown('status[เครื่องวัดความดัน]')" required> พร้อม
                    <input type="radio" name="status[เครื่องวัดความดัน]" value="ไม่พร้อม" id="pressure-no" onclick="toggleDropdown('status[เครื่องวัดความดัน]')"
                        required> ไม่พร้อม
                    <select id="status[เครื่องวัดความดัน]-dropdown" name="reason[เครื่องวัดความดัน]" disabled onchange="updateDropdown('status[เครื่องวัดความดัน]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="สายไฟขาด">สายไฟขาด</option>
                        <option value="หน้าจอเสีย">หน้าจอเสีย</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เครื่องวัดความดัน]-other" name="reason[เครื่องวัดความดัน]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เครื่องวัดความดัน]')" required>
                </div>

                <div class="form-group">
                    <label for="heart_rate">เครื่องวัดชีพจร</label>
                    <input type="radio" name="status[เครื่องวัดชีพจร]" value="พร้อม" id="heart_rate-yes" onclick="toggleDropdown('status[เครื่องวัดชีพจร]')" required> พร้อม
                    <input type="radio" name="status[เครื่องวัดชีพจร]" value="ไม่พร้อม" id="heart_rate-no" onclick="toggleDropdown('status[เครื่องวัดชีพจร]')" required> ไม่พร้อม
                    <select id="status[เครื่องวัดชีพจร]-dropdown" name="reason[เครื่องวัดชีพจร]" disabled onchange="updateDropdown('status[เครื่องวัดชีพจร]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="สายไฟขาด">สายไฟขาด</option>
                        <option value="หน้าจอเสีย">หน้าจอเสีย</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เครื่องวัดชีพจร]-other" name="reason[เครื่องวัดชีพจร]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เครื่องวัดชีพจร]')" required>
                </div>

                <div class="form-group">
                    <label for="bed">เตียงพยาบาล</label>
                    <input type="radio" name="status[เตียงพยาบาล]" value="พร้อม" id="bed-yes" onclick="toggleDropdown('status[เตียงพยาบาล]')" required>พร้อม
                    <input type="radio" name="status[เตียงพยาบาล]" value="ไม่พร้อม" id="bed-no" onclick="toggleDropdown('status[เตียงพยาบาล]')" required>ไม่พร้อม
                    <select id="status[เตียงพยาบาล]-dropdown" name="reason[เตียงพยาบาล]" disabled onchange="updateDropdown('status[เตียงพยาบาล]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ชำรุด">ชำรุด</option>
                        <option value="อะไหล่หลุด">อะไหล่หลุด</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เตียงพยาบาล]-other" name="reason[เตียงพยาบาล]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เตียงพยาบาล]')" required>
                </div>

                <div class="form-group">
                    <label for="stretcher">เปลสนาม</label>
                    <input type="radio" name="status[เปลสนาม]" value="พร้อม" id="stretcher-yes" onclick="toggleDropdown('status[เปลสนาม]')" required> พร้อม
                    <input type="radio" name="status[เปลสนาม]" value="ไม่พร้อม" id="stretcher-no" onclick="toggleDropdown('status[เปลสนาม]')" required> ไม่พร้อม
                    <select id="status[เปลสนาม]-dropdown" name="reason[เปลสนาม]" disabled onchange="updateDropdown('status[เปลสนาม]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ชำรุด">ชำรุด</option>
                        <option value="อะไหล่หลุด">อะไหล่หลุด</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[เปลสนาม]-other" name="reason[เปลสนาม]" placeholder="ระบุ" disabled oninput="updateOtherField('status[เปลสนาม]')" required>
                </div>

                <div class="form-group">
                    <label for="firstaid">อุปกรณ์ปฐมพยาบาล</label>
                    <input type="radio" name="status[อุปกรณ์ปฐมพยาบาล]" value="พร้อม" id="firstaid-yes" onclick="toggleDropdown('status[อุปกรณ์ปฐมพยาบาล]')" required> พร้อม
                    <input type="radio" name="status[อุปกรณ์ปฐมพยาบาล]" value="ไม่พร้อม" id="firstaid-no" onclick="toggleDropdown('status[อุปกรณ์ปฐมพยาบาล]')"
                        required> ไม่พร้อม
                    <select id="status[อุปกรณ์ปฐมพยาบาล]-dropdown" name="reason[อุปกรณ์ปฐมพยาบาล]" disabled onchange="updateDropdown('status[อุปกรณ์ปฐมพยาบาล]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ชำรุด">ชำรุด</option>
                        <option value="หมดอายุ">หมดอายุ</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[อุปกรณ์ปฐมพยาบาล]-other" name="reason[อุปกรณ์ปฐมพยาบาล]" placeholder="ระบุ" disabled
                        oninput="updateOtherField('status[อุปกรณ์ปฐมพยาบาล]')" required>
                </div>

                <div class="form-group">
                    <label for="Splint">อุปกรณ์การดาม</label>
                    <input type="radio" name="status[อุปกรณ์การดาม]" value="พร้อม" id="Splint-yes" onclick="toggleDropdown('status[อุปกรณ์การดาม]')" required>
                    พร้อม
                    <input type="radio" name="status[อุปกรณ์การดาม]" value="ไม่พร้อม" id="Splint-no" onclick="toggleDropdown('status[อุปกรณ์การดาม]')" required>
                    ไม่พร้อม
                    <select id="status[อุปกรณ์การดาม]-dropdown" name="reason[อุปกรณ์การดาม]" disabled onchange="updateDropdown('status[อุปกรณ์การดาม]')" required>
                        <option value="" disabled selected>สาเหตุ</option>
                        <option value="ชำรุด">ชำรุด</option>
                        <option value="ไม้ดามหัก">ไม้ดามหัก</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                    <input type="text" id="status[อุปกรณ์การดาม]-other" name="reason[อุปกรณ์การดาม]" placeholder="ระบุ" disabled oninput="updateOtherField('status[อุปกรณ์การดาม]')"
                        required>
                </div>
            </div>
        </div>
        <!-- ผู้รายงานและปุ่มส่ง -->
        <div class="reporter">
            <button type="submit" class="save">บันทึก</button>
            <button type="button" style="background-color: #E4AE9F;" onclick="resetForm()">ยกเลิก</button>
        </div>
    </form>
</body>

</html>