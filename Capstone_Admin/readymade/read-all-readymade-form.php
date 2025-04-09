<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Check if the admin's ID is stored in the session after login
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

// Fetch ready-made order records from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT 
        rmo.ReadyMadeOrder_ID,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        p.Product_Name,
        rmo.Quantity,
        rmo.Total_Price,
        rmo.Order_Status,
        rmo.Order_Date
    FROM tbl_ready_made_orders rmo
    JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
    JOIN tbl_prod_info p ON rmo.Product_ID = p.Product_ID
    WHERE u.First_Name LIKE :search 
    OR u.Last_Name LIKE :search 
    OR p.Product_Name LIKE :search
    OR rmo.Order_Status LIKE :search
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Order Status mapping
$statusLabels = [
    0 => 'Pending',
    10 => 'Order Placed',
    20 => 'Payment Processing',
    30 => 'Order Confirmed',
    40 => 'Preparing for Shipment',
    50 => 'Shipped',
    60 => 'Out for Delivery',
    70 => 'Delivered',
    80 => 'Installed',
    100 => 'Complete'
];

if (isset($_GET['search'])) {
    echo '<table width="100%" border="1" cellspacing="5">
        <thead>
            <tr>
                <th>USER NAME</th>
                <th>PRODUCT NAME</th>
                <th>TOTAL PRICE</th>
                <th>ORDER STATUS</th>
                <th colspan="3" style="text-align: center;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($rows as $row) {
        $orderID = htmlspecialchars($row["ReadyMadeOrder_ID"]);
        $userName = htmlspecialchars($row["User_Name"]);
        $productName = htmlspecialchars($row["Product_Name"]);
        $totalPrice = number_format((float)$row["Total_Price"], 2, '.', '');
        $orderStatusValue = (int)$row["Order_Status"];
        $progressPercent = min(max($orderStatusValue, 0), 100);
        $orderStatus = htmlspecialchars($statusLabels[$orderStatusValue] ?? 'Unknown');

        echo '
        <tr>
            <td>' . $userName . '</td>
            <td>' . $productName . '</td>
            <td>' . $totalPrice . '</td>
            <td>
                <div class="progress" style="width: 150px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">
                        ' . $progressPercent . '%
                    </div>
                </div>
            </td>
            <td style="text-align: center;"><a class="buttonView" href="read-one-readymade-form.php?id=' . $orderID . '" target="_parent">View</a></td>
            <td style="text-align: center;"><a class="buttonEdit" href="update-readymade-form.php?id=' . $orderID . '" target="_parent">Edit</a></td>
            <td style="text-align: center;"><a class="buttonDelete" href="delete-readymade-form.php?id=' . $orderID . '" target="_parent">Delete</a></td>
        </tr>';
    }
    echo '</tbody></table>';
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
 </nav>
<!-- Link to External JS -->
<script src="dashboard.js"></script>



<br><br><br>

<div class="container_boxes">
    <h4>READY-MADE ORDERS LIST <a href="create-readymade-form.php">Create New Ready-Made Order</a></h4>
    <!-- Add Back to Dashboard button -->
    <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
    <div id="ready-made-orders-list">
        <table width="100%" border="1" cellspacing="5">
            <thead>
                <tr>
                    <th>USER NAME</th>
                    <th>PRODUCT NAME</th>
                    <th>TOTAL PRICE</th>
                    <th>ORDER STATUS</th>
                    <th colspan="3" style="text-align: center;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["User_Name"]) ?></td>
                        <td><?= htmlspecialchars($row["Product_Name"]) ?></td>
                        <td><?= number_format((float)$row["Total_Price"], 2, '.', '') ?></td>
                        <td>
                            <div class="progress" style="width: 150px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(max((int)$row["Order_Status"], 0), 100) ?>%;" aria-valuenow="<?= min(max((int)$row["Order_Status"], 0), 100) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= min(max((int)$row["Order_Status"], 0), 100) ?>%
                                </div>
                            </div>
                        </td>
                        <td style="text-align: center;"><a class="buttonView" href="read-one-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent">View</a></td>
                        <td style="text-align: center;"><a class="buttonEdit" href="update-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent">Edit</a></td>
                        <td style="text-align: center;"><a class="buttonDelete" href="delete-readymade-form.php?id=<?= htmlspecialchars($row["ReadyMadeOrder_ID"]) ?>" target="_parent">Delete</a></td>
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

        // Determine the URL based on whether the search bar is empty
        const url = searchValue ? `read-all-readymade-form.php?search=${encodeURIComponent(searchValue)}` : `read-all-readymade-form.php`;

        // Send an AJAX request to fetch results
        fetch(url)
            .then(response => response.text())
            .then(data => {
                // Update the ready-made orders list with the filtered results
                const readyMadeOrdersList = document.getElementById('ready-made-orders-list');
                readyMadeOrdersList.innerHTML = data.trim(); // Ensure no extra whitespace is added
            })
            .catch(error => console.error('Error fetching search results:', error));
    });

    // Ensure the table resets to its original state when the search input is cleared
    document.querySelector('.search-box input[name="search"]').addEventListener('blur', function () {
        if (!this.value.trim()) {
            // Reload the page to reset the table to its original state
            location.reload();
        }
    });

    document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
        const searchValue = this.value;

        // Send an AJAX request to fetch filtered results
        fetch(`read-all-readymade-form.php?search=${encodeURIComponent(searchValue)}`)
            .then(response => response.text())
            .then(data => {
                // Update the ready-made orders list with the filtered results
                const readyMadeOrdersList = document.getElementById('ready-made-orders-list');
                readyMadeOrdersList.innerHTML = data;
            })
            .catch(error => console.error('Error fetching search results:', error));
    });
</script>
</body>
</html>
