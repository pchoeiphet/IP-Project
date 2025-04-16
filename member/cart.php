<?php
session_start();
include 'username.php';

// ถ้าไม่ได้ล็อกอิน ให้ redirect กลับไปหน้า login
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// เรียก member_id จาก session มาใช้
$member_id = $_SESSION['user_id'];
//------------------------------------------
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>ตะกร้าสินค้า</title>
</head>

<body>
    <div class="top-navbar">
        <nav class="nav-links">
            <div><a href="order_emergency.php">ชำระเงินเคสฉุกเฉิน</a></div>
            <div><a href="contact.html">ติดต่อเรา</a></div>
            <div class="dropdown">
                <img src="image/user.png" alt="Logo" class="nav-logo">
                <div class="dropdown-menu">
                    <a href="profile.html">โปรไฟล์</a>
                    <a href="history.php">ประวัติคำสั่งซื้อ</a>
                    <a href="history_ambulance_booking.php">ประวัติการจองรถ</a>
                    <a href="claim.php">เคลมสินค้า</a>
                    <a href="../logout.php">ออกจากระบบ</a>
                </div>
            </div>
            <a href="index.php">
                <img src="image/united-states-of-america.png" alt="Logo" class="nav-logo">
            </a>
        </nav>
    </div>

    <div class="main-navbar">
        <nav class="nav-links">
            <div><a href="index.php">หน้าแรก</a></div>
            <div><a href="reservation_car.php">จองคิวรถ</a></div>
            <a href="index.php">
                <img src="image/Logo.png" alt="Logo" class="nav-logo1">
            </a>
            <div><a href="shopping.php">ซื้อ/เช่าอุปกรณ์ทางการแพทย์</a></div>
        </nav>

        <div class="cart-icon">
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
            </a>
        </div>
    </div>

    <div class="div container">
        <form id="form1" method="POST" action="">
            <div class="div row">
                <div class="col-md-10">
                    <br>
                    <div class="alert alert-primary h4" role="alert">
                        การสั่งซื้ออุปกรณ์ทางการแพทย์
                    </div>
                    <table class="table table-hover">
                        <tr>
                            <th>ลำดับสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>ราคารวม</th>
                            <th>ลบ</th>
                        </tr>
                        <?php
                        $Total = 0;
                        $sumPrice = 0;
                        $m = 1;
                        // ตรวจสอบว่า $_SESSION["strProductID"] ถูกตั้งค่าหรือยัง
                        if (isset($_SESSION["strProductID"])) {
                            for ($i = 0; $i <= (int)$_SESSION["intLine"]; $i++) {
                                // ตรวจสอบว่า $_SESSION["strProductID"][$i] มีค่าอยู่จริงหรือไม่
                                if (isset($_SESSION["strProductID"][$i]) && $_SESSION["strProductID"][$i] != "") {
                                    $sql1 = "select * from equipment WHERE equipment_id = '" . $_SESSION["strProductID"][$i] . "'";
                                    $result1 = mysqli_query($conn, $sql1);
                                    $row_equip = mysqli_fetch_array($result1);

                                    $_SESSION["equipment_price_per_unit"] = $row_equip['equipment_price_per_unit'];
                                    $Total = $_SESSION["strQty"][$i];
                                    $sum = $Total * $row_equip['equipment_price_per_unit'];
                                    $sumPrice += $sum; // คำนวณราคารวม

                                    // แสดงตารางสินค้า
                        ?>
                                    <tr>
                                        <td><?= $m ?></td>
                                        <td>
                                            <img src="image/<?= $row_equip['equipment_image'] ?>" width="100" height="100" class="border">
                                            <?= $row_equip['equipment_name'] ?>
                                        </td>
                                        <td><?= number_format($row_equip['equipment_price_per_unit'], 2) ?></td>
                                        <td>
                                            <?php if ($_SESSION['strQty'][$i] > 1) { ?>
                                                <!-- ปุ่มลบจำนวน -->
                                                <button type="button" class="btn btn-outline-primary update-cart" data-id="<?= $row_equip['equipment_id'] ?>" data-action="decrease">-</button>
                                            <?php } ?>
                                            <?= $_SESSION['strQty'][$i] ?>
                                            <!-- ปุ่มเพิ่มจำนวน -->
                                            <?php if ($_SESSION['strQty'][$i] < $row_equip['equipment_quantity']) { ?>
                                                <button type="button" class="btn btn-outline-primary update-cart" data-id="<?= $row_equip['equipment_id'] ?>" data-action="increase">+</button>
                                            <?php } else { ?>
                                                <button type="button" class="btn btn-outline-secondary" disabled>+</button>
                                            <?php } ?>

                                        <td><?= number_format($sum, 2) ?></td>
                                        <td><a href="equipment_delete.php?Line=<?= $i ?>"><img src="image/delete.png" width="30"></a></td>
                                    </tr>
                        <?php
                                    $m = $m + 1;
                                }
                            }
                        }
                        ?>
                        <td class="text-end" colspan="4">ราคารวม</td>
                        <td><?= number_format($sumPrice, 2) ?></td>
                        <td>บาท</td>
                        </tr>
                        <?php $vat = ($sumPrice * 7) / 100  ?>
                        <tr>
                            <td class="text-end" colspan="4">Vat 7%</td>
                            <td><?= number_format($vat, 2) ?></td>
                            <td>บาท</td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="4">ค่าจัดส่งสินค้า</td>
                            <td>120</td>
                            <td>บาท</td>
                        </tr>

                        <?php $sumPrice = $sumPrice + $vat ?>
                        <tr>
                            <td class="text-end" colspan="4">ยอดชำระทั้งหมด</td>
                            <td><?= number_format($sumPrice  + 120, 2) ?></td>
                            <td>บาท</td>
                        </tr>
                    </table>
                </div>
            </div>
    </div>
    </form>
    <div class="order-buttons">
        <a href="shopping.php" class="add-to-cart">เลือกสินค้า</a>
        <!-- <a href="QRpayment_order.php" class="confirm-order">ยืนยันการสั่งซื้อ</a> -->

        <!-- ปุ่มอยู่ขวาล่าง -->
        <div class="order-buttons-wrapper">
            <form method="post" action="insert_order_cart.php" style="display:inline;">
                <input type="hidden" name="price_total" value="<?= $sumPrice + 120 ?>">
                <button type="submit" class="confirm-order">ยืนยันการสั่งซื้อ</button>
            </form>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.update-cart').click(function() {
                    const id = $(this).data('id');
                    const action = $(this).data('action');

                    $.ajax({
                        url: 'order_cart.php',
                        type: 'POST',
                        data: {
                            id: id,
                            action: action
                        },
                        success: function(response) {
                            location.reload(); // หรือจะอัปเดตแค่บางส่วนของตารางก็ได้
                        }
                    });
                });
            });
        </script>
</body>

</html>