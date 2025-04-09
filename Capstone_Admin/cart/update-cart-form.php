<?php
session_start(); // Start the session

// Include the database connection
include_once '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /Capstone/login.php");
    exit();
}

// Fetch admin data
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Validate `Cart_ID`
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "Invalid or missing Cart ID.";
    exit();
}

// Fetch cart details
$cartID = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT c.*, p.Product_Name, p.GLB_File_URL, p.Price, p.product_type
    FROM tbl_cart c
    JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
    WHERE c.Cart_ID = :cart_id
");
$stmt->bindParam(':cart_id', $cartID, PDO::PARAM_INT);
$stmt->execute();
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    echo "Cart not found.";
    exit();
}

// Sanitize cart details
$userID = htmlspecialchars($cart['User_ID']);
$productID = htmlspecialchars($cart['Product_ID']);
$productName = htmlspecialchars($cart['Product_Name']);
$productGLB = htmlspecialchars($cart['GLB_File_URL']);
$quantity = htmlspecialchars($cart['Quantity']);
$price = htmlspecialchars($cart['Price']);
$totalPrice = number_format((float)$cart['Total_Price'], 2);
$orderType = htmlspecialchars($cart['Order_Type']);
$productType = htmlspecialchars($cart['product_type']);


// Fetch users
$userQuery = "SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS Full_Name FROM tbl_user_info";
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$productQuery = "SELECT Product_ID, Product_Name, Price FROM tbl_prod_info";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
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
            <form name="frmCartUpdate" method="POST" action="update-cart-rec.php">
                <h2>Update Cart Record</h2>
                <table>
                    <tr>
                        <td>Cart ID:</td>
                        <td><input type="text" name="txtCartID" value="<?php echo $cartID; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>User ID:</td>
                        <td><input type="text" name="txtUserID" value="<?php echo $userID; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Product ID:</td>
                        <td>
                            <input type="text" name="txtProductID" value="<?php echo $productID; ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><?php echo $productName; ?></td>
                    </tr>
                    <tr>
                        <td>3D Model:</td>
                        <td>
                            <model-viewer src="<?php echo $productGLB; ?>" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                        </td>
                    </tr>
                    <tr>
                        <td>Order Type:</td>
                        <td>
                            <select name="txtOrderType" required>
                                <option value="pre_order" <?php echo ($orderType === 'pre_order') ? 'selected' : ''; ?>>Pre Order</option>
                                <option value="ready_made" <?php echo ($orderType === 'ready_made') ? 'selected' : ''; ?>>Ready Made</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input type="number" id="quantity" name="txtQuantity" value="<?php echo $quantity; ?>" required oninput="calculateTotalPrice()"></td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td><input type="text" id="price" name="txtPrice" value="<?php echo $price; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" id="totalPrice" name="txtTotalPrice" value="<?php echo $totalPrice; ?>" readonly></td>
                    </tr>
                </table>
                <div class="button-container">
                <a href="read-all-cart-form.php" target="_parent" class="buttonBack">Back to Cart List</a>
                    <input type="submit" value="Update" class="buttonUpdate">
                    <!-- Add the checkout button -->
                    <button type="button" class="buttonCheckout" onclick="checkout()">Checkout</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Initialize DOM Elements
        const quantityInput = document.getElementById('quantity');
        const priceInput = document.getElementById('price');
        const totalPriceInput = document.getElementById('totalPrice');
        let productType = `<?php echo $productType; ?>`;
        let orderType = `<?php echo $orderType; ?>`;

        function calculateTotalPrice() {
            const price = parseFloat(priceInput.value) || 0;
            const quantity = parseInt(quantityInput.value) || 0;

            // Calculate and update total price
            const totalPrice = price * quantity;
            totalPriceInput.value = totalPrice.toFixed(2);
        }

        // Initialize prices on page load
        window.onload = calculateTotalPrice;

        // Add event listener to quantity input
        quantityInput.addEventListener('input', calculateTotalPrice);

        // New checkout function
        function checkout() {
            // Send an AJAX request to update-cart-rec.php to handle the checkout
            const cartID = <?php echo $cartID; ?>;
            const quantity = parseInt(quantityInput.value) || 0;
            const totalPrice = parseFloat(totalPriceInput.value) || 0;
            const productID = <?php echo $productID; ?>;
            const userID = '<?php echo $userID; ?>';


            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-cart-rec.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Handle the response from the server
                    alert(xhr.responseText);
                    window.location.href = 'read-all-cart-form.php'; //Redirect to cart page
                } else {
                    alert('Error during checkout.');
                }
            };
            xhr.send(`checkout=true&txtCartID=${cartID}&txtQuantity=${quantity}&txtTotalPrice=${totalPrice}&txtOrderType=${orderType}&txtProductID=${productID}&txtUserID=${userID}`);
        }

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
