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

// Fetch preorder record based on the provided ID, including the product name and user name
$query = "
    SELECT 
        p.Preorder_ID, 
        p.Product_ID, 
        pi.Product_Name, 
        p.User_ID, 
        ui.First_Name AS User_First_Name, 
        ui.Last_Name AS User_Last_Name, 
        p.Quantity, 
        p.Total_Price, 
        p.Preorder_Status
    FROM tbl_preorder p
    JOIN tbl_prod_info pi ON p.Product_ID = pi.Product_ID
    JOIN tbl_user_info ui ON p.User_ID = ui.User_ID
    WHERE p.Preorder_ID = :preorderID
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':preorderID', $_GET['id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Store data records to variables
$preorderID = $row["Preorder_ID"];
$productID = $row["Product_ID"];
$productName = $row["Product_Name"];  // Fetch product name from tbl_prod_info
$userID = $row["User_ID"];
$userName = $row["User_First_Name"] . ' ' . $row["User_Last_Name"];  // Fetch user name (first and last name) from tbl_user_info
$quantity = $row["Quantity"];
$totalPrice = $row["Total_Price"];
$preorderStatus = $row["Preorder_Status"];

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
    <form method="POST" action="delete-preorder-rec.php">
        <h2>Delete Preorder Record</h2>
        <p>Are you sure you want to delete the following preorder?</p>
        <table>
            <?php
            echo '
            <tr>
                <td>Preorder ID:</td>
                <td>' . htmlspecialchars($preorderID) . '</td>
            </tr>
            <tr>
                <td>Product ID:</td>
                <td>' . htmlspecialchars($productID) . '</td>
            </tr>
            <tr>
                <td>Product Name:</td>
                <td>' . htmlspecialchars($productName) . '</td>
            </tr>
            <tr>
                <td>User ID:</td>
                <td>' . htmlspecialchars($userID) . '</td>
            </tr>
            <tr>
                <td>User Name:</td>
                <td>' . htmlspecialchars($userName) . '</td>
            </tr>
            <tr>
                <td>Quantity:</td>
                <td>' . htmlspecialchars($quantity) . '</td>
            </tr>
            <tr>
                <td>Total Price:</td>
                <td>' . htmlspecialchars($totalPrice) . '</td>
            </tr>
            <tr>
                <td>Preorder Status:</td>
                <td>' . htmlspecialchars($preorderStatus) . '</td>
            </tr>';
            ?>
        </table>

        <!-- Separated buttons -->
        <div class="button-container">
            <a href="read-all-preorder-prod-form.php" target="_parent" class="buttonBack">Back to List</a>
            <a href="delete-preorder-prod-rec.php?id=<?php echo htmlspecialchars($preorderID); ?>" target="_parent" class="buttonDelete">Delete Record</a>
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

</body>
</html>
