<?php
session_start(); // Start the session

// Include the database connection
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

// Fetch order details
$orderId = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT 
        r.ReadyMadeOrder_ID, 
        r.Product_ID, 
        p.Product_Name, 
        p.Price, 
        p.GLB_File_URL,
        r.User_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name, 
        r.Quantity, 
        r.Total_Price, 
        r.Order_Status, 
        r.Product_Status, 
        r.Order_Date 
    FROM tbl_ready_made_orders r
    JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    WHERE r.ReadyMadeOrder_ID = ?
");
$stmt->bindValue(1, $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit();
}

// Assign fetched data
$readyMadeOrderID = $order["ReadyMadeOrder_ID"];
$productID = $order["Product_ID"];
$productName = $order["Product_Name"];
$productPrice = $order["Price"];
$glbFileURL = $order["GLB_File_URL"];
$userID = $order["User_ID"];
$userName = $order["User_Name"];
$quantity = $order["Quantity"];
$totalPrice = $order["Total_Price"];
$orderStatus = $order["Order_Status"];
$productStatus = $order["Product_Status"];
$orderDate = $order["Order_Date"];

// Order status mapping for Readymade
$orderStatusMap = [
    0   => 'Order Received (0%)',
    10  => 'Order Confirmed (10%)',
    70  => 'Quality Check (70%)',
    90  => 'Ready for Delivery (90%)',
    100 => 'Delivered / Completed (100%)'
];

// Product status mapping for Readymade
$productStatusMap = [
    90 => 'Final Inspection & Packaging (90%)',
    100 => 'Completed (100%)'
];

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
                <a href="../dashboard/dashboard.php" class="">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
         
            <li>
                <a href="../purchase-history/read-all-history-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
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
            <form name="frmReadyMadeOrder" method="POST" action="update-readymade-rec.php">
                <h4>Update Ready-Made Order</h4>
                <table>
                    <tr>
                        <td>Order ID:</td>
                        <td><input type="text" name="txtOrderID" value="<?php echo htmlspecialchars($readyMadeOrderID); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><input type="text" name="txtProductName" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>3D Model:</td>
                        <td>
                            <?php if ($glbFileURL): ?>
                                <model-viewer src="<?php echo $glbFileURL; ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                            <?php else: ?>
                                No 3D model available.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td><input type="text" name="txtUserName" value="<?php echo htmlspecialchars($userName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input type="number" id="quantity" name="txtQuantity" value="<?php echo htmlspecialchars($quantity); ?>" min="1" required></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" id="totalPrice" name="txtTotalPrice" value="<?php echo htmlspecialchars($totalPrice); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Order Status:</td>
                        <td>
                            <select name="txtOrderStatus">
                                <?php foreach ($orderStatusMap as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo ($orderStatus == $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Product Status:</td>
                        <td>
                            <select name="txtProductStatus">
                                <?php foreach ($productStatusMap as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo ($productStatus == $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="button-container">
                   
                    <a href="read-all-readymade-form.php" class="buttonBack">Back to List</a>
                    <input type="submit" value="Update" class="buttonUpdate">
                </div>
            </form>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        };
        
        document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', function () {
            const parent = this.parentElement; // Get the parent <li> of the toggle
            const dropdownMenu = parent.querySelector('.dropdown-menu'); // Get the <ul> of the dropdown menu
            parent.classList.toggle('active'); // Toggle the 'active' class on the parent <li>

            // Toggle the chevron icon rotation
            const chevron = this.querySelector('i'); // Find the chevron icon inside the toggle
            if (parent.classList.contains('active')) {
                chevron.classList.remove('bx-chevron-down');
                chevron.classList.add('bx-chevron-up'); // Change to up when menu is open
            } else {
                chevron.classList.remove('bx-chevron-up');
                chevron.classList.add('bx-chevron-down'); // Change to down when menu is closed
            }
            
            // Toggle the display of the dropdown menu
            dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
        });
    });


    </script>
</body>
</html>
