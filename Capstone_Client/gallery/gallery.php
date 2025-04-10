<?php
session_start();
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}
include("../config/database.php");

// Initialize search term
$searchTerm = "";

// Check if a search term is submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%"; // Add wildcards for LIKE clause
}

// Fetch readymade products (with search filter)
try {
    $sql = "SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock != '0'";
    if (!empty($searchTerm)) {
        $sql .= " AND Product_Name LIKE :searchTerm";
    }
    $stmt = $pdo->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->execute();
    $readymadeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch pre-order products (with search filter)
try {
    $sql = "SELECT * FROM tbl_prod_info WHERE product_type = 'readymade' AND Stock = '0'";
    if (!empty($searchTerm)) {
        $sql .= " AND Product_Name LIKE :searchTerm";
    }
    $stmt = $pdo->prepare($sql);
    if (!empty($searchTerm)) {
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->execute();
    $preorderProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/Gallery.css">
    <link rel="stylesheet" href="../static/css-files/grid.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <style>
        .search-form {
            position: relative;
        }

        .clear-search {
            position: absolute;
            right: 40px; /* Adjust as needed */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none; /* Hidden by default */
            color: #888;
            font-size: 1.2em;
        }

        .clear-search.show {
            display: block; /* Show when there's a search term */
        }
    </style>
</head>
<body>
<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="dropdown">
        <a href="../dashboard/home.php">Home</a>
        <ul class="dropdown-menus">
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php" class="active">Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
      <li class="dropdown">
        <a href="../profile/profile.php" class="profile" id="sign_in">Profile</a>
        <ul class="dropdown-menus">
          <li><a href="../profile/profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </li>
      <span id="close-menu-btn" class="material-symbols-outlined">close</span>
    </ul>
    <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
  </nav>
</header>

<main>
    <div class="hero-section">
        <div class="content">
            <h1>What Furniture Are You Looking For?</h1>
            <form action="" method="GET" class="search-form" id="search-form">
                <input type="text" name="search" id="search-input" placeholder="Search a type of furniture" required value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <span class="clear-search" id="clear-search">&times;</span>
                <button type="submit" class="material-symbols-outlined">search</button>
            </form>
        </div>
    </div>

    <div class="Show_Rooms">
        <a href="../showroom/showroom.php" class="showroom">Showroom</a>
        <a href="../../keme/index.html" class="showroom">Measure</a>




    </div>

    <!-- Ready Made Section -->
    <div class="container" id="readymade-products-container">
        <h3 class="title">Ready Made Furnitures</h3>
        <div class="products-container">
            <?php foreach ($readymadeProducts as $product): ?>
                <div class="product" data-name="p-<?= $product['Product_ID'] ?>">
                    <?php
                    $imageURLs = explode(',', $product['ImageURL']);
                    $firstImageUrl = trim($imageURLs[0]);
                    $imageFilePath = '../uploads/product/' . basename($firstImageUrl);
                    $glbFilePath = '../uploads/product/3d/' . basename($product['GLB_File_URL']);
                    ?>
                    <?php if (!empty($product['GLB_File_URL']) && file_exists($glbFilePath)): ?>
                        <model-viewer class="image-card three-d" src="<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate></model-viewer>
                    <?php elseif (!empty($firstImageUrl) && file_exists($imageFilePath)): ?>
                        <img src="<?= $imageFilePath ?>" alt="<?= $product['Product_Name'] ?>" class="image-card">
                    <?php else: ?>
                        <p>No Media Available</p>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
                    <div class="price">₱ <?= number_format($product['Price'], 2) ?></div>
                    <div class="view-btn-con">
                        <a href="gallery-readone.php?product_id=<?= $product['Product_ID'] ?>" class="view-btn">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Pre-order Section -->
    <?php if (!empty($preorderProducts)): ?>
    <div class="container" id="preorder-products-container">
        <h3 class="title">Pre-order Furnitures</h3>
        <div class="products-container">
            <?php foreach ($preorderProducts as $product): ?>
                <div class="product" data-name="p-<?= $product['Product_ID'] ?>">
                    <?php
                    $imageURLs = explode(',', $product['ImageURL']);
                    $firstImageUrl = trim($imageURLs[0]);
                    $imageFilePath = '../uploads/product/' . basename($firstImageUrl);
                    $glbFilePath = '../uploads/product/3d/' . basename($product['GLB_File_URL']);
                    ?>
                    <?php if (!empty($product['GLB_File_URL']) && file_exists($glbFilePath)): ?>
                        <model-viewer class="image-card three-d" src="<?= $glbFilePath ?>" ar shadow-intensity="1" camera-controls auto-rotate></model-viewer>
                    <?php elseif (!empty($firstImageUrl) && file_exists($imageFilePath)): ?>
                        <img src="<?= $imageFilePath ?>" alt="<?= $product['Product_Name'] ?>" class="image-card">
                    <?php else: ?>
                        <p>No Media Available</p>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
                    <div class="price">₱ <?= number_format($product['Price'], 2) ?></div>
                    <div class="view-btn-con">
                        <a href="gallery-readone.php?product_id=<?= $product['Product_ID'] ?>" class="view-btn">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Customization Section -->
    <div class="container">
        <h3 class="title">Customize Now!</h3>
        <p class="explain-cus">
            Create your personalized furniture! Select options or upload designs.
            Our team will help bring your vision to life.
        </p>
    </div>

    <div class="customized-container">
        <!-- Furniture Type -->
        <div class="cus-boxed">
            <label for="furniture">Furniture Type:</label>
            <select id="furniture" name="furniture">
                <option value="" disabled selected>Select one</option>
                <option value="chair">Chair</option>
                <option value="table">Table</option>
                <option value="salaset">Sala Set</option>
                <option value="bedframe">Bed Frame</option>
                <option value="custom">Custom</option>
            </select>
            <div id="furniture-custom-options" style="display: none;">
                <label class="upload-file" for="fileFurnitureImage">Upload Design:</label>
                <input type="file" id="fileFurnitureImage" name="fileFurnitureImage" accept="image/*">
                <div id="furniture-image-preview" class="image-preview"></div>
                <label for="furniture-info">Details:</label>
                <input type="text" id="furniture-info" name="furniture-info" placeholder="Additional info">
            </div>
        </div>

        <!-- Sizes -->
        <div class="cus-boxed">
            <label for="sizes">Sizes:</label>
            <select id="sizes" name="sizes">
                <option value="" disabled selected>Select one</option>
            </select>
            <div id="sizes-custom-options" style="display: none;">
                <label for="sizes-info">Sizes Additional Info:</label>
                <input type="text" id="sizes-info" name="sizes-info" placeholder="Enter additional info">
            </div>
        </div>

        <!-- Color -->
        <div class="cus-boxed">
            <label for="color">Color:</label>
            <select id="color" name="color">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Teak">Teak</option>
                <option value="Weathered Oak">Weathered Oak</option>
                <option value="Antique White">Antique White</option>
                <option value="Black">Black</option>
                <option value="Driftwood Gray">Driftwood Gray</option>
            </select>
            <div id="color-custom-options" style="display: none;">
                <label for="color-info">Color Additional Info:</label>
                <input type="text" id="color-info" name="color-info" placeholder="Enter additional info">
                <label for="fileColorImage">Upload Color Image:</label>
                <input type="file" id="fileColorImage" name="fileColorImage" accept="image/*">
                <div id="color-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Texture -->
        <div class="cus-boxed">
            <label for="texture">Texture:</label>
            <select id="texture" name="texture">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="High Gloss">High Gloss</option>
                <option value="Brushed">Brushed</option>
                <option value="Textured">Textured</option>
                <option value="Smooth">Smooth</option>
                <option value="Semi Glossy">Semi Glossy</option>
                <option value="Duco Finish">Duco Finish</option>
                <option value="Rustic">Rustic</option>
            </select>
            <div id="texture-custom-options" style="display: none;">
                <label for="texture-info">Texture Additional Info:</label>
                <input type="text" id="texture-info" name="texture-info" placeholder="Enter additional info">
                <label for="fileTextureImage">Upload Texture Image:</label>
                <input type="file" id="fileTextureImage" name="fileTextureImage" accept="image/*">
                <div id="texture-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Wood -->
        <div class="cus-boxed">
            <label for="woods">Wood:</label>
            <select id="woods" name="wood">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Nara">Nara</option>
                <option value="Plywood 1/4">Plywood 1/4</option>
                <option value="Plywood 1/8">Plywood 1/8</option>
            </select>
            <div id="woods-custom-options" style="display: none;">
                <label for="wood-info">Wood Additional Info:</label>
                <input type="text" id="wood-info" name="wood-info" placeholder="Enter additional info">
                <label for="fileWoodImage">Upload Wood Image:</label>
                <input type="file" id="fileWoodImage" name="fileWoodImage" accept="image/*">
                <div id="wood-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Foam -->
        <div class="cus-boxed">
            <label for="foam">Foam:</label>
            <select id="foam" name="foam">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Uratex">Uratex</option>
            </select>
            <div id="foam-custom-options" style="display: none;">
                <label for="foam-info">Foam Additional Info:</label>
                <input type="text" id="foam-info" name="foam-info" placeholder="Enter additional info">
                <label for="fileFoamImage">Upload Foam Image:</label>
                <input type="file" id="fileFoamImage" name="fileFoamImage" accept="image/*">
                <div id="foam-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Cover -->
        <div class="cus-boxed">
            <label for="cover">Cover:</label>
            <select id="cover" name="cover">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="German Leather">German Leather</option>
                <option value="Korean Leather">Korean Leather</option>
                <option value="Velvet">Velvet</option>
                <option value="Italian Leather">Italian Leather</option>
                <option value="Linen Type">Linen Type</option>
            </select>
            <div id="cover-custom-options" style="display: none;">
                <label for="cover-info">Cover Additional Info:</label>
                <input type="text" id="cover-info" name="cover-info" placeholder="Enter additional info">
                <label for="fileCoverImage">Upload Cover Image:</label>
                <input type="file" id="fileCoverImage" name="fileCoverImage" accept="image/*">
                <div id="cover-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Design -->
        <div class="cus-boxed">
            <label for="design">Design:</label>
            <select id="design" name="design">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Eclectic">Eclectic</option>
                <option value="Shabby Chic">Shabby Chic</option>
                <option value="Rustic">Rustic</option>
                <option value="Asian Inspired">Asian Inspired</option>
                <option value="Farmhouse">Farmhouse</option>
            </select>
            <div id="design-custom-options" style="display: none;">
                <label for="design-info">Design Additional Info:</label>
                <input type="text" id="design-info" name="design-info" placeholder="Enter additional info">
                <label for="fileDesignImage">Upload Design Image:</label>
                <input type="file" id="fileDesignImage" name="fileDesignImage" accept="image/*">
                <div id="design-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Tiles -->
        <div class="cus-boxed">
            <label for="tiles">Tiles:</label>
            <select id="tiles" name="tiles">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Porcelain">Porcelain</option>
                <option value="Quartz">Quartz</option>
                <option value="Marble">Marble</option>
                <option value="Granite">Granite</option>
            </select>
            <div id="tiles-custom-options" style="display: none;">
                <label for="tiles-info">Tiles Additional Info:</label>
                <input type="text" id="tiles-info" name="tiles-info" placeholder="Enter additional info">
                <label for="fileTileImage">Upload Tile Image:</label>
                <input type="file" id="fileTileImage" name="fileTileImage" accept="image/*">
                <div id="tiles-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Metal -->
        <div class="cus-boxed">
            <label for="metal">Metal:</label>
            <select id="metal" name="metal">
                <option value="" disabled selected>Select one</option>
                <option value="custom">Custom</option>
                <option value="Flat Bar">Flat Bar</option>
                <option value="Tubular">Tubular</option>
            </select>
            <div id="metal-custom-options" style="display: none;">
                <label for="metal-info">Metal Additional Info:</label>
                <input type="text" id="metal-info" name="metal-info" placeholder="Enter additional info">
                <label for="fileMetalImage">Upload Metal Image:</label>
                <input type="file" id="fileMetalImage" name="fileMetalImage" accept="image/*">
                <div id="metal-image-preview" class="image-preview"></div>
            </div>
        </div>

        <!-- Submit and Reset Buttons -->
        <div class="cus-boxed">
            <button id="submit-button">Submit</button>
            <button id="reset-button">Reset</button>
        </div>
    </div>

    <!-- Print Modal -->
    <div id="print-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Customization Receipt</h2>
            <div id="modal-preview" class="receipt-content"></div>
            <button id="modal-ok-button">OK</button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirm Submission</h2>
            <p>Are you sure you want to submit this customization?</p>
            <button id="confirm-ok-button">OK</button>
            <!-- <button id="confirm-cancel-button">Cancel</button> -->
        </div>
    </div>
</main>
<footer class="footer">
    </footer>
    <script src="../static/Javascript-files/script.js"></script>
    <script src="../static/Javascript-files/customization.js"></script>
    <script src="../static/Javascript-files/gallery.js"></script>
    <script src="../static/Javascript-files/jQuery.js"></script>
</body>
</html>
