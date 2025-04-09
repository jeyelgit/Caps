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
 
        <br><br><br><br>

        <?php
        // DISABLE ERROR DETECTION
        error_reporting(0);

        // CREATE THE QUERY TO SELECT RECORD FROM tbl_customizations TABLE WHERE Customization_ID MATCHES
        if (isset($_GET['id'])) {
            $query = "SELECT * FROM tbl_customizations WHERE Customization_ID = ?";

            // PREPARE QUERY AND STORE TO A STATEMENT VARIABLE
            $stmt = $pdo->prepare($query);

            // BIND VALUE TO DATABASE PARAMETER (Ensuring that 'id' is referenced correctly)
            $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);

            // EXECUTE STATEMENT
            $stmt->execute();

            // GET RECORD PER ROW
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// CHECK IF RECORD EXISTS
if ($row) {
    // STORE DATA RECORDS TO VARIABLES
    $customizationID = $row["Customization_ID"];
    $userID = $row["User_ID"];
    $furnitureType = $row["Furniture_Type"];
    $furnitureTypeAdditionalInfo = $row["Furniture_Type_Additional_Info"];
    $standardSize = $row["Standard_Size"];
    $desiredSize = $row["Desired_Size"];
    $color = $row["Color"];
    $colorImageURL = $row["Color_Image_URL"];
    $colorAdditionalInfo = $row["Color_Additional_Info"];
    $texture = $row["Texture"];
    $textureImageURL = $row["Texture_Image_URL"];
    $textureAdditionalInfo = $row["Texture_Additional_Info"];
    $woodType = $row["Wood_Type"];
    $woodImageURL = $row["Wood_Image_URL"];
    $woodAdditionalInfo = $row["Wood_Additional_Info"];
    $foamType = $row["Foam_Type"];
    $foamImageURL = $row["Foam_Image_URL"];
    $foamAdditionalInfo = $row["Foam_Additional_Info"];
    $coverType = $row["Cover_Type"];
    $coverImageURL = $row["Cover_Image_URL"];
    $coverAdditionalInfo = $row["Cover_Additional_Info"];
    $design = $row["Design"];
    $designImageURL = $row["Design_Image_URL"];
    $designAdditionalInfo = $row["Design_Additional_Info"];
    $tileType = $row["Tile_Type"];
    $tileImageURL = $row["Tile_Image_URL"];
    $tileAdditionalInfo = $row["Tile_Additional_Info"];
    $metalType = $row["Metal_Type"];
    $metalImageURL = $row["Metal_Image_URL"];
    $metalAdditionalInfo = $row["Metal_Additional_Info"];
} else {
    // Redirect back if no record is found
    header("Location: read-all-custom-form.php");
    exit();
}

        } else {
            // Redirect back if 'id' is not set
            header("Location: read-all-custom-form.php");
            exit();
        }
        ?>

        <div class="container_boxes">
            <form name="frmCustomRec" method="POST" action="delete-custom-rec.php">
                <h2>Delete Customization Record</h2>
                <table>
                    <?php
                    echo '
                    <tr>
                        <td>Customization ID:</td>
                        <td>' . htmlspecialchars($customizationID) . '</td>
                    </tr>
                    <tr>
                        <td>User ID:</td>
                        <td>' . htmlspecialchars($userID) . '</td>
                    </tr>
                    <tr>
                        <td>Furniture Type:</td>
                        <td>' . htmlspecialchars($furnitureType) . '</td>
                    </tr>
                    <tr>
                        <td>Additional Info:</td>
                        <td>' . htmlspecialchars($furnitureTypeAdditionalInfo) . '</td>
                    </tr>
                    <tr>
                        <td>Standard Size:</td>
                        <td>' . htmlspecialchars($standardSize) . '</td>
                    </tr>
                    <tr>
                        <td>Desired Size:</td>
                        <td>' . htmlspecialchars($desiredSize) . '</td>
                    </tr>
                    <tr>
                        <td>Color:</td>
                        <td>' . htmlspecialchars($color) . '</td>
                    </tr>';
                    ?>
                </table>

                <!-- Separated buttons -->
                <div class="button-container">
                    <a href="read-all-custom-form.php" target="_parent" class="buttonBack">Back to List</a>
                    <a href="delete-custom-rec.php?id=<?php echo htmlspecialchars($customizationID); ?>" target="_parent" class="buttonDelete">Delete Record</a>
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
