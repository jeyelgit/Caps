<?php
session_start(); // Start the session

// Include the database connection
include_once '../config/database.php';

// Assuming the admin's ID is stored in session after login
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

// Ensure 'id' is present and is a valid integer
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "Invalid Cart ID.";
    exit();
}

// Fetch cart details from the database
$query = "
    SELECT c.Cart_ID, c.User_ID, c.Product_ID, p.Product_Name, c.Quantity, c.Price, c.Total_Price, c.Order_Type,
           u.First_Name AS User_First_Name, u.Last_Name AS User_Last_Name, u.Email_Address, u.Mobile_Number
    FROM tbl_cart c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
    WHERE c.Cart_ID = :cart_id
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':cart_id', $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    echo "No cart found.";
    exit();
}

$cartID = htmlspecialchars($cart["Cart_ID"]);
$userID = htmlspecialchars($cart["User_ID"]);
$productID = htmlspecialchars($cart["Product_ID"]);
$productName = htmlspecialchars($cart["Product_Name"]);
$quantity = htmlspecialchars($cart["Quantity"]);
$price = htmlspecialchars($cart["Price"]);
$totalPrice = htmlspecialchars($cart["Total_Price"]);
$orderType = htmlspecialchars($cart["Order_Type"]);
$userFirstName = htmlspecialchars($cart["User_First_Name"]);
$userLastName = htmlspecialchars($cart["User_Last_Name"]);
$userEmail = htmlspecialchars($cart["Email_Address"]);
$userMobile = htmlspecialchars($cart["Mobile_Number"]);
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
            <form name="frmCartDelete" method="POST" action="delete-cart-rec.php">
                <h2>Delete Cart Record</h2>
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
                        <td>User Name:</td>
                        <td><input type="text" name="txtUserName" value="<?php echo $userFirstName . ' ' . $userLastName; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                        <td><input type="text" name="txtEmail" value="<?php echo $userEmail; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Mobile Number:</td>
                        <td><input type="text" name="txtMobile" value="<?php echo $userMobile; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><input type="text" name="txtProductName" value="<?php echo $productName; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td><input type="number" name="txtQuantity" value="<?php echo $quantity; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td><input type="text" name="txtPrice" value="<?php echo $price; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" name="txtTotalPrice" value="<?php echo $totalPrice; ?>" readonly></td>
                    </tr>
                    <tr>
                        <td>Order Type:</td>
                        <td><input type="text" name="txtOrderType" value="<?php echo $orderType; ?>" readonly></td>
                    </tr>
                </table>

                <div class="button-container">
                    <input type="submit" value="Delete" class="buttonDelete" style="background-color: red;">
                    <a href="read-all-cart-form.php" target="_parent" class="buttonBack">Back to Cart List</a>
                </div>
            </form>
        </div>

        <script>
            const productSelect = document.getElementById('productId');
            const quantityInput = document.getElementById('quantity');
            const totalPriceInput = document.getElementById('totalPrice');
            const priceInput = document.getElementById('price');

            function calculateTotalPrice() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 0;

                const totalPrice = price * quantity;
                totalPriceInput.value = totalPrice.toFixed(2);
                priceInput.value = price.toFixed(2);
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
