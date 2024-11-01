<?php
session_start();

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginAdmin.php");
    exit;
}

include '../db_connect.php'; // Kết nối database

// Lấy tháng và năm được chọn từ form, mặc định là tháng và năm hiện tại
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Điều kiện thời gian cho các truy vấn
$timeConditionMonth = "AND MONTH(hd.NgayBH) = $selectedMonth AND YEAR(hd.NgayBH) = $selectedYear";
$timeConditionQuarter = "AND QUARTER(hd.NgayBH) = QUARTER(STR_TO_DATE('$selectedMonth-1-$selectedYear', '%m-%d-%Y')) AND YEAR(hd.NgayBH) = $selectedYear";
$timeConditionYear = "AND YEAR(hd.NgayBH) = $selectedYear";

// Truy vấn Sản phẩm bán chạy trong tháng được chọn
$bestSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionMonth
                     GROUP BY h.Mahang
                     ORDER BY TotalSold DESC LIMIT 5";
$bestSellingResult = $con->query($bestSellingQuery);

// Truy vấn Sản phẩm bán chậm trong tháng được chọn
$slowSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionMonth
                     GROUP BY h.Mahang
                     ORDER BY TotalSold ASC LIMIT 5";
$slowSellingResult = $con->query($slowSellingQuery);

// Truy vấn Khách hàng mua nhiều trong tháng được chọn
$frequentBuyersQuery = "SELECT k.Tenkhach, COUNT(hd.SohieuHD) AS TotalOrders
                        FROM khach k
                        JOIN hoadon hd ON k.id = hd.id
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        $timeConditionMonth
                        GROUP BY k.id
                        ORDER BY TotalOrders DESC LIMIT 5";
$frequentBuyersResult = $con->query($frequentBuyersQuery);

// Truy vấn Doanh số và Doanh thu trong tháng
$monthlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                      FROM hoadon hd
                      WHERE hd.Trangthai = 'Giao hàng thành công'
                      $timeConditionMonth";
