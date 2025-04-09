<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php';

// Redirect to login page if not logged in
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

// Fetch user record
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Create the query to select user record
    $query = "SELECT * FROM tbl_user_info WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    // Fetch user record
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "User not found.";
        exit();
    }

    // Store user data in variables
    $userID = htmlspecialchars($user["User_ID"]);
    $lname = htmlspecialchars($user["Last_Name"]);
    $fname = htmlspecialchars($user["First_Name"]);
    $mname = htmlspecialchars($user["Middle_Name"]);
    $homeAddress = htmlspecialchars($user["Home_Address"]);
    $email = htmlspecialchars($user["Email_Address"]);
    $mobile = htmlspecialchars($user["Mobile_Number"]);
    $status = htmlspecialchars($user["Status"]);
    $picPath = htmlspecialchars($user["PicPath"]);
} else {
    echo "No user ID provided.";
    exit();
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
            <form name="frmUserRec" method="POST" action="update-user-rec.php" enctype="multipart/form-data" onsubmit="return validateForm()">
                <h4>UPDATE USER RECORD</h4>
                <table>
                    <tr>
                        <td>User ID:</td>
                        <!-- Changed to make it editable -->
                        <td><input type="text" name="txtID" value="<?php echo $userID; ?>"></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><input type="text" name="txtLName" value="<?php echo $lname; ?>"></td>
                    </tr>
                    <tr>
                        <td>First Name:</td>
                        <td><input type="text" name="txtFName" value="<?php echo $fname; ?>"></td>
                    </tr>
                    <tr>
                        <td>Middle Name:</td>
                        <td><input type="text" name="txtMName" value="<?php echo $mname; ?>"></td>
                    </tr>
                    <tr>
                        <td>Home Address:</td>
                        <td><input type="text" name="txtHomeAddress" value="<?php echo $homeAddress; ?>"></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                        <td><input type="email" name="txtEmail" value="<?php echo $email; ?>"></td>
                    </tr>
                    <tr>
                        <td>Mobile Number:</td>
                        <td><input type="text" name="txtMobile" value="<?php echo $mobile; ?>"></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            <select name="txtStatus" class="form-select">
                                <option value="Active" <?php if ($status === 'Active') echo 'selected'; ?>>Active</option>
                                <option value="Inactive" <?php if ($status === 'Inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="txtPass" placeholder="Leave blank to keep current password"></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input type="password" name="txtConfirm" placeholder="Leave blank to keep current password"></td>
                    </tr>
                    <tr>
                        <td>Current Profile Picture:</td>
                        <td><img src="<?php echo $picPath; ?>" alt="User Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;"></td>
                    </tr>
                    <tr>
                        <td>Upload New Profile Picture:</td>
                        <td><input type="file" name="PicPath" accept="image/*"></td>
                    </tr>
                    
                </table>
                <!-- Separated buttons -->
                <div class="button-container">
                <a href="read-all-user-form.php" target="_parent" class="buttonBack">Back to List</a>
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
