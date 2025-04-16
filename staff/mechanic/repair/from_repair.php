<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_from.css">
    <script src="script_form_repair.js" defer></script>
    <title>รายละเอียดการแจ้งซ่อม</title>
</head>

<body>
    <nav>
        <ul class="menu">
            <li><a href="..\car_report\car_report.php">รายงานสภาพรถพยาบาล</a></li>
            <li><a href="repair.php">การซ่อมอุปกรณ์และรถพยาบาล</a></li>
        </ul>
    </nav>
    <div class="header">
        <h1 class="title">การแจ้งซ่อม</h1>
    </div>

    <form action="con_from.php" class="formReportRepair" method="post">
        <div class="container">
            <div class="form-container">
                <div class="form-title">รายละเอียดการแจ้งซ่อม</div>

                <div class="section">
                    <div class="form-row1">
                        <div class="form-group1">
                            <label for="date">วันที่</label>
                            <input type="date" id="currentDate" name="currentDate" disabled>
                        </div>
                        <div class="form-group1">
                            <label for="level">ระดับรถ</label>
                            <select id="level" required>
                                <option value="" disabled selected>ระบุระดับรถ</option>
                                <option value="ระดับ 1">ระดับ 1</option>
                                <option value="ระดับ 2">ระดับ 2</option>
                                <option value="ระดับ 3">ระดับ 3</option>
                            </select>
                        </div>
                        <div class="form-group1">
                            <label for="number">ทะเบียนรถ</label>
                            <select id="number" name="car_number" required>
                                <option value="" disabled selected>ระบุทะเบียนรถ</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row2">
                        <div class="form-group">
                            <label for="category">ประเภทการซ่อม</label>
                            <select id="category" name="category" required>
                                <option value="" disabled selected>ระบุประเภท</option>
                                <option value="รถพยาบาล">รถพยาบาล</option>
                                <option value="อุปกรณ์ทางการแพทย์">อุปกรณ์ทางการแพทย์</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="device">อะไหล่/อุปกรณ์</label>
                            <select id="device" name="device" required>
                                <option value="" disabled selected>ระบุอุปกรณ์</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason">สาเหตุ</label>
                            <select id="reason" name="reason" required>
                                <option value="" disabled selected>สาเหตุ</option>
                            </select>
                        </div>
                        <div class="form-group" id="other-cause-row"  style="display: none;">
                            <label for="note">ระบุสาเหตุ</label>
                            <input type="text" name="reason" id="note">
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="save-button">บันทึก</button>
                    <button type="button" class="cancel-button" id="cancel-button">ยกเลิก</button>
                </div>
            </div>
        </div>
    </form>
</body>

</html>