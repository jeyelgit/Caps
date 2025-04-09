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

        <h2>Create New Product</h2>
        <form name="frmProduct" method="POST" enctype="multipart/form-data" action="create-product-rec.php">
            <table>
                <!-- Product Name -->
                <tr>
                    <td><label for="productName">Product Name:</label></td>
                    <td><input type="text" name="Product_Name" id="productName" required></td>
                </tr>

                <!-- Description -->
                <tr>
                    <td><label for="description">Description:</label></td>
                    <td><textarea name="Description" id="description"></textarea></td>
                </tr>

                <!-- Furniture Type -->
                <tr>
                    <td>Furniture Type:</td>
                    <td>
                        <select id="furniture" name="Category" onchange="updateSizes()">
                            <option value="" disabled selected>Select one</option>
                            <option value="chair">Chair</option>
                            <option value="table">Table</option>
                            <option value="salaset">Sala Set</option>
                            <option value="bedframe">Bed Frame</option>
                            <option value="sofa">Sofa</option>
                        </select>
                    </td>
                </tr>

                <!-- Standard Sizes -->
                <tr>
                    <td>Standard Sizes:</td>
                    <td>
                        <select id="sizes" name="Sizes">
                            <option value="" disabled selected>Select one</option>
                        </select>
                        <input type="hidden" id="sizesText" name="SizesText">
                    </td>
                </tr>

                <!-- Color -->
                <tr>
                    <td><label for="color">Color:</label></td>
                    <td><input type="text" name="Color" id="color"></td>
                </tr>

                <!-- Stock -->
                <tr>
                    <td><label for="stock">Stock:</label></td>
                    <td><input type="text" name="Stock" id="stock"></td>
                </tr>

                <!-- Assembly Required -->
                <tr>
                    <td><label for="assemblyRequired">Assembly Required:</label></td>
                    <td>
                        <select name="Assembly_Required" id="assemblyRequired">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </td>
                </tr>

                <!-- Price -->
                <tr>
                    <td><label for="price">Price:</label></td>
                    <td><input type="text" name="Price" id="price" required></td>
                </tr>

                <!-- Sold (Optional Field) -->
                <tr>
                    <td><label for="sold">Sold:</label></td>
                    <td><input type="text" name="Sold" id="sold"></td>
                </tr>

                <!-- Image Upload (3 Images) -->
                <tr>
                    <td><label for="fileImage">Upload Product Images (You can upload 3 images):</label></td>
                    <td>
                        <input type="file" name="ImageURLs[]" id="fileImage" accept="image/*" multiple>
                    </td>
                </tr>

                <!-- GLB File Upload -->
                <tr>
                    <td><label for="fileGLB">Upload 3D Model (.glb):</label></td>
                    <td>
                        <input type="file" name="GLB_File_URL" id="fileGLB" accept=".glb">
                    </td>
                </tr>

                <!-- Submit and Reset Buttons -->
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Submit">
                        <input type="reset" value="Reset">
                        <a href="read-all-product-form.php" target="_parent">Back to List</a>
                    </td>
                </tr>
            </table>
        </form>

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
        function updateSizes() {
            const furniture = document.getElementById('furniture').value;
            const sizes = document.getElementById('sizes');

            const options = {
                chair: [
                    { value: 'chair-stan', text: 'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.' }
                ],
                table: [
                    { value: 'table1', text: 'Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.' },
                    { value: 'table2', text: 'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.' },
                    { value: 'table3', text: 'Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.' }
                ],
                salaset: [
                    { value: 'salaset1', text: 'Sala Set 8x8 ft.' },
                    { value: 'salaset2', text: 'Sala Set 9x9 ft.' },
                    { value: 'salaset3', text: 'Sala Set 10x10 ft.' },
                    { value: 'salaset4', text: 'Sala Set 10x11 ft.' }
                ],
                bedframe: [
                    { value: 'bedframe1', text: 'Bed Frame - California King  72x84 in.' },
                    { value: 'bedframe2', text: 'Bed Frame -  King  76x80 in.' },
                    { value: 'bedframe3', text: 'Bed Frame - Queen  60x80 in.' },
                    { value: 'bedframe4', text: 'Bed Frame - Full XL  54x80 in.' },
                    { value: 'bedframe5', text: 'Bed Frame - Full   54x75 in.' },
                    { value: 'bedframe6', text: 'Bed Frame - Twin XL   38x80 in.' },
                    { value: 'bedframe7', text: 'Bed Frame - Twin   38x75 in.' }
                ],
                 sofa: [
                    { value: 'sofa1', text: 'Sofa 3 seater - L: 7 ft // W: 3 ft // H: 3.5 ft' },
                    { value: 'sofa2', text: 'Sofa 2 seater - L: 5 ft // W: 3 ft // H: 3.5 ft' },
                    { value: 'sofa3', text: 'Sofa 1 seater - L: 3 ft // W: 3 ft // H: 3.5 ft' },
                    { value: 'sofa4', text: 'L Shape Sofa 6-7 seater  - L: 9 ft // W: 3 ft // H: 3.5 ft' }
                ]
            };

            // Clear existing options
            sizes.innerHTML = '<option value="" disabled selected>Select one</option>';

            // Add new options based on selected furniture
            if (options[furniture]) {
                options[furniture].forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    sizes.appendChild(opt);
                });
            }
        }

        document.getElementById('sizes').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            document.getElementById('sizesText').value = selectedOption.text;
        });
    </script>
</body>

</html>
