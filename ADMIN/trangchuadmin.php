<?php
session_start();
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginADMIN.php");
    exit;
}



include '../db_connect.php';

if (!$con) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}


// Truy vấn Sản phẩm bán chạy
$bestSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     GROUP BY h.Mahang
                     ORDER BY TotalSold DESC LIMIT 5";
$bestSellingResult = $con->query($bestSellingQuery);

// Truy vấn Sản phẩm bán chậm
$slowSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     GROUP BY h.Mahang
                     ORDER BY TotalSold ASC LIMIT 5";
$slowSellingResult = $con->query($slowSellingQuery);

// Truy vấn Khách hàng mua nhiều
$frequentBuyersQuery = "SELECT k.Tenkhach, COUNT(hd.SohieuHD) AS TotalOrders
                        FROM khach k
                        JOIN hoadon hd ON k.id = hd.id
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        GROUP BY k.id
                        ORDER BY TotalOrders DESC LIMIT 5";
$frequentBuyersResult = $con->query($frequentBuyersQuery);

// Truy vấn Doanh số và Doanh thu trong tháng
$monthlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                      FROM hoadon hd
                      WHERE hd.Trangthai = 'Giao hàng thành công'
                      AND MONTH(hd.NgayBH) = MONTH(CURRENT_DATE())
                      AND YEAR(hd.NgayBH) = YEAR(CURRENT_DATE())";
$monthlySalesResult = $con->query($monthlySalesQuery);
$monthlySalesData = $monthlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong quý
$quarterlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                        FROM hoadon hd
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        AND QUARTER(hd.NgayBH) = QUARTER(CURRENT_DATE())
                        AND YEAR(hd.NgayBH) = YEAR(CURRENT_DATE())";
$quarterlySalesResult = $con->query($quarterlySalesQuery);
$quarterlySalesData = $quarterlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong năm
$yearlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                     FROM hoadon hd
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     AND YEAR(hd.NgayBH) = YEAR(CURRENT_DATE())";
$yearlySalesResult = $con->query($yearlySalesQuery);
$yearlySalesData = $yearlySalesResult->fetch_assoc();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Trang chủ ADMIN</title>
</head>
<body class="font-sans bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-blue-500">Bảng Điều Khiển Admin</h1>
            <ul class="flex items-center">
                <?php if ($loggedIn): ?>
                    <li><a href="logout.php" class="text-gray-600 hover:text-blue-500 transition"><i class="fa fa-user mr-2"></i><?php echo $_SESSION['admin_username']; ?></a></li>
                <?php else: ?>
                    <li><a href="loginADMIN.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Đăng Nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <nav class="bg-gray-800 text-white">
        <div class="container mx-auto px-4 py-3">
            <div class="flex space-x-4">
                <a href="trangchuadmin.php" class="px-3 py-2 rounded bg-gray-700 hover:bg-gray-600 transition"><i class="fa fa-home mr-2"></i>Trang chủ</a>
                <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
                    <a href="Nhập_SP.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-product-hunt mr-2"></i>Sản phẩm</a>
                <?php endif; ?>
                <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
                    <a href="Nhập_DM.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-list mr-2"></i>Danh mục</a>
                <?php endif; ?>
                <?php if (in_array('banner', $_SESSION['quyen'])): ?>
                    <a href="Nhập_Banner.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-image mr-2"></i>Banner</a>
                <?php endif; ?>
                <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
                    <a href="qltaikhaon.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-user mr-2"></i>Tài khoản</a>
                <?php endif; ?>
                <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
                    <a href="quanlydonhang.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-credit-card mr-2"></i>Đơn hàng</a>
                <?php endif; ?>
                <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
                    <a href="xemhoadon.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-clipboard-list mr-2"></i>Hóa đơn</a>
                <?php endif; ?>
                <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
                    <a href="qlnhanvien.php" class="px-3 py-2 rounded hover:bg-gray-700 transition"><i class="fa fa-user-tie mr-2"></i>Nhân viên</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Sản phẩm bán chạy</h2>
                <ul class="space-y-2">
                    <?php while ($row = $bestSellingResult->fetch_assoc()) { ?>
                        <li class="bg-gray-100 p-3 rounded"><?php echo $row['Tenhang'] . " - " . $row['TotalSold'] . " sản phẩm"; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Sản phẩm bán chậm</h2>
                <ul class="space-y-2">
                    <?php while ($row = $slowSellingResult->fetch_assoc()) { ?>
                        <li class="bg-gray-100 p-3 rounded"><?php echo $row['Tenhang'] . " - " . $row['TotalSold'] . " sản phẩm"; ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Khách hàng mua nhiều</h2>
                <ul class="space-y-2">
                    <?php while ($row = $frequentBuyersResult->fetch_assoc()) { ?>
                        <li class="bg-gray-100 p-3 rounded"><?php echo $row['Tenkhach'] . " - " . $row['TotalOrders'] . " đơn hàng"; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Doanh số & Doanh thu trong tháng</h2>
                <p class="mb-2">Tổng số đơn hàng: <span class="font-semibold"><?php echo $monthlySalesData['TotalSales']; ?></span></p>
                <p>Tổng doanh thu: <span class="font-semibold"><?php echo number_format($monthlySalesData['TotalRevenue']); ?> VND</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Doanh số & Doanh thu trong quý</h2>
                <p class="mb-2">Tổng số đơn hàng: <span class="font-semibold"><?php echo $quarterlySalesData['TotalSales']; ?></span></p>
                <p>Tổng doanh thu: <span class="font-semibold"><?php echo number_format($quarterlySalesData['TotalRevenue']); ?> VND</span></p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Doanh số & Doanh thu trong năm</h2>
                <p class="mb-2">Tổng số đơn hàng: <span class="font-semibold"><?php echo $yearlySalesData['TotalSales']; ?></span></p>
                <p>Tổng doanh thu: <span class="font-semibold"><?php echo number_format($yearlySalesData['TotalRevenue']); ?> VND</span></p>
            </div>
        </div>
    </main>
</body>
</html>