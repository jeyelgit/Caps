<?php
session_start();

// Include the database connection
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin data
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

// Initialize the search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Order Status Map
$orderStatusLabels = [
    0 => 'Order Received',
    10 => 'Order Confirmed',
    20 => 'Design Finalization',
    30 => 'Material Preparation',
    40 => 'Production Started',
    50 => 'Mid-Production',
    60 => 'Finishing Process',
    70 => 'Quality Check',
    80 => 'Final Assembly',
    90 => 'Ready for Delivery',
    100 => 'Delivered / Completed',
];

// Product Status Map
$productStatusLabels = [
    0 => 'Concept Stage',
    10 => 'Design Approved',
    20 => 'Material Sourcing',
    30 => 'Cutting & Shaping',
    40 => 'Structural Assembly',
    50 => 'Detailing & Refinements',
    60 => 'Sanding & Pre-Finishing',
    70 => 'Final Coating',
    80 => 'Assembly & Testing',
    90 => 'Ready for Sale',
    100 => 'Sold / Installed',
];

// Fetch records based on search or fetch all records
$query = "
    SELECT 
        'custom' AS Order_Type,
        c.Customization_ID AS ID,
        c.Furniture_Type AS Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        c.Order_Status,
        c.Product_Status,
        p.Price AS Price,
        c.Last_Update AS Last_Update,
        c.User_ID
    FROM tbl_customizations c
    JOIN tbl_user_info u ON c.User_ID = u.User_ID
    LEFT JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
    WHERE c.Order_Status = 100 AND c.Product_Status = 100
    UNION
    SELECT 
        'pre_order' AS Order_Type,
        po.Preorder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        po.Preorder_Status AS Order_Status,
        po.Product_Status,
        pr.Price AS Price,
        po.Order_Date AS Last_Update,
        u.User_ID
    FROM tbl_preorder po
    JOIN tbl_user_info u ON po.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON po.Product_ID = pr.Product_ID
    WHERE po.Preorder_Status = 100 AND po.Product_Status = 100
    UNION
    SELECT 
        'ready_made' AS Order_Type,
        rmo.ReadyMadeOrder_ID AS ID,
        pr.Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        rmo.Order_Status,
        rmo.Product_Status,
        pr.Price AS Price,
        rmo.Order_Date AS Last_Update,
        u.User_ID
    FROM tbl_ready_made_orders rmo
    JOIN tbl_user_info u ON rmo.User_ID = u.User_ID
    JOIN tbl_prod_info pr ON rmo.Product_ID = pr.Product_ID
    WHERE rmo.Order_Status = 100 AND rmo.Product_Status = 100
    ORDER BY Last_Update DESC
";

if (!empty($search)) {
    $query = str_replace(
        "WHERE c.Order_Status = 100 AND c.Product_Status = 100",
        "WHERE c.Order_Status = 100 AND c.Product_Status = 100 AND (u.First_Name LIKE :search OR u.Last_Name LIKE :search OR c.Furniture_Type LIKE :search)",
        $query
    );
    $query = str_replace(
        "WHERE po.Preorder_Status = 100 AND po.Product_Status = 100",
        "WHERE po.Preorder_Status = 100 AND po.Product_Status = 100 AND (u.First_Name LIKE :search OR u.Last_Name LIKE :search OR pr.Product_Name LIKE :search)",
        $query
    );
    $query = str_replace(
        "WHERE rmo.Order_Status = 100 AND rmo.Product_Status = 100",
        "WHERE rmo.Order_Status = 100 AND rmo.Product_Status = 100 AND (u.First_Name LIKE :search OR u.Last_Name LIKE :search OR pr.Product_Name LIKE :search)",
        $query
    );
}

$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX requests to return only table rows
if (isset($_GET['search'])) {
    foreach ($rows as $row) {
        $totalPrice = isset($row['Price']) ? number_format((float) $row['Price'], 2, '.', '') : 0;
        echo '
        <tr>
            <td>' . htmlspecialchars($row['Order_Type']) . '</td>
            <td>' . htmlspecialchars($row['User_Name']) . '</td>
            <td>' . htmlspecialchars($row['Product_Name']) . '</td>
            <td>' . htmlspecialchars($orderStatusLabels[$row['Order_Status']] ?? "Unknown") . '</td>
            <td>' . htmlspecialchars($productStatusLabels[$row['Product_Status']] ?? "Unknown") . '</td>
            <td>' . $totalPrice . '</td>
            <td style="text-align: center;">
                <a class="buttonView" href="read-one-history-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">View</a>
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
<script src="../static/js/dashboard.js"></script>


 </nav>
        <br><br><br>

        <div class="container_boxes">
            <h4>PURCHASE HISTORY LIST</h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                    <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                </div>
                <div id="purchase-history-list">
    <table width="100%" border="1" cellspacing="5">
        <thead>
            <tr>
                <th>ORDER TYPE</th>
                <th>USER NAME</th>
                <th>PRODUCT NAME</th>
                <th>ORDER STATUS</th>
                <th>PRODUCT STATUS</th>
                <th>TOTAL PRICE</th>
                <th style="text-align: center;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Order_Type']) ?></td>
                    <td><?= htmlspecialchars($row['User_Name']) ?></td>
                    <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                    <td><?= htmlspecialchars($orderStatusLabels[$row['Order_Status']] ?? "Unknown") ?></td>
                    <td><?= htmlspecialchars($productStatusLabels[$row['Product_Status']] ?? "Unknown") ?></td>
                    <td>
                        <?php
                            $totalPrice = 0;
                            if (isset($row['Price'])) {
                                $totalPrice = number_format((float) $row['Price'], 2, '.', '');
                            }
                        ?>
                        <?= $totalPrice ?>
                    </td>
                    <td style="text-align: center;">
                        <a class="buttonView" href="read-one-history-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">View</a>
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
                const parent = this.parentElement;
                const dropdownMenu = parent.querySelector('.dropdown-menu');
                parent.classList.toggle('active');

                const chevron = this.querySelector('i');
                if (parent.classList.contains('active')) {
                    chevron.classList.remove('bx-chevron-down');
                    chevron.classList.add('bx-chevron-up');
                } else {
                    chevron.classList.remove('bx-chevron-up');
                    chevron.classList.add('bx-chevron-down');
                }

                dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
            });
        });
        </script>
        <script>
    document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
        const searchValue = this.value.trim();
        const url = searchValue ? `read-all-history-form.php?search=${encodeURIComponent(searchValue)}` : `read-all-history-form.php?search=`;

        fetch(url)
            .then(response => response.text())
            .then(data => {
                const tableBody = document.querySelector('#purchase-history-list table tbody');
                tableBody.innerHTML = data.trim(); // Replace the table body content with the fetched rows
            })
            .catch(error => console.error('Error fetching search results:', error));
    });
</script>
</body>

</html>
