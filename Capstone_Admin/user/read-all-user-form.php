<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if admin is logged in
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

// Fetch user records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        User_ID,
        Last_Name,
        First_Name,
        Mobile_Number,
        Status
    FROM tbl_user_info
    WHERE First_Name LIKE :search 
    OR Last_Name LIKE :search 
    OR User_ID LIKE :search
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
            <td>' . htmlspecialchars($row["User_ID"]) . '</td>
            <td>' . htmlspecialchars($row["Last_Name"]) . '</td>
            <td>' . htmlspecialchars($row["First_Name"]) . '</td>
            <td>' . htmlspecialchars($row["Mobile_Number"]) . '</td>
            <td>' . htmlspecialchars($row["Status"]) . '</td>
            <td><a class="buttonView" href="read-one-user-form.php?id=' . htmlspecialchars($row["User_ID"]) . '" target="_parent">View</a></td>
            <td><a class="buttonEdit" href="update-user-form.php?id=' . htmlspecialchars($row["User_ID"]) . '" target="_parent">Edit</a></td>
            <td><a class="buttonDelete" href="delete-user-form.php?id=' . htmlspecialchars($row["User_ID"]) . '" target="_parent">Delete</a></td>
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
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <span class="logo_name">
                <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE" class="logo_name">
            </span>
        </div>
        <ul class="nav-links">
            <li>
                <a href="../dashboard/dashboard.php">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../purchase-history/read-all-history-form.php">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
                <a href="../reviews/read-all-reviews-form.php">
                    <i class="bx bx-message-dots"></i>
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
                </form>
            </div>
            <div class="profile-details" onclick="toggleDropdown()">
                <img src="../<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>
                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>
        </nav>
        <br><br><br>

        <div class="container_boxes">
            <h4>USER LIST<a href="create-user-form.php">Create New User</a></h4>
            <div class="button-container">
                <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
            </div>
            <div id="user-list">
                <table width="100%" border="1" cellspacing="5">
                    <thead>
                        <tr>
                            <th>USER ID</th>
                            <th>LAST NAME</th>
                            <th>FIRST NAME</th>
                            <th>MOBILE NUMBER</th>
                            <th>STATUS</th>
                            <th colspan="3">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["User_ID"]) ?></td>
                                <td><?= htmlspecialchars($row["Last_Name"]) ?></td>
                                <td><?= htmlspecialchars($row["First_Name"]) ?></td>
                                <td><?= htmlspecialchars($row["Mobile_Number"]) ?></td>
                                <td><?= htmlspecialchars($row["Status"]) ?></td>
                                <td><a class="buttonView" href="read-one-user-form.php?id=<?= htmlspecialchars($row["User_ID"]) ?>" target="_parent">View</a></td>
                                <td><a class="buttonEdit" href="update-user-form.php?id=<?= htmlspecialchars($row["User_ID"]) ?>" target="_parent">Edit</a></td>
                                <td><a class="buttonDelete" href="delete-user-form.php?id=<?= htmlspecialchars($row["User_ID"]) ?>" target="_parent">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
            const searchValue = this.value.trim();
            const url = searchValue ? `read-all-user-form.php?search=${encodeURIComponent(searchValue)}` : `read-all-user-form.php?search=`;

            fetch(url)
                .then(response => response.text())
                .then(data => {
                    const tableBody = document.querySelector('#user-list table tbody');
                    tableBody.innerHTML = data.trim(); // Replace the table body content with the fetched rows
                })
                .catch(error => console.error('Error fetching search results:', error));
        });
    </script>
</body>
</html>
