<?php
session_start(); // Start the session

// Include the database connection
include_once '../config/database.php'; 

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
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <!-- Modified link here -->
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

        <br><br><br>

        <?php
        // Disable error reporting (consider commenting this out during development)
        error_reporting(0);
        
        // Ensure 'id' is present and is a valid integer
        if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
            echo "Invalid Product ID.";
            exit();
        }

        // Query to select the specific product record based on Product_ID
        $query = "SELECT * FROM tbl_prod_info WHERE Product_ID = ?";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the product data
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if product exists
        if (!$row) {
            echo "No product found.";
            exit();
        }

        // Store product details in variables
        $productID = htmlspecialchars($row["Product_ID"]);
        $productName = htmlspecialchars($row["Product_Name"]);
        $description = htmlspecialchars($row["Description"]);
        $category = htmlspecialchars($row["Category"]);
        $sizes = htmlspecialchars($row["Sizes"]);
        $color = htmlspecialchars($row["Color"]);
        $stock = htmlspecialchars($row["Stock"]);
        $sold = htmlspecialchars($row["Sold"]); // Added Sold field
        $assemblyRequired = htmlspecialchars($row["Assembly_Required"]);
        $price = htmlspecialchars($row["Price"]);
        $imageURLs = explode(',', htmlspecialchars($row["ImageURL"]));
        $glbFileURL = htmlspecialchars($row["GLB_File_URL"]);
        ?>

        <div class="container_boxes">
            <form name="frmProdRec" method="POST" action="">
                <h2>View Product Record</h2>
                <table>
                    <tr><td>Product ID:</td><td><?php echo $productID; ?></td></tr>
                    <tr><td>Product Name:</td><td><?php echo $productName; ?></td></tr>
                    <tr><td>Description:</td><td><?php echo $description; ?></td></tr>
                    <tr><td>Category:</td><td><?php echo $category; ?></td></tr>
                    <tr><td>Sizes:</td><td><?php echo $sizes; ?></td></tr>
                    <tr><td>Color:</td><td><?php echo $color; ?></td></tr>
                    <tr><td>Stock:</td><td><?php echo $stock; ?></td></tr>
                    <tr><td>Sold:</td><td><?php echo $sold; ?></td></tr> <!-- Display Sold value -->
                    <tr><td>Assembly Required:</td><td><?php echo $assemblyRequired; ?></td></tr>
                    <tr><td>Price:</td><td><?php echo $price; ?></td></tr>
                    <tr><td>Images:</td>
                        <td>
                            <?php foreach ($imageURLs as $imageURL): ?>
                                <img src="<?php echo $imageURL; ?>" alt="Product Image" style="width:100px;height:auto;">
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr><td>3D Model:</td>
                        <td>
                            <?php if ($glbFileURL): ?>
                                <model-viewer src="<?php echo $glbFileURL; ?>" alt="3D Model" auto-rotate camera-controls style="width: 300px; height: 300px;"></model-viewer>
                            <?php else: ?>
                                No 3D model available.
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <!-- Action buttons -->
                <div class="button-container">
                    <br>
                    <a href="read-all-product-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="update-product-form.php?id=<?php echo $productID; ?>" target="_parent" class="buttonEdit">Edit</a>
                </div>
            </form>
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
    </script>
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</body>
</html>
