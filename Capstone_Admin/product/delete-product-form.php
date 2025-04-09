<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in session after login
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

// Disable error reporting for development only (optional)
error_reporting(E_ALL); 

// Select a product record from the table
$query = "SELECT * FROM tbl_prod_info WHERE Product_ID = ?"; // Using '?' placeholder
$stmt = $pdo->prepare($query);

// Bind the product ID from the URL parameter
$stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT); // Explicitly set the parameter type (PDO::PARAM_INT)
$stmt->execute();

// Fetch the product details
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if product exists
if (!$row) {
    echo "Product not found.";
    exit();
}

// Assign product details to variables
$pid = $row["Product_ID"];
$pname = $row["Product_Name"];
$description = $row["Description"];
$category = $row["Category"];
$sizes = $row["Sizes"];
$color = $row["Color"];
$stock = $row["Stock"];
$assemblyRequired = $row["Assembly_Required"];
$imageURL = $row["ImageURL"];
$price = $row["Price"];
$sold = isset($row["Sold"]) ? $row["Sold"] : ''; // Use isset to avoid undefined index
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

        <br><br> <br><br>
        <div class="container_boxes">
        <form name="frmProductRec" method="POST" action="delete-product-rec.php?id=<?php echo htmlspecialchars($pid); ?>">
                <h2>Delete Product Record</h2>
                <table>
                    <tr>
                        <td>Product ID:</td>
                        <td><?php echo htmlspecialchars($pid); ?></td>
                    </tr>
                    <tr>
                        <td>Product Name:</td>
                        <td><?php echo htmlspecialchars($pname); ?></td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td><?php echo htmlspecialchars($description); ?></td>
                    </tr>
                    <tr>
                        <td>Category:</td>
                        <td><?php echo htmlspecialchars($category); ?></td>
                    </tr>
                    <tr>
                        <td>Sizes:</td>
                        <td><?php echo htmlspecialchars($sizes); ?></td>
                    </tr>
                    <tr>
                        <td>Color:</td>
                        <td><?php echo htmlspecialchars($color); ?></td>
                    </tr>
                    <tr>
                        <td>Stock:</td>
                        <td><?php echo htmlspecialchars($stock); ?></td>
                    </tr>
                    <tr>
                        <td>Assembly Required:</td>
                        <td><?php echo htmlspecialchars($assemblyRequired); ?></td>
                    </tr>
                    <tr>
                        <td>Image URL:</td>
                        <td><?php echo htmlspecialchars($imageURL); ?></td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td><?php echo htmlspecialchars($price); ?></td>
                    </tr>
                    <tr>
                        <td>Sold:</td> <!-- Added Sold field -->
                        <td><?php echo htmlspecialchars($sold); ?></td>
                    </tr>
                </table>

                <!-- Buttons -->
                <div class="button-container">
                    <a href="read-all-product-form.php" class="buttonBack">Back to List</a>
                    <button type="submit" class="buttonDelete">Delete Record</button>
                </div>
            </form>
        </div>

    <script src="../static/js/bootstrap.min.js" crossorigin="anonymous"></script>
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
</body>
</html>
