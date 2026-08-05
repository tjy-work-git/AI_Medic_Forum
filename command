This command is to auto click a specific link

  <script>
            window.onload = function() {
                var urlParams = new URLSearchParams(window.location.search);
                if (!urlParams.has('name')) {
                    var firstCategoryLink = document.querySelector('.bottomnav li a');
                    if (firstCategoryLink) {
                        firstCategoryLink.click();
                    }
                }
            };
        </script>

bind param to url

if (isset($_GET['name'])) {
                    $selectedCategory = $_GET['name'];
                    $stmt = $conn->prepare("SELECT * FROM productadmin WHERE category = ?");
                    $stmt->execute([$selectedCategory]);  // Pass parameters directly to execute method
                    $categoryDisplayed = false;

                    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!$categoryDisplayed) {
                            // Display category only once
                            ?>
                            <h1><?php echo htmlspecialchars($user['category']); ?></h1>
                            <?php
                            $categoryDisplayed = true;
                        }

                        // Display product information
                        ?>
                        <div class="row g-0 my-3 border product-row">
                            <div class="col">
                                <?php
                                $width = 302;  // width in pixels
                                $height = 350; // height in pixels
                                if (!empty($user['image'])) {
                                    $imageName = htmlspecialchars($user['image']);
                                    echo "<img src='$imageName' alt='User Image' width='$width' height='$height'>";
                                }
                                ?>
                            </div>