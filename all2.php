<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/trangview.css">
    <link rel="stylesheet" href="css/banner.css">
    <link rel="stylesheet" href="css/danhmuc.css">
    <link rel="stylesheet" href="css/sx.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script src="https://unpkg.com/unlazy@0.11.3/dist/unlazy.with-hashing.iife.js" defer init></script>

    <title>Vũ Tuấn Shop</title>
    <link rel="shortcut icon" href="img/sản_phẩm/logo.png" type="image/x-icon">
    <style>

    </style>
</head>

<body>
    <div class="bg-background text-foreground min-h-screen">
        <nav class="bg-primary text-primary-foreground p-4">
            <div class="container mx-auto flex justify-between items-center">
                <a class="text-xl font-bold flex items-center" href="index.php">
                    <img src="img/sản_phẩm/logo.png" alt="Logo" class="h-12 w-auto mr-2 rounded-full" />
                    <span>Vũ Tuấn Shop</span>
                </a>
                <!-- thanh tìm kiếm  -->
                <div class="relative">
                    <form action="allsp.php" method="GET" class="flex">
                        <input type="text" name="search" placeholder="100% Hàng Thật" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" class="border rounded-2xl px-4 py-2 w-96 focus:border-primary-foreground focus:ring-1 focus:ring-primary-foreground text-black" />
                        <button type="submit" class="absolute right-0 top-0 mt-2 mr-2">🔍</button>
                    </form>
                </div>
                <ul class="flex space-x-4" style="align-items: center;">
                    <?php if ($loggedIn): ?>
                        <li><a href="giohang1.php" class="hover:underline"><img src="img/giohang.png" alt="giohang" class="h-11 w-11 mr-2 "></a></li>
                        <a href="theodoi.php">
                            <li class="text-sm font-bold"><img src="img/icon.png" alt="icon" style="width: 30px; height: 30px;"> <span class="ten"><?= htmlspecialchars($username) ?></span></li>
                        </a>
                        <li><a href="logout.php" class="dangxuat text-lg">Đăng Xuất</a></li>
                    <?php else: ?>
                        <li><a href="giohang1.php" class="hover:underline"><img src="img/giohang.png" alt="giohang" class="h-11 w-11 mr-2 "></a></li>
                        <li><a href="Login_singup/login.php" class="dangnhap">Đăng Nhập</a></li>
                        <li><a href="Login_singup/singup.php" class="dangky">Đăng Ký</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <div id="sort-bar">
            <!-- Các liên kết sắp xếp -->
            <div class="sort-links">
                <a href="?<?php echo (isset($_GET['category']) ? "category=" . $_GET['category'] . "&" : "");
                            echo (isset($_GET['search']) ? "search=" . $_GET['search'] . "&" : ""); ?>sort=ban_chay" class="<?php if (isset($_GET['sort']) && $_GET['sort'] == 'ban_chay') echo 'active'; ?>">Sản Phẩm Bán Chạy</a>
                <a href="?<?php echo (isset($_GET['category']) ? "category=" . $_GET['category'] . "&" : "");
                            echo (isset($_GET['search']) ? "search=" . $_GET['search'] . "&" : ""); ?>sort=gia_thap_den_cao" class="<?php if (isset($_GET['sort']) && $_GET['sort'] == 'gia_thap_den_cao') echo 'active'; ?>">Giá Thấp đến Cao</a>
                <a href="?<?php echo (isset($_GET['category']) ? "category=" . $_GET['category'] . "&" : "");
                            echo (isset($_GET['search']) ? "search=" . $_GET['search'] . "&" : ""); ?>sort=gia_cao_den_thap" class="<?php if (isset($_GET['sort']) && $_GET['sort'] == 'gia_cao_den_thap') echo 'active'; ?>">Giá Cao đến Thấp</a>
                <div id="category-dropdown">
                    <span>Khác</span>
                    <div id="category-list" style="display: none;">
                        <?php
                        include 'db_connect.php';
                        $sql_loaihang = "SELECT * FROM loaihang";
                        $result_loaihang = mysqli_query($con, $sql_loaihang);

                        if (mysqli_num_rows($result_loaihang) > 0) {
                            while ($row_loaihang = mysqli_fetch_assoc($result_loaihang)) {
                                $tenloaihang = $row_loaihang['Tenloaihang'];
                                $maloaihang = $row_loaihang['Maloaihang'];
                                // Thêm class 'active' nếu danh mục đang được chọn
                                $activeClass = (isset($_GET['category']) && $_GET['category'] == $maloaihang) ? 'active' : '';
                                echo "<a href='?category=$maloaihang" . (isset($_GET['search']) ? "&search=" . $_GET['search'] : "") . "' class='$activeClass'>$tenloaihang</a>";
                            }
                        }

                        mysqli_close($con);
                        ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Main Product Grid -->
        <div id="product-section" class="container mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 py-8">
            <?php

            // Include database connection
            include 'db_connect.php';

            // Check for connection error
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Lấy giá trị category từ URL nếu có
            $category = isset($_GET['category']) ? $_GET['category'] : '';

            // Lấy giá trị tìm kiếm từ URL nếu có
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            $limit = 20;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;

            // Truy vấn Sản phẩm bán chạy (đã được giao thành công)
            $bestSellingQuery = "SELECT h.Mahang, SUM(ct.Soluong) AS TotalSold
                                         FROM hang h
                                         JOIN chitiethd ct ON h.Mahang = ct.Mahang
                                         JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                                         WHERE hd.Trangthai = 'Giao hàng thành công'
                                         GROUP BY h.Mahang
                                         ORDER BY TotalSold DESC LIMIT 5";
            $bestSellingResult = $con->query($bestSellingQuery);


            // SQL query để lấy các sản phẩm
            $sql = "SELECT hang.Mahang, hang.Tenhang, hang.Mota, loaihang.Tenloaihang, hang.anh, hang.Dongia, hang.Soluongton 
                            FROM hang 
                            INNER JOIN loaihang ON hang.Maloaihang = loaihang.Maloaihang";

            // Nếu có danh mục được chọn, thêm điều kiện lọc
            if (!empty($category)) {
                $sql .= " WHERE hang.Maloaihang = '$category'";
            }

            // Nếu có từ khóa tìm kiếm, thêm điều kiện lọc
            if (!empty($search)) {
                if (!empty($category)) {
                    $sql .= " AND";
                } else {
                    $sql .= " WHERE";
                }
                $sql .= " hang.Tenhang LIKE '%$search%'";
            }


            // Sắp xếp sản phẩm dựa trên lựa chọn
            $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
            switch ($sort) {
                case 'gia_thap_den_cao':
                    $sql .= " ORDER BY Dongia ASC";
                    break;
                case 'gia_cao_den_thap':
                    $sql .= " ORDER BY Dongia DESC";
                    break;
                case 'ban_chay':
                    // Sử dụng kết quả truy vấn sản phẩm bán chạy để lọc sản phẩm chính
                    $bestSellingProducts = [];
                    while ($row = $bestSellingResult->fetch_assoc()) {
                        $bestSellingProducts[] = $row['Mahang']; // Sử dụng Mahang để đảm bảo tính duy nhất
                    }

                    if (!empty($bestSellingProducts)) {
                        $inClause = "'" . implode("','", $bestSellingProducts) . "'";
                        $sql .= " AND Mahang IN ($inClause)"; // Lọc sản phẩm dựa trên danh sách bán chạy (sử dụng Mahang)
                        $sql .= " ORDER BY FIELD(Mahang, $inClause)"; // Sắp xếp theo thứ tự trong danh sách bán chạy (sử dụng Mahang)
                    } else {
                        $sql .= " ORDER BY Soluongton DESC"; // Nếu không có sản phẩm bán chạy, sắp xếp theo số lượng tồn
                    }
                    break;
                default: // Mặc định là bán chạy 
                    $sql .= " ORDER BY Soluongton DESC"; // Nếu không có sản phẩm bán chạy, sắp xếp theo số lượng tồn
                    break;
            }
            // Giới hạn sản phẩm theo phân trang sau khi đã sắp xếp
            $sql .= " LIMIT $limit OFFSET $offset";


            $result = mysqli_query($con, $sql);

            $result = mysqli_query($con, $sql);

            // Kiểm tra và hiển thị các sản phẩm
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $Tenhang = $row['Tenhang'];
                    $Mota = $row['Mota'];
                    $anh = $row['anh'];
                    $don_gia = number_format($row['Dongia'], 0, '.', '.');
                    $soluongton = $row['Soluongton'];
                    $Mahang = $row['Mahang'];

                    // Đường dẫn đến hình ảnh "hết hàng"
                    $outOfStockImage = 'img/sold_out.png';

                    echo "
                        <a href='sp1.php?id=$Mahang'>
                            <div class='bg-card text-card-foreground p-4 rounded-lg shadow-md relative'>
                                <img src='../img/sản_phẩm/$anh' alt='$Tenhang' class='w-full h-81 object-cover rounded-lg mb-4' />
                                ";

                    // Hiển thị hình ảnh "hết hàng" nếu số lượng tồn bằng 0
                    if ($soluongton == 0) {
                        echo "
                                <img src='$outOfStockImage' alt='Hết hàng' class='out-of-stock opacity-90' />
                            ";
                    }

                    echo "
                                <h2 class='text-lg font-bold mb-2'>$Tenhang</h2>
                                <p class='text-sm text-muted-foreground mb-4'>$Mota</p>
                                <span class='text-lg font-bold text-black'>$don_gia VNĐ</span>
                            </div>
                        </a>";
                }
            } else {
                echo "<p class='text-center'>Không tìm thấy sản phẩm.</p>";
            }
            // Tính tổng số trang cho phân trang
            $count_sql = "SELECT COUNT(*) AS total FROM hang";

            // Thêm điều kiện lọc danh mục hoặc tìm kiếm
            if (!empty($category)) {
                $count_sql .= " WHERE Maloaihang = '$category'";
            }

            if (!empty($search)) {
                if (!empty($category)) {
                    $count_sql .= " AND";
                } else {
                    $count_sql .= " WHERE";
                }
                $count_sql .= " Tenhang LIKE '%$search%'";
            }

            $count_result = mysqli_query($con, $count_sql);
            $count_row = mysqli_fetch_assoc($count_result);
            $total_products = $count_row['total'];

            // Tính tổng số trang
            $total_pages = ceil($total_products / $limit);

            // Hiển thị nút phân trang
            echo "</div><div class='pagination flex justify-center space-x-2 my-4' style='margin-top: 0; margin-bottom: 2px;'>";
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<a class='pagination-btn $active' href='?page=$i&category=$category&search=$search'>$i</a>";
            }
            echo "</div>";
            // Đóng kết nối
            mysqli_close($con);
            ?>
        </div>

        <!-- Footer -->
        <footer class="bg-primary text-primary-foreground py-4 text-center">
            <p>&copy; 2024 Vương Moi Shop</p>
        </footer>
        </>

        <script src="script/trangchu.js"></script>
        <script src="script/banner.js"></script>
        <script>
            // Hàm cuộn mượt với thời gian tùy chỉnh
            function smoothScrollTo(element, duration) {
                const targetPosition = element.getBoundingClientRect().top + window.pageYOffset;
                const startPosition = window.pageYOffset;
                const distance = targetPosition - startPosition;
                let startTime = null;

                function animationScroll(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
                    window.scrollTo(0, run);
                    if (timeElapsed < duration) requestAnimationFrame(animationScroll);
                }

                function easeInOutQuad(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return c / 2 * t * t + b;
                    t--;
                    return -c / 2 * (t * (t - 2) - 1) + b;
                }

                requestAnimationFrame(animationScroll);
            }

            // Xử lý khi nhấn vào nút phân trang
            document.querySelectorAll('.pagination-btn').forEach(button => {
                button.addEventListener('click', function(event) {
                    // Ngăn chặn hành vi mặc định của nút liên kết
                    event.preventDefault();

                    // Lưu trạng thái cuộn vào localStorage
                    localStorage.setItem('scrollToProducts', 'true');

                    // Chuyển hướng đến URL của trang được chọn
                    const url = this.getAttribute('href');
                    window.location.href = url;
                });
            });

            const categoryDropdown = document.getElementById('category-dropdown');
            const categoryList = document.getElementById('category-list');

            categoryDropdown.addEventListener('mouseover', () => {
                // Hiển thị danh sách khi di chuột vào
                categoryList.style.display = 'block';
            });

            categoryDropdown.addEventListener('mouseout', () => {
                // Ẩn danh sách khi rời chuột
                categoryList.style.display = 'none';
            });
        </script>
</body>

</html>