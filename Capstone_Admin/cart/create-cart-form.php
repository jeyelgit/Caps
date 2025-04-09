<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login/login.php");
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

// Fetch product data
$productQuery = "SELECT Product_ID, Product_Name, Price, GLB_File_URL FROM tbl_prod_info";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data
$userQuery = "SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS Full_Name FROM tbl_user_info";
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Add to Cart</h2>
        <form name="frmCart" method="POST" action="create-cart-rec.php">
            <table>
                <tr>
                    <td><label for="userId">User:</label></td>
                    <td>
                        <select name="txtUserId" id="userId" required>
                            <option value="" disabled selected>Select User</option>
                            <?php foreach ($users as $user) { ?>
                                <option value="<?php echo $user['User_ID']; ?>"><?php echo $user['Full_Name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="productId">Product:</label></td>
                    <td>
                        <select name="txtProductId" id="productId" required>
                            <option value="" disabled selected>Select Product</option>
                            <?php foreach ($products as $product) { ?>
                                <option value="<?php echo $product['Product_ID']; ?>" data-price="<?php echo $product['Price']; ?>" data-glb="<?php echo $product['GLB_File_URL']; ?>">
                                    <?php echo $product['Product_Name']; ?>
                                </option>
                            <?php } ?>
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
                    <td><label for="quantity">Quantity:</label></td>
                    <td><input type="number" name="txtQuantity" id="quantity" value="1" required></td>
                </tr>
                <tr>
                    <td><label for="price">Price:</label></td>
                    <td><input type="number" step="0.01" name="txtPrice" id="price" value="" readonly required></td>
                </tr>
                <tr>
                    <td><label for="totalPrice">Total Price:</label></td>
                    <td><input type="number" step="0.01" name="txtTotalPrice" id="totalPrice" value="" readonly required></td>
                </tr>
                <tr>
                    <td><label for="orderType">Order Type:</label></td>
                    <td>
                        <select name="txtOrderType" id="orderType" required>
                            <option value="ready_made" selected>Ready Made</option>
                            <option value="pre_order">Pre Order</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Submit">
                        <input type="reset" value="Reset">
                        <a href="../cart/read-all-cart-form.php" target="_parent">Back to Cart List</a>
                    </td>
                </tr>
            </table>
        </form>

        <script>
            const productSelect = document.getElementById('productId');
            const quantityInput = document.getElementById('quantity');
            const totalPriceInput = document.getElementById('totalPrice');
            const priceInput = document.getElementById('price');
            const modelViewer = document.getElementById('modelViewer');

            function calculateTotalPrice() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 0;

                const totalPrice = price * quantity;
                totalPriceInput.value = totalPrice.toFixed(2);
                priceInput.value = price.toFixed(2);

                //set the model if file is available
                const glbFileURL = selectedOption.getAttribute('data-glb');
                if (glbFileURL) {
                   modelViewer.setAttribute('src', glbFileURL);
                } else {
                  modelViewer.removeAttribute('src');
               }
            }

            productSelect.addEventListener('change', calculateTotalPrice);
            quantityInput.addEventListener('input', calculateTotalPrice);

            window.onload = calculateTotalPrice;

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
