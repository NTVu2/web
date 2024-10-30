<?php
header("");
include '../db_connect.php'; // Đảm bảo file này chứa kết nối cơ sở dữ liệu của bạn

$month = $_GET['month'];
$year = $_GET['year'];

// Chuẩn bị câu truy vấn SQL để lấy tổng doanh thu và số đơn hàng trong tháng
$sql = "SELECT COUNT(Madonhang) AS total_orders, SUM(Tongtien) AS total_revenue 
        FROM donhang 
        WHERE MONTH(NgayDH) = ? AND YEAR(NgayDH) = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $month, $year);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'total_orders' => $result['total_orders'],
    'total_revenue' => number_format($result['total_revenue'], 0, ',', '.')
]);

$stmt->close();
$con->close();
?>
