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

// Handle AJAX search requests
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "
        SELECT 
            tbl_cart.Cart_ID,
            tbl_user_info.First_Name AS User_Name,
            tbl_prod_info.Product_Name,
            tbl_cart.Order_Type,
            tbl_cart.Quantity,
            tbl_cart.Total_Price
        FROM tbl_cart
        JOIN tbl_user_info ON tbl_cart.User_ID = tbl_user_info.User_ID
        JOIN tbl_prod_info ON tbl_cart.Product_ID = tbl_prod_info.Product_ID
        WHERE tbl_user_info.First_Name LIKE :search 
        OR tbl_user_info.Last_Name LIKE :search 
        OR tbl_prod_info.Product_Name LIKE :search
        OR tbl_cart.Order_Type LIKE :search
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the full table structure for AJAX requests
    echo '<table width="100%" border="1" cellspacing="5">
        <thead>
            <tr>
                <th>USER NAME</th>
                <th>PRODUCT NAME</th>
                <th>ORDER TYPE</th>
                <th>QUANTITY</th>
                <th>TOTAL PRICE</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($rows as $row) { 
        $cartID = htmlspecialchars($row["Cart_ID"]);
        $userName = htmlspecialchars($row["User_Name"]);
        $productName = htmlspecialchars($row["Product_Name"]);
        $orderType = htmlspecialchars($row["Order_Type"]);
        $quantity = htmlspecialchars($row["Quantity"]);
        $totalPrice = number_format((float)$row["Total_Price"], 2, '.', '');

        echo '
        <tr>
            <td>'.$userName.'</td>
            <td>'.$productName.'</td>
            <td>'.$orderType.'</td>
            <td>'.$quantity.'</td>
            <td>'.$totalPrice.'</td>
            <td><a class="buttonView" href="read-one-cart-form.php?id='.$cartID.'" target="_parent">View</a></td>
            <td><a class="buttonEdit" href="update-cart-form.php?id='.$cartID.'" target="_parent">Edit</a></td>
            <td><a class="buttonDelete" href="delete-cart-form.php?id='.$cartID.'" target="_parent">Delete</a></td>
        </tr>';
    }
    echo '</tbody></table>';
    exit; // Stop further execution for AJAX requests
}

// Fetch cart records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        tbl_cart.Cart_ID,
        tbl_user_info.First_Name AS User_Name,
        tbl_prod_info.Product_Name,
        tbl_cart.Order_Type,
        tbl_cart.Quantity,
        tbl_cart.Total_Price
    FROM tbl_cart
    JOIN tbl_user_info ON tbl_cart.User_ID = tbl_user_info.User_ID
    JOIN tbl_prod_info ON tbl_cart.Product_ID = tbl_prod_info.Product_ID
    WHERE tbl_user_info.First_Name LIKE :search 
    OR tbl_user_info.Last_Name LIKE :search 
    OR tbl_prod_info.Product_Name LIKE :search
    OR tbl_cart.Order_Type LIKE :search
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" /> -->

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
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" id="search" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" />
        </form>
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
</nav>

<br>
<br>
<br>
<div class="container_boxes">
    <h4>CART LIST <a href="create-cart-form.php">Create New Cart</a></h4>
    <!-- Add Back to Dashboard button -->
    <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
    <div id="cart-list">
        <table width="100%" border="1" cellspacing="5">
            <tr>
                <th>USER NAME</th>
                <th>PRODUCT NAME</th>
                <th>ORDER TYPE</th>
                <th>QUANTITY</th>
                <th>TOTAL PRICE</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
            <?php
            foreach ($rows as $row) { 
                $cartID = htmlspecialchars($row["Cart_ID"]);
                $userName = htmlspecialchars($row["User_Name"]);
                $productName = htmlspecialchars($row["Product_Name"]);
                $orderType = htmlspecialchars($row["Order_Type"]);
                $quantity = htmlspecialchars($row["Quantity"]);
                $totalPrice = number_format((float)$row["Total_Price"], 2, '.', '');

                echo '
                <tr>
                    <td>'.$userName.'</td>
                    <td>'.$productName.'</td>
                    <td>'.$orderType.'</td>
                    <td>'.$quantity.'</td>
                    <td>'.$totalPrice.'</td>
                    <td><a class="buttonView" href="read-one-cart-form.php?id='.$cartID.'" target="_parent">View</a></td>
                    <td><a class="buttonEdit" href="update-cart-form.php?id='.$cartID.'" target="_parent">Edit</a></td>
                    <td><a class="buttonDelete" href="delete-cart-form.php?id='.$cartID.'" target="_parent">Delete</a></td>
                </tr>';
            }
            ?>
        </table>
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

document.getElementById('search').addEventListener('input', function () {
    const searchValue = this.value.trim();

    // Send an AJAX request to fetch filtered results
    fetch(`read-all-cart-form.php?search=${encodeURIComponent(searchValue)}`)
        .then(response => response.text())
        .then(data => {
            // Update the cart list with the filtered results
            const cartList = document.getElementById('cart-list');
            cartList.innerHTML = data.trim(); // Ensure no extra whitespace is added
        })
        .catch(error => console.error('Error fetching search results:', error));
});
    </script>
</body>
</html>
