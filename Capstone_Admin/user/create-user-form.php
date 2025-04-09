<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
            <h4>CREATE NEW USER</h4>

            <!-- SETUP FORM METHOD POST AND CALL THE PHP SCRIPT TO CREATE THE RECORD -->
            <form name="frmUser" method="POST" enctype="multipart/form-data" action="create-user-rec.php" onsubmit="return validateForm()">
                <table>
                    <tr>
                        <td>User ID:</td>
                        <td><input type="text" name="txtUserID" required></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><input type="text" name="txtLName" required></td>
                    </tr>
                    <tr>
                        <td>First Name:</td>
                        <td><input type="text" name="txtFName" required></td>
                    </tr>
                    <tr>
                        <td>Middle Name:</td>
                        <td><input type="text" name="txtMName"></td>
                    </tr>
                    <tr>
                        <td>Home Address:</td>
                        <td><input type="text" name="txtAddress" required></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                        <td><input type="email" name="txtEmail" required></td>
                    </tr>
                    <tr>
                        <td>Mobile Number:</td>
                        <td>
                            <input id="phone" type="text" name="txtMobile" required placeholder="Enter 11 digit number" />
                        </td>
                    </tr>

                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="txtPass" required></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input type="password" name="txtConfirm" required></td>
                    </tr>
                    <tr>
                        <td>Upload Picture:</td>
                        <td><input type="file" name="filePic"></td>
                    </tr>
                    <tr>
                        <td>reCAPTCHA:</td>
                        <td>
                            <div class="recaptcha-container">
                                <div class="g-recaptcha" data-sitekey="6LdGk9wqAAAAAPGLtqpTt5f2IdBdSFGjA806AF7X"></div>
                            </div>
                        </td>
                    </tr>
                </table>
                <!-- Separated buttons -->
                <div class="button-container">
                    <input type="submit" value="Submit" class="buttonUpdate">
                    <input type="reset" value="Reset" class="buttonDelete">
                    <a href="read-all-user-form.php" target="_parent" class="buttonBack">Back to List</a>
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
        window.onload = function () {
            document.getElementById("phone").focus();
        }
        function validateForm() {
            let email = document.forms["frmUser"]["txtEmail"].value;
            let mobile = document.forms["frmUser"]["txtMobile"].value;
            let homeAddress = document.forms["frmUser"]["txtAddress"].value;

            // Email Validation (Basic Pattern)
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert("Invalid email format!");
                return false;
            }

            // Mobile Number Validation (11 digits)
            if (!/^\d{11}$/.test(mobile)) {
                alert("Mobile number must be 11 digits!");
                return false;
            }
            
            // Home Address Validation(simple checks if not empty)
             if(homeAddress.trim() === ""){
                alert("Home Address must not be empty");
                return false;
            }
            // Password confirmation
            var pass = document.forms["frmUser"]["txtPass"].value;
            var confirmPass = document.forms["frmUser"]["txtConfirm"].value;
            if (pass !== confirmPass) {
                alert("Passwords do not match!");
                return false; // Prevent form submission
            }

            return true; // Allow form submission if everything is valid
        }
    </script>
</body>
</html>
