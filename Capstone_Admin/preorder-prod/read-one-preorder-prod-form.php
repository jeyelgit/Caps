<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Fetch admin data from the database
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

// Check if Preorder_ID is passed in the URL
if (isset($_GET['id'])) {
    $preorderID = $_GET['id'];
    
    try {
        // Fetch preorder details
        $query = "
            SELECT 
                po.*, 
                po.Product_Status,  -- Fix: Get Product_Status from tbl_preorder
                p.Product_Name,  
                p.GLB_File_URL,
                u.First_Name, 
                u.Last_Name
            FROM tbl_preorder po
            JOIN tbl_prod_info p ON po.Product_ID = p.Product_ID
            JOIN tbl_user_info u ON po.User_ID = u.User_ID
            WHERE po.Preorder_ID = :preorderID
        ";
;

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':preorderID', $preorderID, PDO::PARAM_INT);
        $stmt->execute();
        $preorder = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($preorder) {
            $productID = htmlspecialchars($preorder['Product_ID']);
            $productName = htmlspecialchars($preorder['Product_Name']);
            $productStatus = htmlspecialchars($preorder['Product_Status']); // Add this
            $userID = htmlspecialchars($preorder['User_ID']);
            $userName = htmlspecialchars($preorder['First_Name'] . ' ' . $preorder['Last_Name']);
            $quantity = htmlspecialchars($preorder['Quantity']);
            $totalPrice = htmlspecialchars($preorder['Total_Price']);
            $preorderStatus = htmlspecialchars($preorder['Preorder_Status']);
            $orderDate = htmlspecialchars($preorder['Order_Date']);
            $glbFileURL = htmlspecialchars($preorder['GLB_File_URL']);
        }
         else {
            echo "Preorder not found.";
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    echo "No preorder ID provided.";
    exit();
}

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
            <form name="frmPreorderRec" method="POST" action="">
                <h4>View Preorder Record</h4>
                <table>
    <tr>
        <td>Preorder ID:</td>
        <td><?= $preorderID ?></td>
    </tr>
    <tr>
        <td>Product ID:</td>
        <td><?= $productID ?></td>
    </tr>
    <tr>
        <td>3D Model:</td>
        <td>
            <?php if ($glbFileURL): ?>
                <model-viewer src="<?= $glbFileURL ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
            <?php else: ?>
                No 3D model available.
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>Product Name:</td>
        <td><?= $productName ?></td>
    </tr>
    <tr>
        <td>User ID:</td>
        <td><?= $userID ?></td>
    </tr>
    <tr>
        <td>User Name:</td>
        <td><?= $userName ?></td>
    </tr>
    <tr>
        <td>Quantity:</td>
        <td><?= $quantity ?></td>
    </tr>
    <tr>
        <td>Total Price:</td>
        <td><?= $totalPrice ?></td>
    </tr>
    <tr>
        <td>Preorder Status:</td>
        <td><?= $orderStatusText ?></td>
    </tr>
    <tr>
        <td>Product Status:</td>
        <td><?= $productStatusText ?></td>
    </tr>
    <tr>
        <td>Order Date:</td>
        <td><?= $orderDate ?></td>
    </tr>
</table>

<div class="button-container">
    <a href="read-all-preorder-prod-form.php" class="buttonBack">Back to Preorders</a>
    <a class="buttonEdit" href="update-preorder-prod-form.php?id=<?php echo $preorderID; ?>" target="_parent">Edit</a>
</div>
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
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