$monthlySalesResult = $con->query($monthlySalesQuery);
$monthlySalesData = $monthlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong quý
$quarterlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                        FROM hoadon hd
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        $timeConditionQuarter";
$quarterlySalesResult = $con->query($quarterlySalesQuery);
$quarterlySalesData = $quarterlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong năm
$yearlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                     FROM hoadon hd
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionYear";
$yearlySalesResult = $con->query($yearlySalesQuery);
$yearlySalesData = $yearlySalesResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ ADMIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* Tổng quan */
    body,
    html {
        margin: 0;
        padding: 0;
        font-family: 'Roboto', sans-serif;
        background-color: #f4f4f4;
    }

    /* Header */
    .admin-header {
        background-color: #3a3b3c;
        color: #ffffff;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-title {
        font-size: 24px;
    }

    .user-actions {
        list-style: none;
        display: flex;
        gap: 10px;
    }

    .user-actions li {
        display: inline;
    }

    .user-actions a {
        color: #ffffff;
        text-decoration: none;
        font-weight: bold;
    }

    .user-actions a:hover {
        text-decoration: underline;
    }

    /* Navigation */
    nav {
        background-color: #404040;
        padding: 10px;
    }

    .tabs {
        display: flex;
        gap: 10px;
    }

    .tab-button {
        background-color: #5a5a5a;
        color: #ffffff;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-weight: bold;
        display: inline-block;
    }

    .tab-button:hover {
        background-color: #6c757d;
    }

    .hamburger {
        font-size: 24px;
        cursor: pointer;
        color: #ffffff;
    }

    /* Form chọn thời gian */
    .nav-form-container {
        display: flex;
        gap: 15px;
        padding: 15px;
        background-color: #f4f4f4;
        border-bottom: 1px solid #ddd;
    }

    .form-container {
        display: flex;
        gap: 10px;
    }

    .btn-submit {
        background-color: #4CAF50;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-submit:hover {
        background-color: #45a049;
    }

    /* Bảng danh sách đơn hàng */
    .container {
        padding: 20px;
    }

    h2 {
        color: #333;
        text-align: center;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f2f2f2;
        color: #333;
    }

    .table tbody tr:hover {
        background-color: #f9f9f9;
    }

    /* Nút xem chi tiết */
    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    /* Container Biểu đồ */
    #chartDiv {
        max-width: 100%;
        margin-top: 20px;
    }

    #revenueChart {
        max-width: 100%;
    }

    /* Dropdown Button */
    .dropbtn {
        background-color: #04AA6D;
        color: white;
        padding: 16px;
        font-size: 16px;
        border: none;
    }

    /* The container <div> - needed to position the dropdown content */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    /* Dropdown Content (Hidden by Default) */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    /* Links inside the dropdown */
    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    /* Change color of dropdown links on hover */
    .dropdown-content a:hover {
        background-color: #ddd;
    }

    /* Show the dropdown menu on hover */
    .dropdown:hover .dropdown-content {
        display: block;
    }

    /* Change the background color of the dropdown button when the dropdown content is shown */
    .dropdown:hover .dropbtn {
        background-color: #3e8e41;
    }

    /* Responsive */
    @media screen and (max-width: 768px) {
        .nav-form-container {
            flex-direction: column;
        }

        .form-container {
            flex-direction: column;
        }

        .tabs {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>
</head>

<body>

    <header class="admin-header">
        <div class="header-container">
            <h1 class="admin-title">Bảng Điều Khiển Admin</h1>
            <ul class="user-actions">
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <li><a href="logout.php"><i class="fa fa-user"></i> <?php echo $_SESSION['admin_username']; ?></a></li>
                <?php else: ?>
                    <li><a href="loginAdmin.php" class="btn-primary">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <nav>
        <div class="tabs">
            <a href="trangchu.php" class="tab-button">Trang Chủ</a>
            <a href="thongke.php" class="tab-button">Thống Kê</a>
            <div class="dropdown">
                <button class="tab-button">Quản lý</button>
                <div class="dropdown-content">
                    <a href="qlnhanvien.php" class="tab-button">Quản Lý nhân viên</a>
                    <a href="qltaikhaon.php" class="tab-button">Quản Lý tài khoản</a>
                    <a href="quanlydonhang.php" class="tab-button">Quản Lý</a>
                </div>
            </div>
            <span class="hamburger" onclick="toggleNav()">&#9776;</span>
        </div>
    </nav>

    <div class="container">
        <form method="get" class="nav-form-container">
            <div class="form-container">
                <label for="month">Chọn Tháng:</label>
                <select name="month" id="month">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $selectedMonth) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label for="year">Chọn Năm:</label>
                <select name="year" id="year">
                    <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <input type="submit" value="Xem Thống Kê" class="btn-submit">
            </div>
        </form>

        <h2>Sản Phẩm Bán Chạy Nhất</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng Bán</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bestSellingResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Tenhang']); ?></td>
                        <td><?php echo htmlspecialchars($row['TotalSold']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Sản Phẩm Bán Chậm Nhất</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng Bán</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $slowSellingResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Tenhang']); ?></td>
                        <td><?php echo htmlspecialchars($row['TotalSold']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Khách Hàng Mua Nhiều Nhất</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Tên Khách Hàng</th>
                    <th>Số Đơn Hàng</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $frequentBuyersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Tenkhach']); ?></td>
                        <td><?php echo htmlspecialchars($row['TotalOrders']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Doanh Số và Doanh Thu</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Giá Trị</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Doanh Thu Tháng</td>
                    <td><?php echo htmlspecialchars(number_format($monthlySalesData['TotalRevenue'], 0, ',', '.')); ?> VNĐ</td>
                </tr>
                <tr>
                    <td>Số Đơn Hàng Tháng</td>
                    <td><?php echo htmlspecialchars($monthlySalesData['TotalSales']); ?></td>
                </tr>
                <tr>
                    <td>Doanh Thu Quý</td>
                    <td><?php echo htmlspecialchars(number_format($quarterlySalesData['TotalRevenue'], 0, ',', '.')); ?> VNĐ</td>
                </tr>
                <tr>
                    <td>Số Đơn Hàng Quý</td>
                    <td><?php echo htmlspecialchars($quarterlySalesData['TotalSales']); ?></td>
                </tr>
                <tr>
                    <td>Doanh Thu Năm</td>
                    <td><?php echo htmlspecialchars(number_format($yearlySalesData['TotalRevenue'], 0, ',', '.')); ?> VNĐ</td>
                </tr>
                <tr>
                    <td>Số Đơn Hàng Năm</td>
                    <td><?php echo htmlspecialchars($yearlySalesData['TotalSales']); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="col-md-8 offset-md-2">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Doanh Thu Tháng', 'Doanh Thu Quý', 'Doanh Thu Năm'],
                datasets: [{
                    label: 'Doanh Thu',
                    data: [
                        <?php echo htmlspecialchars($monthlySalesData['TotalRevenue']); ?>,
                        <?php echo htmlspecialchars($quarterlySalesData['TotalRevenue']); ?>,
                        <?php echo htmlspecialchars($yearlySalesData['TotalRevenue']); ?>
                    ],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>