<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if the admin's ID is stored in the session after login
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

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Fetch reviews with user names and product names
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        r.Review_ID,
        r.Rating,
        r.Review_Text,
        r.Review_Date,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Product_Name
    FROM tbl_reviews r
    JOIN tbl_user_info u ON r.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info p ON r.Product_ID = p.Product_ID
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR r.Review_Text LIKE :search
    OR p.Product_Name LIKE :search
    ORDER BY r.Review_Date DESC
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX requests to return only table rows
if (isset($_GET['search'])) {
    foreach ($rows as $row) {
        echo '
        <tr>
            <td>' . htmlspecialchars($row['User_Name']) . '</td>
            <td>' . htmlspecialchars($row['Product_Name'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($row['Rating']) . '</td>
            <td>' . htmlspecialchars($row['Review_Text']) . '</td>
            <td>' . htmlspecialchars($row['Review_Date']) . '</td>
            <td style="text-align: center;">
                <a class="buttonView" href="read-one-review-form.php?id=' . htmlspecialchars($row['Review_ID']) . '" target="_parent">View</a>
            </td>
        </tr>';
    }
    exit; // Stop further execution for AJAX requests
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
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" />
                    <button type="submit"><i class="bx bx-search"></i></button>
                </form>
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
        
            <h4>Reviews List <a href="../reviews/create-review-form.php" class="buttonUpdate">Create Review</a></h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <div id="reviews-list">
                    <table width="100%" border="1" cellspacing="5">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Review Date</th>
                                <th colspan="3" style="text-align: center;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['User_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Product_Name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Rating']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Review_Text']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Review_Date']); ?></td>
                                    <td style="text-align: center;">
                                        <a class="buttonView" href="read-one-review-form.php?id=<?php echo htmlspecialchars($row['Review_ID']); ?>" target="_parent">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
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

    document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
        const searchValue = this.value.trim();
        const url = searchValue ? `read-all-reviews-form.php?search=${encodeURIComponent(searchValue)}` : `read-all-reviews-form.php?search=`;

        fetch(url)
            .then(response => response.text())
            .then(data => {
                const tableBody = document.querySelector('#reviews-list table tbody');
                tableBody.innerHTML = data.trim(); // Replace the table body content with the fetched rows
            })
            .catch(error => console.error('Error fetching search results:', error));
    });
</script>
</body>
</html>
