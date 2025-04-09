<?php
session_start();

// Include database connection
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data for profile display
$adminId = $_SESSION['admin_id'];
if ($pdo) {
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
}

// Query to select preorder record details along with product and user information
$query = "
    SELECT 
        po.Preorder_ID, 
        po.Quantity, 
        po.Total_Price, 
        po.Preorder_Status,
        po.Product_Status,  -- Add this line
        p.Product_ID, 
        p.Product_Name,
        p.GLB_File_URL,
        u.User_ID, 
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name
    FROM tbl_preorder AS po
    JOIN tbl_prod_info AS p ON po.Product_ID = p.Product_ID
    JOIN tbl_user_info AS u ON po.User_ID = u.User_ID
    WHERE po.Preorder_ID = :preorder_id
";


$stmt = $pdo->prepare($query);
$stmt->bindParam(':preorder_id', $_GET['id']);
$stmt->execute();

$preorder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$preorder) {
    echo "Preorder not found.";
    exit();
}

$preorderID = $preorder["Preorder_ID"];
$productID = $preorder["Product_ID"];
$productName = $preorder["Product_Name"];
$userID = $preorder["User_ID"];
$userName = $preorder["User_Name"];
$quantity = $preorder["Quantity"];
$totalPrice = $preorder["Total_Price"];
$preorderStatus = $preorder["Preorder_Status"];
$glbFileURL = $preorder["GLB_File_URL"];

// Map order status to descriptive text
$orderStatusMap = [
    0   => 'Order Received',       // 0% - Order placed by the customer
    10  => 'Order Confirmed',      // 10% - Down payment received
    20  => 'Design Finalization',  // 20% - Final design confirmed
    30  => 'Material Preparation', // 30% - Sourcing and cutting materials
    40  => 'Production Started',   // 40% - Carpentry/assembly in progress
    50  => 'Mid-Production',       // 50% - Major structural work completed
    60  => 'Finishing Process',    // 60% - Upholstery, varnishing, detailing
    70  => 'Quality Check',        // 70% - Inspection for defects
    80  => 'Final Assembly',       // 80% - Last touches, packaging
    90  => 'Ready for Delivery',   // 90% - Scheduled for transport
    100 => 'Delivered / Completed' // 100% - Customer has received the furniture
];

// Get order status text
$orderStatusText = $orderStatusMap[$preorder['Preorder_Status']] ?? 'Unknown Status';

// Map product status to descriptive text
$productStatusLabels = [
    0   => 'Concept Stage',         // 0% - Idea or design submitted
    10  => 'Design Approved',       // 10% - Finalized by customer
    20  => 'Material Sourcing',     // 20% - Gathering necessary materials
    30  => 'Cutting & Shaping',     // 30% - Preparing materials
    40  => 'Structural Assembly',   // 40% - Base framework built
    50  => 'Detailing & Refinements', // 50% - Carvings, upholstery, elements added
    60  => 'Sanding & Pre-Finishing', // 60% - Smoothening, preparing for final coat
    70  => 'Varnishing/Painting',   // 70% - Applying the final finish
    80  => 'Drying & Curing',       // 80% - Final coating sets in
    90  => 'Final Inspection & Packaging', // 90% - Quality control before handover
    100 => 'Completed'              // 100% - Ready for pickup/delivery
];

// Get product status text
$productStatusText = $productStatusLabels[$preorder['Product_Status']] ?? 'Unknown Status';

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
            <form name="frmPreorderRec" method="POST" action="update-preorder-prod-rec.php">
                <h4>UPDATE PREORDER RECORD</h4>
                <table>
                    <tr>
                        <td>Preorder ID:</td>
                        <td><input type="text" name="txtPreorderID" value="<?php echo htmlspecialchars($preorderID); ?>" readonly></td>
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
                        <td>Product Name:</td>
                        <td><input type="text" name="txtProductName" value="<?php echo htmlspecialchars($productName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>User ID:</td>
                        <td><input type="text" name="txtUserID" value="<?php echo htmlspecialchars($userID); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>User Name:</td>
                        <td><input type="text" name="txtUserName" value="<?php echo htmlspecialchars($userName); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input type="number" id="quantity" name="txtQuantity" value="<?php echo htmlspecialchars($quantity); ?>" oninput="calculateTotalPrice()"></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" id="totalPrice" name="txtTotalPrice" value="<?php echo htmlspecialchars($totalPrice); ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Preorder Status:</td>
                        <td>
                            <select name="status" required>
                                <?php foreach ($orderStatusMap as $key => $label) : ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($preorderStatus == $key) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Product Status:</td>
                        <td>
                            <select name="product_status" required>
                                <?php foreach ($productStatusLabels as $key => $label) : ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($preorder['Product_Status'] == $key) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
    <td colspan="2"> <!-- Ensures buttons span across the table if needed -->
        <div class="button-container">
            <a href="read-all-preorder-prod-form.php" target="_parent" class="buttonBack">Back to List</a>
            <button type="submit" class="buttonUpdate">Update</button>
        </div>
    </td>
</tr>

        </div>
    </section>
    <script>
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
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
