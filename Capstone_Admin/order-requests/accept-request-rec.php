<?php
session_start();
include '../config/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validate the request ID
$requestID = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$requestID) {
    die("Invalid request ID.");
}

try {
    $pdo->beginTransaction();

    // Fetch the order request
    $orderRequest = fetchOrderRequest($pdo, $requestID);

    if (!$orderRequest) {
        throw new Exception("Order request not found.");
    }

    // Process the order based on its type
    switch ($orderRequest['Order_Type']) {
        case 'ready_made':
            processReadyMade($pdo, $orderRequest);
            break;

        case 'pre_order':
            processPreOrder($pdo, $orderRequest);
            break;

        case 'custom':
            processCustomOrder($pdo, $orderRequest, $requestID);
            break;

        default:
            throw new Exception("Unsupported order type.");
    }

    // Update the order request instead of deleting it
    $stmt = $pdo->prepare("UPDATE tbl_order_request SET Order_Status = 1, Processed = 1 WHERE Request_ID = ?");
    $stmt->execute([$requestID]);

    $pdo->commit();
    header("Location: read-all-request-form.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing request: " . $e->getMessage());
}

/**
 * Fetch the order request from tbl_order_request.
 */
function fetchOrderRequest($pdo, $requestID) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_request WHERE Request_ID = ?");
    $stmt->execute([$requestID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Process a ready-made order.
 */
function processReadyMade($pdo, $orderRequest) {
    // Fetch the product name from tbl_prod_info
    $productName = fetchProductName($pdo, $orderRequest['Product_ID']);

    // Insert into tbl_ready_made_orders
    $stmt = $pdo->prepare("
        INSERT INTO tbl_ready_made_orders 
        (Product_ID, User_ID, Quantity, Total_Price, Order_Status, Product_Status)
        VALUES (?, ?, ?, ?, 10, 90)
    ");
    $stmt->execute([
        $orderRequest['Product_ID'],
        $orderRequest['User_ID'],
        $orderRequest['Quantity'],
        $orderRequest['Total_Price']
    ]);

    // Insert into tbl_progress
    insertIntoProgress($pdo, $orderRequest, 'ready_made', 10, 90, $productName);
}

/**
 * Process a pre-order.
 */
function processPreOrder($pdo, $orderRequest) {
    // Insert into tbl_preorder
    $stmt = $pdo->prepare("
        INSERT INTO tbl_preorder 
        (Product_ID, User_ID, Quantity, Total_Price, Preorder_Status, Product_Status)
        VALUES (?, ?, ?, ?, 10, 0)
    ");
    $stmt->execute([
        $orderRequest['Product_ID'],
        $orderRequest['User_ID'],
        $orderRequest['Quantity'],
        $orderRequest['Total_Price']
    ]);

    // Insert into tbl_progress
    insertIntoProgress($pdo, $orderRequest, 'pre_order', 10, 0);
}

/**
 * Process a custom order.
 */
function processCustomOrder($pdo, $orderRequest, $requestID) {
    // Fetch temporary customization
    $customization = fetchTemporaryCustomization($pdo, $orderRequest['Customization_ID']);

    if (!$customization) {
        throw new Exception("Customization request not found.");
    }

    // Create a custom product
    $newProductID = createCustomProduct($pdo, $customization, $requestID);

    // Fetch the product name from tbl_prod_info
    $productName = fetchProductName($pdo, $newProductID);

    // Move to permanent customizations
    moveToPermanentCustomizations($pdo, $customization, $newProductID);

    // Insert into tbl_progress
    insertIntoProgress($pdo, [
        'User_ID' => $customization['User_ID'],
        'Product_ID' => $newProductID,
        'Product_Name' => $productName,
        'Quantity' => 1,
        'Total_Price' => 0.00
    ], 'custom', 10, 0);

    // Cleanup temporary data
    cleanupTemporaryData($pdo, $orderRequest['Customization_ID']);
}

/**
 * Fetch temporary customization data.
 */
function fetchTemporaryCustomization($pdo, $tempCustomizationID) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_customizations_temp WHERE Temp_Customization_ID = ?");
    $stmt->execute([$tempCustomizationID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a custom product in tbl_prod_info.
 */
function createCustomProduct($pdo, $customization, $requestID) {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_prod_info 
        (Product_Name, Description, Category, product_type)
        VALUES (?, ?, 'Custom Furniture', 'custom')
    ");
    $productName = 'Custom ' . $customization['Furniture_Type'];
    $description = 'Custom order from request #' . $requestID;
    $stmt->execute([$productName, $description]);
    return $pdo->lastInsertId();
}

/**
 * Fetch the product name from tbl_prod_info.
 */
function fetchProductName($pdo, $productID) {
    $stmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
    $stmt->execute([$productID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['Product_Name'] ?? 'N/A';
}

/**
 * Move temporary customization to permanent customizations.
 */
function moveToPermanentCustomizations($pdo, $customization, $newProductID) {
    $stmt = $pdo->prepare("
        INSERT INTO tbl_customizations (
            User_ID, Furniture_Type, Furniture_Type_Additional_Info,
            Standard_Size, Desired_Size, Color, Color_Image_URL, Color_Additional_Info,
            Texture, Texture_Image_URL, Texture_Additional_Info,
            Wood_Type, Wood_Image_URL, Wood_Additional_Info,
            Foam_Type, Foam_Image_URL, Foam_Additional_Info,
            Cover_Type, Cover_Image_URL, Cover_Additional_Info,
            Design, Design_Image_URL, Design_Additional_Info,
            Tile_Type, Tile_Image_URL, Tile_Additional_Info,
            Metal_Type, Metal_Image_URL, Metal_Additional_Info,
            Product_ID, Order_Status, Product_Status
        ) VALUES (
            ?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?
        )
    ");
    $stmt->execute([
        $customization['User_ID'],
        $customization['Furniture_Type'],
        $customization['Furniture_Type_Additional_Info'],
        $customization['Standard_Size'],
        $customization['Desired_Size'],
        $customization['Color'],
        $customization['Color_Image_URL'],
        $customization['Color_Additional_Info'],
        $customization['Texture'],
        $customization['Texture_Image_URL'],
        $customization['Texture_Additional_Info'],
        $customization['Wood_Type'],
        $customization['Wood_Image_URL'],
        $customization['Wood_Additional_Info'],
        $customization['Foam_Type'],
        $customization['Foam_Image_URL'],
        $customization['Foam_Additional_Info'],
        $customization['Cover_Type'],
        $customization['Cover_Image_URL'],
        $customization['Cover_Additional_Info'],
        $customization['Design'],
        $customization['Design_Image_URL'],
        $customization['Design_Additional_Info'],
        $customization['Tile_Type'],
        $customization['Tile_Image_URL'],
        $customization['Tile_Additional_Info'],
        $customization['Metal_Type'],
        $customization['Metal_Image_URL'],
        $customization['Metal_Additional_Info'],
        $newProductID,
        10, // Order_Status
        0  // Product_Status
    ]);
}

/**
 * Insert progress data into tbl_progress.
 */
function insertIntoProgress($pdo, $orderRequest, $orderType, $orderStatus, $productStatus, $productName = null) {
    // Fetch the product name if not provided
    if (empty($productName)) {
        $stmt = $pdo->prepare("SELECT Product_Name FROM tbl_prod_info WHERE Product_ID = ?");
        $stmt->execute([$orderRequest['Product_ID']]);
        $productInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $productName = $productInfo['Product_Name'] ?? 'N/A';
    }

    // Insert progress data into tbl_progress
    $stmt = $pdo->prepare("
        INSERT INTO tbl_progress 
        (User_ID, Product_ID, Product_Name, Order_Type, Order_Status, Product_Status, Quantity, Total_Price, Date_Added, LastUpdate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $orderRequest['User_ID'],
        $orderRequest['Product_ID'],
        $productName,
        $orderType,
        $orderStatus,
        $productStatus,
        $orderRequest['Quantity'],
        $orderRequest['Total_Price']
    ]);
}

/**
 * Cleanup temporary customization data.
 */
function cleanupTemporaryData($pdo, $tempCustomizationID) {
    $stmt = $pdo->prepare("DELETE FROM tbl_customizations_temp WHERE Temp_Customization_ID = ?");
    $stmt->execute([$tempCustomizationID]);
}

/**
 * Delete the order request from tbl_order_request.
 */
function deleteOrderRequest($pdo, $requestID) {
    $stmt = $pdo->prepare("DELETE FROM tbl_order_request WHERE Request_ID = ?");
    $stmt->execute([$requestID]);
}
?>
