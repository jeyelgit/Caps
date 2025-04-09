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

// Fetch admin data for profile display from the database
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

// Disable error reporting for production
error_reporting(0);

// Fetch the customization record
$customizationID = $_GET['id']; // Get the Customization_ID from the URL
$query = "SELECT * FROM tbl_customizations WHERE Customization_ID = ?";
$stmt = $pdo->prepare($query);
$stmt->bindValue(1, $customizationID);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if record is found
if (!$row) {
    echo "Customization not found.";
    exit();
}

// Store values to variables
$userID = htmlspecialchars($row['User_ID']);
$furnitureType = htmlspecialchars($row['Furniture_Type']);
$furnitureTypeAdditionalInfo = htmlspecialchars($row['Furniture_Type_Additional_Info']);
$standardSize = htmlspecialchars($row['Standard_Size']);
$desiredSize = htmlspecialchars($row['Desired_Size']);
$color = htmlspecialchars($row['Color']);
$colorImageURL = htmlspecialchars($row['Color_Image_URL']);
$colorAdditionalInfo = htmlspecialchars($row['Color_Additional_Info']);
$texture = htmlspecialchars($row['Texture']);
$textureImageURL = htmlspecialchars($row['Texture_Image_URL']);
$textureAdditionalInfo = htmlspecialchars($row['Texture_Additional_Info']);
$woodType = htmlspecialchars($row['Wood_Type']);
$woodImageURL = htmlspecialchars($row['Wood_Image_URL']);
$woodAdditionalInfo = htmlspecialchars($row['Wood_Additional_Info']);
$foamType = htmlspecialchars($row['Foam_Type']);
$foamImageURL = htmlspecialchars($row['Foam_Image_URL']);
$foamAdditionalInfo = htmlspecialchars($row['Foam_Additional_Info']);
$coverType = htmlspecialchars($row['Cover_Type']);
$coverImageURL = htmlspecialchars($row['Cover_Image_URL']);
$coverAdditionalInfo = htmlspecialchars($row['Cover_Additional_Info']);
$design = htmlspecialchars($row['Design']);
$designImageURL = htmlspecialchars($row['Design_Image_URL']);
$designAdditionalInfo = htmlspecialchars($row['Design_Additional_Info']);
$tileType = htmlspecialchars($row['Tile_Type']);
$tileImageURL = htmlspecialchars($row['Tile_Image_URL']);
$tileAdditionalInfo = htmlspecialchars($row['Tile_Additional_Info']);
$metalType = htmlspecialchars($row['Metal_Type']);
$metalImageURL = htmlspecialchars($row['Metal_Image_URL']);
$metalAdditionalInfo = htmlspecialchars($row['Metal_Additional_Info']);
$orderStatus = htmlspecialchars($row['Order_Status']); // Corrected: Fetch Order_Status
$productStatus = htmlspecialchars($row['Product_Status']);

// Order Status Map
$orderStatusLabels = [
    0   => 'Order Received',
    10  => 'Order Confirmed',
    20  => 'Design Finalization',
    30  => 'Material Preparation',
    40  => 'Production Started',
    50  => 'Mid-Production',
    60  => 'Finishing Process',
    70  => 'Quality Check',
    80  => 'Final Assembly',
    90  => 'Ready for Delivery',
    100 => 'Delivered / Completed'
];

