<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: /Capstone/login.php");
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

// Disable error reporting (consider removing this for debugging)
error_reporting(0);

// Get Cart_ID from the URL
$cartID = $_GET['id'];

// Fetch the cart record with the Product Name and Order Type
$query = "
    SELECT 
        c.Cart_ID, 
        c.User_ID, 
        c.Product_ID, 
        c.Quantity, 
        c.Price, 
        c.Total_Price, 
        c.Order_Type, 
        c.Date_Added, 
        p.Product_Name,
        p.Description,
        p.Category,
        p.Stock,
        p.GLB_File_URL
    FROM 
        tbl_cart c
    JOIN 
        tbl_prod_info p
    ON 
        c.Product_ID = p.Product_ID 
    WHERE 
        c.Cart_ID = :cart_id
";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->bindParam(':cart_id', $cartID);
$stmt->execute();
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the cart record exists
if (!$cart) {
    echo "Cart not found.";
    exit();
}

// Store data records to variables
$userID = htmlspecialchars($cart["User_ID"]);
$productID = htmlspecialchars($cart["Product_ID"]);
$productName = htmlspecialchars($cart["Product_Name"]);
$quantity = htmlspecialchars($cart["Quantity"]);
$price = number_format($cart["Price"], 2);
$totalPrice = number_format($cart["Total_Price"], 2);
$orderType = htmlspecialchars($cart["Order_Type"]);
$dateAdded = htmlspecialchars($cart["Date_Added"]);
$productDesc = htmlspecialchars($cart['Description'] ?? "No description available.");
$productCategory = htmlspecialchars($cart['Category'] ?? "Not specified.");
$productStock = htmlspecialchars($cart['Stock'] ?? "Not available.");
$productGLB = htmlspecialchars($cart['GLB_File_URL'] ?? "");

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
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
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
        <a href="../admin/read-one-admin-form.php">Settings</a>
        <a href="../admin/logout.php">Logout</a>
    </div>
</div>

<!-- Link to External JS -->
<script src="../static/js/dashboard.js"></script>


 </nav>

 <br><br><br>
<div class="container_boxes">
    <h4>CART DETAILS</h4>
    <table class="table table-bordered">
        <tr><th>Field</th><th>Value</th></tr>
        <tr><td>Cart ID:</td><td><?php echo $cartID; ?></td></tr>
        <tr><td>User ID:</td><td><?php echo $userID; ?></td></tr>
        <tr><td>Product ID:</td><td><?php echo $productID; ?></td></tr>
        <tr><td>Product Name:</td><td><?php echo $productName; ?></td></tr>
        <tr><td>Quantity:</td><td><?php echo $quantity; ?></td></tr>
        <tr><td>Price:</td><td><?php echo $price; ?></td></tr>
        <tr><td>Total Price:</td><td><?php echo $totalPrice; ?></td></tr>
        <tr><td>Order Type:</td><td><?php echo ucfirst($orderType); ?></td></tr>
        <tr><td>Date Added:</td><td><?php echo $dateAdded; ?></td></tr>
        <tr><td>Product Description:</td><td><?php echo $productDesc; ?></td></tr>
        <tr><td>Category:</td><td><?php echo $productCategory; ?></td></tr>
        <tr><td>Stock:</td><td><?php echo $productStock; ?></td></tr>
        <tr><td>3D Model:</td>
            <td>
                <?php if (!empty($productGLB)) : ?>
                    <model-viewer src="<?php echo $productGLB; ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                <?php else: ?>
                    No 3D model available
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <div class="button-container">
        <a href="read-all-cart-form.php" target="_parent" class="buttonBack">Back to Cart List</a>
        <a href="update-cart-form.php?id=<?php echo $cartID; ?>" target="_parent" class="buttonEdit">Edit</a>
    </div>
</div>

    <script>

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
    </section>
</body>
</html>
