<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Assuming the admin's ID is stored in session after login
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

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

try {
    // Query to fetch order details along with product name, 3D model URL, and user full name
    $query = "
        SELECT 
            r.ReadyMadeOrder_ID, 
            r.Product_ID, 
            p.Product_Name, 
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
        WHERE r.ReadyMadeOrder_ID = ?";


    // Prepare query and bind the order ID
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $_GET['id']);
    $stmt->execute();

    // Fetch the record
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "Ready-made order not found.";
        exit();
    }

    // Assign fetched data to variables
    $readyMadeOrderID = $row["ReadyMadeOrder_ID"];
    $productID = $row["Product_ID"];
    $productName = $row["Product_Name"];
    $glbFileURL = $row["GLB_File_URL"];
    $userID = $row["User_ID"];
    $userName = $row["User_Name"];
    $quantity = $row["Quantity"];
    $totalPrice = $row["Total_Price"];
    $orderStatus = $row["Order_Status"];
    $productStatus = $row["Product_Status"];
    $orderDate = $row["Order_Date"];

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
$orderStatusText = $orderStatusMap[$orderStatus] ?? 'Unknown Status';

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
$productStatusText = $productStatusLabels[$productStatus] ?? 'Unknown Status';


} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
    exit();
}
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
            <form name="frmReadyMadeRec" method="POST" action="">
                <h4>View Ready-Made Order Record</h4>
                <table>
                    <tr><td>Order ID:</td><td><?php echo $readyMadeOrderID; ?></td></tr>
                    <tr><td>Product Name:</td><td><?php echo $productName; ?></td></tr>
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
                    <tr><td>User Name:</td><td><?php echo $userName; ?></td></tr>
                    <tr><td>Quantity:</td><td><?php echo $quantity; ?></td></tr>
                    <tr><td>Total Price:</td><td><?php echo number_format($totalPrice, 2); ?></td></tr>
                    <tr><td>Status:</td><td><?php echo $orderStatusText; ?></td></tr>
                    <tr><td>Product Status:</td><td><?php echo $productStatusText; ?></td></tr>
                    <tr><td>Order Date:</td><td><?php echo $orderDate; ?></td></tr>
                </table>
                <div class="button-container">
                    <br>
                    <a href="read-all-readymade-form.php" class="buttonBack">Back to List</a>
                    <a href="update-readymade-form.php?id=<?php echo $readyMadeOrderID; ?>" class="buttonEdit">Edit</a>
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
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
