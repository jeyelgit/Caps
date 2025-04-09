<?php
session_start();

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

// Check if the request is an AJAX search request
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "
        SELECT
            Progress_ID AS ID,
            Product_Name,
            CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
            Order_Type,
            Order_Status,
            Product_Status,
            Total_Price,
            LastUpdate
        FROM tbl_progress p
        JOIN tbl_user_info u ON p.User_ID = u.User_ID
        WHERE (u.First_Name LIKE :search
        OR u.Last_Name LIKE :search
        OR p.Product_Name LIKE :search)
        ORDER BY LastUpdate DESC
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the table rows for AJAX requests
    echo '<table width="100%" border="1" cellspacing="5">
        <tr>
            <th>ORDER TYPE</th>
            <th>USER NAME</th>
            <th>PRODUCT NAME</th>
            <th>ORDER STATUS</th>
            <th>PRODUCT STATUS</th>
            <th>TOTAL PRICE</th>
            <th colspan="3" style="text-align: center;">ACTIONS</th>
        </tr>';
    foreach ($rows as $row) {
        $orderStatusPercentage = calculatePercentage($row['Order_Status']);
        $productStatusPercentage = calculatePercentage($row['Product_Status']);
        echo '
        <tr>
            <td>' . htmlspecialchars($row['Order_Type']) . '</td>
            <td>' . htmlspecialchars($row['User_Name']) . '</td>
            <td>' . htmlspecialchars($row['Product_Name']) . '</td>
            <td>
                <div class="status-bar">
                    <div class="status-bar-fill order-status-bar" style="width: ' . $orderStatusPercentage . '%;">
                        ' . $orderStatusPercentage . '%
                    </div>
                </div>
            </td>
            <td>
                <div class="status-bar">
                    <div class="status-bar-fill product-status-bar" style="width: ' . $productStatusPercentage . '%;">
                        ' . $productStatusPercentage . '%
                    </div>
                </div>
            </td>
            <td>' . number_format((float) $row['Total_Price'], 2, '.', '') . '</td>
            <td style="text-align: center;"><a class="buttonView" href="read-one-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">View</a></td>';
        if ($row['Order_Status'] != 100) {
            echo '<td style="text-align: center;"><a class="buttonEdit" href="update-progress-form.php?id=' . htmlspecialchars($row['ID']) . '&order_type=' . htmlspecialchars($row['Order_Type']) . '" target="_parent">Edit</a></td>';
        } else {
            echo '<td style="text-align: center;"></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    exit; // Stop further execution for AJAX requests
}

// Fetch all progress records from tbl_progress
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "
    SELECT
        Progress_ID AS ID,
        Product_Name,
        CONCAT(u.First_Name, ' ', u.Last_Name) AS User_Name,
        Order_Type,
        Order_Status,
        Product_Status,
        Total_Price,
        LastUpdate
    FROM tbl_progress p
    JOIN tbl_user_info u ON p.User_ID = u.User_ID
    WHERE (u.First_Name LIKE :search
    OR u.Last_Name LIKE :search
    OR p.Product_Name LIKE :search)
    ORDER BY LastUpdate DESC
";

$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Function to calculate percentage
function calculatePercentage($status) {
    return ($status / 100) * 100;
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
    <link href="../static/css-files/progress.css" rel="stylesheet">
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

<!-- Link to External JS -->
<script src="../static/js/dashboard.js"></script>


 </nav>
        <br><br><br>

        <div class="container_boxes">
            <h4>PROGRESS LIST <a href="create-progress-form.php">Create New Progress</a></h4>
            <!-- Add Back to Dashboard button -->
            <div class="button-container">
                <a href="../dashboard/dashboard.php" class="buttonBack">Back to Dashboard</a>
                <!-- <a href="../purchase-history/read-all-history-form.php" class="buttonBack">Read All Purchase History</a> -->
            </div>
            <div id="progress-list">
                <table width="100%" border="1" cellspacing="5">
                    <tr>
                        <th>ORDER TYPE</th>
                        <th>USER NAME</th>
                        <th>PRODUCT NAME</th>
                        <th>ORDER STATUS</th>
                        <th>PRODUCT STATUS</th>
                        <th>TOTAL PRICE</th>
                        <th colspan="3" style="text-align: center;">ACTIONS</th>
                    </tr>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        // Fetch Product_Name dynamically if missing
                        if (empty($row['Product_Name'])) {
                            $stmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
                            $stmt->execute([$row['Product_ID']]);
                            $productInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                            $row['Product_Name'] = $productInfo['Product_Name'] ?? 'N/A';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Order_Type']) ?></td>
                            <td><?= htmlspecialchars($row['User_Name']) ?></td>
                            <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                            <td>
                                <div class="status-bar">
                                    <div class="status-bar-fill order-status-bar" style="width: <?= calculatePercentage($row['Order_Status']) ?>%;">
                                        <?= calculatePercentage($row['Order_Status']) ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="status-bar">
                                    <div class="status-bar-fill product-status-bar" style="width: <?= calculatePercentage($row['Product_Status']) ?>%;">
                                        <?= calculatePercentage($row['Product_Status']) ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format((float) $row['Total_Price'], 2, '.', '') ?></td>
                            <td style="text-align: center;"><a class="buttonView" href="read-one-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">View</a></td>
                            <?php if($row['Order_Status'] != 100): ?>
                                <td style="text-align: center;"><a class="buttonEdit" href="update-progress-form.php?id=<?= htmlspecialchars($row['ID']) ?>&order_type=<?= htmlspecialchars($row['Order_Type']) ?>" target="_parent">Edit</a></td>
                            <?php else: ?>
                                <td style="text-align: center;"></td>
                                <td style="text-align: center;"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
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

        document.querySelector('.search-box input[name="search"]').addEventListener('input', function () {
            const searchValue = this.value;

            // Send an AJAX request to fetch filtered results
            fetch(`read-all-progress-form.php?search=${encodeURIComponent(searchValue)}`)
                .then(response => response.text())
                .then(data => {
                    // Update the progress list with the filtered results
                    const progressList = document.getElementById('progress-list');
                    progressList.innerHTML = data;
                })
                .catch(error => console.error('Error fetching search results:', error));
        });
    </script>
</body>

</html>
