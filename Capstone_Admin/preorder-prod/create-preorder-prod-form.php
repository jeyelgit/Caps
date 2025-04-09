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

// Initialize the preorder status variable
$orderStatus = ''; // or you can set it to a default value

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

// Fetch product data for dropdown (only readymade products)
$productQuery = "SELECT Product_ID, Product_Name, Price, GLB_File_URL FROM tbl_prod_info WHERE product_type = 'readymade'";
$productStmt = $pdo->prepare($productQuery);
$productStmt->execute();
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data for dropdown
$userQuery = "SELECT User_ID, CONCAT(First_Name, ' ', Last_Name) AS User_Name FROM tbl_user_info";
$userStmt = $pdo->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data safely
    $productID = isset($_POST['txtProductID']) ? $_POST['txtProductID'] : null;
    $userID = isset($_POST['txtUserID']) ? $_POST['txtUserID'] : null;
    $quantity = isset($_POST['txtQuantity']) ? $_POST['txtQuantity'] : null;
    $totalPrice = isset($_POST['txtTotalPrice']) ? $_POST['txtTotalPrice'] : null;
    $orderStatus = isset($_POST['txtOrderStatus']) ? $_POST['txtOrderStatus'] : 0;

    // Check for required fields
    if ($productID && $userID && $quantity && $totalPrice && $orderStatus !== null) {
        // Insert the preorder into the database
        $insertQuery = "INSERT INTO tbl_preorder (Product_ID, User_ID, Quantity, Total_Price, Preorder_Status) VALUES (:product_id, :user_id, :quantity, :total_price, :order_status)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->bindParam(':product_id', $productID);
        $insertStmt->bindParam(':user_id', $userID);
        $insertStmt->bindParam(':quantity', $quantity);
        $insertStmt->bindParam(':total_price', $totalPrice);
        $insertStmt->bindParam(':order_status', $orderStatus);

        // Execute the insert statement
        if ($insertStmt->execute()) {
            header("Location: read-all-preorder-form.php?message=Preorder created successfully");
            exit();
        } else {
            echo "Error creating preorder.";
        }
    } else {
        echo "Please fill all required fields.";
    }
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
            <form name="frmPreorder" method="POST" action="create-preorder-prod-rec.php">
                <h4>Create Preorder</h4>
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
                        <td>User ID:</td>
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
                        <td><input type="number" name="txtQuantity" id="quantityInput" min="1" required></td>
                    </tr>
                    <tr>
                        <td>Total Price:</td>
                        <td><input type="text" name="txtTotalPrice" id="totalPrice" readonly required></td>
                    </tr>
                </table>

                <div class="button-container">
                    <input type="submit" value="Submit" class="buttonUpdate">
                    <input type="reset" value="Reset" class="buttonDelete">
                    <a href="read-all-preorder-prod-form.php" target="_parent" class="buttonBack">Back to List</a>
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
                } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            };

            // Calculate total price
            const productSelect = document.getElementById('productSelect');
            const quantityInput = document.getElementById('quantityInput');
            const totalPriceInput = document.getElementById('totalPrice');

            function calculateTotalPrice() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 0;

                const totalPrice = price * quantity;
                totalPriceInput.value = totalPrice.toFixed(2); // Format to 2 decimal places
            }

            // Event listeners
            productSelect.addEventListener('change', calculateTotalPrice);
            quantityInput.addEventListener('input', calculateTotalPrice);

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
        <script>
            document.getElementById('productSelect').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const glbFileURL = selectedOption.getAttribute('data-glb');
                const modelViewer = document.getElementById('modelViewer');
                
                if (glbFileURL) {
                    modelViewer.src = glbFileURL;
                } else {
                    modelViewer.removeAttribute('src');
                }
            });
        </script>
    </section>
</body>
</html>
