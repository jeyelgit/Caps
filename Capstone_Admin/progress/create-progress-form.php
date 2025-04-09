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

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Fetch users from the database
$userStmt = $pdo->prepare("SELECT User_ID, First_Name FROM tbl_user_info");
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products from the database, including Product_Name and Price
$productStmt = $pdo->prepare("SELECT Product_ID, Product_Name, Price FROM tbl_prod_info");
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

$preorderStatus = isset($_GET['preorderStatus']) ? $_GET['preorderStatus'] : 'Pending';  
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
    <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" />
    <span class="admin_name"><?php echo $adminName; ?></span>
    <i class="bx bx-chevron-down dropdown-button"></i>

    <div class="dropdown" id="profileDropdown">
        <a href="../admin/read-one-admin-form.php">Settings</a>
        <a href="../admin/logout.php">Logout</a>
    </div>
</div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

        <br><br><br>

        <div class="container_boxes">
        <form name="frmProgress" method="POST" enctype="multipart/form-data" action="create-progress-rec.php">
            <h4>Create New Progress</h4>
            <table>
            <tr>
                <td>User ID:</td>
                <td>
                    <select name="User_ID" required>
                        <option value="" disabled selected>Select User</option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?= htmlspecialchars($user['User_ID']) ?>"><?= htmlspecialchars($user['First_Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Product:</td>
                <td>
                    <select id="productSelect" name="Product_ID" required>
                        <option value="" disabled selected>Select Product</option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?= htmlspecialchars($product['Product_ID']) ?>" data-price="<?= htmlspecialchars($product['Price']) ?>">
                                <?= htmlspecialchars($product['Product_Name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Order Type:</td>
                <td>
                    <select name="txtOrderType" required>
                        <option value="" disabled selected>Select Order Type</option>
                        <option value="Preorder">Preorder</option>
                        <option value="Ready Made">Ready Made</option>
                        <option value="Customized">Customized</option>
                    </select>
                </td>
            </tr>

            <!-- Updated Status dropdown -->
            <tr>
                <td>Status:</td>
                <td>
                    <select name="txtStatus">
                        <option value="Pending" <?php echo ($preorderStatus == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Confirmed" <?php echo ($preorderStatus == 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="Shipped" <?php echo ($preorderStatus == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Delivered" <?php echo ($preorderStatus == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Canceled" <?php echo ($preorderStatus == 'Canceled') ? 'selected' : ''; ?>>Canceled</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Quantity:</td>
                <td><input type="number" id="quantityInput" name="Quantity" min="1" required></td>
            </tr>
            <tr>
                <td>Total Price:</td>
                <td><input type="text" id="totalPriceInput" name="Total_Price" readonly></td>
            </tr>
            </table>

            <div class="button-container">
                <input type="submit" value="Submit" class="buttonUpdate">
                <input type="reset" value="Reset" class="buttonDelete">
                <a href="read-all-progress-form.php" target="_parent" class="buttonBack">Back to List</a>
            </div>
        </form>
        </div>

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
    </section>
</body>
</html>
