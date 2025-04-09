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

// Fetch products from the database (only readymade products)
$stmt = $pdo->prepare("SELECT Product_ID, Product_Name, Price, GLB_File_URL FROM tbl_prod_info WHERE product_type = 'readymade'");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users from the database
$stmt = $pdo->prepare("SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS User_Name FROM tbl_user_info");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <form name="frmReadyMadeOrder" method="POST" action="create-readymade-rec.php">
                <h4>Create Ready-Made Order</h4>
                <table>
                    <tr>
                        <td>Product:</td>
                        <td>
                            <select name="txtProductID" id="productSelect" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['Product_ID']; ?>" data-price="<?php echo htmlspecialchars($product['Price']); ?>" data-glb="<?php echo htmlspecialchars($product['GLB_File_URL']); ?>">
                                        <?php echo htmlspecialchars($product['Product_Name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>3D Model:</td>
                        <td>
                            <model-viewer id="modelViewer" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                        </td>
                    </tr>
                    <tr>
                        <td>User:</td>
                        <td>
                            <select name="txtUserID" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                        <?php echo htmlspecialchars($user['User_Name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input type="number" id="quantity" name="txtQuantity" min="1" required></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" id="totalPrice" name="txtTotalPrice" readonly required></td>
                    </tr>
                </table>
                <div class="button-container">
                    <input type="submit" value="Submit" class="buttonUpdate">
                    <a href="read-all-readymade-form.php" target="_parent" class="buttonBack">Back to List</a>
                </div>
            </form>
        </div>
    </section>

    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <script>
        document.getElementById('productSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const glbFileURL = selectedOption.getAttribute('data-glb');
            const modelViewer = document.getElementById('modelViewer');
            const quantityInput = document.getElementById('quantity');
            const totalPriceInput = document.getElementById('totalPrice');
            
            if (glbFileURL) {
                modelViewer.src = glbFileURL;
            } else {
                modelViewer.removeAttribute('src');
            }

            if (price && quantityInput.value) {
                totalPriceInput.value = (price * quantityInput.value).toFixed(2);
            } else {
                totalPriceInput.value = '';
            }
        });

        document.getElementById('quantity').addEventListener('input', function() {
            const selectedOption = document.getElementById('productSelect').options[document.getElementById('productSelect').selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const totalPriceInput = document.getElementById('totalPrice');
            
            if (price && this.value) {
                totalPriceInput.value = (price * this.value).toFixed(2);
            } else {
                totalPriceInput.value = '';
            }
        });
    </script>
</body>
</html>
