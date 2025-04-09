<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Get the Progress_ID from the URL
if (!isset($_GET['id'])) {
    echo "Progress ID not specified.";
    exit();
}

$id = $_GET['id'];

// Query to fetch progress record
$query = "
    SELECT
        p.Progress_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Order_Type,
        p.Order_Status,
        p.Product_Status,
        p.Total_Price,
        p.Date_Added AS Request_Date,
        p.LastUpdate AS Last_Update,
        p.Progress_Pic_10,
        p.Progress_Pic_20,
        p.Progress_Pic_30,
        p.Progress_Pic_40,
        p.Progress_Pic_50,
        p.Progress_Pic_60,
        p.Progress_Pic_70,
        p.Progress_Pic_80,
        p.Progress_Pic_90,
        p.Progress_Pic_100,
        p.Stop_Reason
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON p.Product_ID = pr.Product_ID
    WHERE p.Progress_ID = :id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Record not found.";
    exit();
}

// Map Order Status and Product Status to labels
$orderStatusLabels = [
    0 => 'Order Received',
    10 => 'Order Confirmed',
    20 => 'In Production',
    30 => '30% Complete',
    40 => '40% Complete',
    50 => '50% Complete',
    60 => '60% Complete',
    70 => '70% Complete',
    80 => '80% Complete',
    90 => '90% Complete',
    100 => 'Completed'
];

$productStatusLabels = $orderStatusLabels; // Assuming same mapping
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    

    
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../static/js/dashboard.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <!-- <link href="../static/css-files/dashboard.css" rel="stylesheet"> -->
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="../static/js/admin_home.js" rel="">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_name">
        </span>
    </div>
        <ul class="nav-links">
        
            <li>
                <a href="../dashboard/dashboard.php" class="active">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
         
            <!-- <li>
                <a href="../purchase-history/read-all-history-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li> -->
            <li>
    <a href="../reviews/read-all-reviews-form.php">
        <i class="bx bx-message-dots"></i> <!-- Changed to a more appropriate message icon -->
        <span class="links_name">All Reviews</span>
    </a>
</li>
        </ul>

    </div>

    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Dashboard</span>
            </div>
       


            <div class="profile-details" onclick="toggleDropdown()">
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <!-- Modified link here -->
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>
 <br><br><br>
<div class="container_boxes">
    <h4>PROGRESS DETAILS</h4>
    
    <table class="table table-bordered">
        <tr><th>Order Type</th><td><?= htmlspecialchars($row['Order_Type']) ?></td></tr>
        <tr><th>Order Status</th><td><?= $orderStatusLabels[$row['Order_Status']] ?></td></tr>
        <tr><th>Product Status</th><td><?= $productStatusLabels[$row['Product_Status']] ?></td></tr>
        <tr><th>Total Price</th><td><?= htmlspecialchars($row['Total_Price']) ?></td></tr>
        <tr><th>Request Date</th><td><?= htmlspecialchars($row['Request_Date']) ?></td></tr>
        <tr><th>Last Update</th><td><?= htmlspecialchars($row['Last_Update']) ?></td></tr>
    </table>
    
    <h3>Progress Pictures</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Progress</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $percentage): ?>
                <?php $picKey = "Progress_Pic_$percentage"; ?>
                <?php if (!empty($row[$picKey])): ?>
                    <tr>
                        <td><strong><?= $percentage ?>%</strong></td>
                        <td><img src="<?= htmlspecialchars($row[$picKey]) ?>" alt="Progress <?= $percentage ?>%" width="100px" height="auto"></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <table class="table table-bordered">
        <tr><th>Stop Reason</th><td><?= htmlspecialchars($row['Stop_Reason'] ?? 'N/A') ?></td></tr>
    </table>
    
    <div class="button-container">
    
        <a href="read-all-progress-form.php" class="buttonBack">Back to List</a>
        <td style="text-align: center;"><a class="buttonEdit" href="update-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Edit</a></td>
    </div>
</div>

    <script>
        function showProgressPic(picUrl) {
            const picRow = document.getElementById('progress-pic-row');
            const picImg = document.getElementById('progress-pic');
            if (picUrl) {
                picImg.src = picUrl;
                picRow.style.display = 'table-row';
            } else {
                picRow.style.display = 'none';
            }
        }

        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        };

        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const parent = this.parentElement;
                const dropdownMenu = parent.querySelector('.dropdown-menu');
                parent.classList.toggle('active');

                const chevron = this.querySelector('i');
                if (parent.classList.contains('active')) {
                    chevron.classList.remove('bx-chevron-down');
                    chevron.classList.add('bx-chevron-up');
                } else {
                    chevron.classList.remove('bx-chevron-up');
                    chevron.classList.add('bx-chevron-down');
                }

                dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