// Product Status Map
$productStatusLabels = [
    0   => 'Concept Stage',
    10  => 'Design Approved',
    20  => 'Material Sourcing',
    30  => 'Cutting & Shaping',
    40  => 'Structural Assembly',
    50  => 'Detailing & Refinements',
    60  => 'Sanding & Pre-Finishing',
    70  => 'Final Coating',
    80  => 'Assembly & Testing',
    90  => 'Ready for Sale',
    100 => 'Sold / Installed'
];
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

        <div class="container_boxes">
        <form name="frmCustomizationRec" method="POST" action="update-custom-rec.php" enctype="multipart/form-data">
            <h4>UPDATE CUSTOMIZATION RECORD</h4>
            <table>
                <!-- Customization ID (readonly) -->
                <tr>
                    <td>Customization ID:</td>
                    <td><input type="text" name="txtCustomizationID" value="<?php echo $customizationID; ?>" readonly></td>
                </tr>

                <!-- User ID (readonly) -->
                <tr>
                    <td>User ID:</td>
                    <td><input type="text" name="txtUserID" value="<?php echo $userID; ?>" readonly></td>
                </tr>

                <!-- Furniture Type -->
                <tr>
                    <td>Furniture Type:</td>
                    <td><input type="text" name="txtFurnitureType" value="<?php echo $furnitureType; ?>"></td>
                </tr>

                <!-- Furniture Type Additional Info -->
                <tr>
                    <td>Furniture Type Additional Info:</td>
                    <td><textarea name="txtFurnitureTypeAdditionalInfo"><?php echo $furnitureTypeAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Standard Size -->
                <tr>
                    <td>Standard Size:</td>
                    <td><input type="text" name="txtStandardSize" value="<?php echo $standardSize; ?>"></td>
                </tr>

                <!-- Desired Size -->
                <tr>
                    <td>Desired Size:</td>
                    <td><input type="text" name="txtDesiredSize" value="<?php echo $desiredSize; ?>"></td>
                </tr>

                <!-- Color -->
                <tr>
                    <td>Color:</td>
                    <td><input type="text" name="txtColor" value="<?php echo $color; ?>"></td>
                </tr>

                <!-- Color Image -->
                <tr>
                    <td>Color Image:</td>
                    <td>
                        <input type="file" name="colorImage" accept="image/*">
                        <?php if (!empty($colorImageURL)): ?>
                            <img src="<?php echo $colorImageURL; ?>" alt="Color Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Color Additional Info -->
                <tr>
                    <td>Color Additional Info:</td>
                    <td><textarea name="txtColorAdditionalInfo"><?php echo $colorAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Texture -->
                <tr>
                    <td>Texture:</td>
                    <td><input type="text" name="txtTexture" value="<?php echo $texture; ?>"></td>
                </tr>

                <!-- Texture Image -->
                <tr>
                    <td>Texture Image:</td>
                    <td>
                        <input type="file" name="textureImage" accept="image/*">
                        <?php if (!empty($textureImageURL)): ?>
                            <img src="<?php echo $textureImageURL; ?>" alt="Texture Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Texture Additional Info -->
                <tr>
                    <td>Texture Additional Info:</td>
                    <td><textarea name="txtTextureAdditionalInfo"><?php echo $textureAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Wood Type -->
                <tr>
                    <td>Wood Type:</td>
                    <td><input type="text" name="txtWoodType" value="<?php echo $woodType; ?>"></td>
                </tr>

                <!-- Wood Image -->
                <tr>
                    <td>Wood Image:</td>
                    <td>
                        <input type="file" name="woodImage" accept="image/*">
                        <?php if (!empty($woodImageURL)): ?>
                            <img src="<?php echo $woodImageURL; ?>" alt="Wood Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Wood Additional Info -->
                <tr>
                    <td>Wood Additional Info:</td>
                    <td><textarea name="txtWoodAdditionalInfo"><?php echo $woodAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Foam Type -->
                <tr>
                    <td>Foam Type:</td>
                    <td><input type="text" name="txtFoamType" value="<?php echo $foamType; ?>"></td>
                </tr>

                <!-- Foam Image -->
                <tr>
                    <td>Foam Image:</td>
                    <td>
                        <input type="file" name="foamImage" accept="image/*">
                        <?php if (!empty($foamImageURL)): ?>
                            <img src="<?php echo $foamImageURL; ?>" alt="Foam Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Foam Additional Info -->
                <tr>
                    <td>Foam Additional Info:</td>
                    <td><textarea name="txtFoamAdditionalInfo"><?php echo $foamAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Cover Type -->
                <tr>
                    <td>Cover Type:</td>
                    <td><input type="text" name="txtCoverType" value="<?php echo $coverType; ?>"></td>
                </tr>

                <!-- Cover Image -->
                <tr>
                    <td>Cover Image:</td>
                    <td>
                        <input type="file" name="coverImage" accept="image/*">
                        <?php if (!empty($coverImageURL)): ?>
                            <img src="<?php echo $coverImageURL; ?>" alt="Cover Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Cover Additional Info -->
                <tr>
                    <td>Cover Additional Info:</td>
                    <td><textarea name="txtCoverAdditionalInfo"><?php echo $coverAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Design -->
                <tr>
                    <td>Design:</td>
                    <td><input type="text" name="txtDesign" value="<?php echo $design; ?>"></td>
                </tr>

                <!-- Design Image -->
                <tr>
                    <td>Design Image:</td>
                    <td>
                        <input type="file" name="designImage" accept="image/*">
                        <?php if (!empty($designImageURL)): ?>
                            <img src="<?php echo $designImageURL; ?>" alt="Design Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Design Additional Info -->
                <tr>
                    <td>Design Additional Info:</td>
                    <td><textarea name="txtDesignAdditionalInfo"><?php echo $designAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Tile Type -->
                <tr>
                    <td>Tile Type:</td>
                    <td><input type="text" name="txtTileType" value="<?php echo $tileType; ?>"></td>
                </tr>

                <!-- Tile Image -->
                <tr>
                    <td>Tile Image:</td>
                    <td>
                        <input type="file" name="tileImage" accept="image/*">
                        <?php if (!empty($tileImageURL)): ?>
                            <img src="<?php echo $tileImageURL; ?>" alt="Tile Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Tile Additional Info -->
                <tr>
                    <td>Tile Additional Info:</td>
                    <td><textarea name="txtTileAdditionalInfo"><?php echo $tileAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Metal Type -->
                <tr>
                    <td>Metal Type:</td>
                    <td><input type="text" name="txtMetalType" value="<?php echo $metalType; ?>"></td>
                </tr>

                <!-- Metal Image -->
                <tr>
                    <td>Metal Image:</td>
                    <td>
                        <input type="file" name="metalImage" accept="image/*">
                        <?php if (!empty($metalImageURL)): ?>
                            <img src="<?php echo $metalImageURL; ?>" alt="Metal Image" width="100">
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Metal Additional Info -->
                <tr>
                    <td>Metal Additional Info:</td>
                    <td><textarea name="txtMetalAdditionalInfo"><?php echo $metalAdditionalInfo; ?></textarea></td>
                </tr>

                <!-- Order Status -->
                <tr>
                    <td>Order Status:</td>
                    <td>
                        <select name="txtStatus">
                            <?php
                            foreach ($orderStatusLabels as $key => $label) {
                                $selected = ($key == $orderStatus) ? 'selected' : '';
                                echo "<option value=\"$key\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <!-- Product Status -->
                <tr>
                    <td>Product Status:</td>
                    <td>
                        <select name="txtProductStatus">
                            <?php
                            foreach ($productStatusLabels as $key => $label) {
                                $selected = ($key == $productStatus) ? 'selected' : '';
                                echo "<option value=\"$key\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>

            <div class="button-container">
            <a href="read-all-custom-form.php" target="_parent" class="buttonBack">Back to List</a>
                <input type="submit" value="Update" class="buttonUpdate">
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
            } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
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
