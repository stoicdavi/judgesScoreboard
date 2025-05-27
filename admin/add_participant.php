<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if admin is logged in
if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: login.php");
    exit;
}

// Generate CSRF token if it doesn't exist
if(!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Define variables and initialize with empty values
$name = $category = "";
$name_err = $category_err = $csrf_err = "";

// Get existing categories for dropdown
$categories_sql = "SELECT DISTINCT category FROM participants ORDER BY category";
$categories_result = mysqli_query($conn, $categories_sql);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $csrf_err = "CSRF token validation failed.";
    } else {
        // Validate name
        if(empty(trim($_POST["name"]))){
            $name_err = "Please enter a name.";
        } else{
            $name = trim($_POST["name"]);
        }
        
        // Validate category
        if(empty(trim($_POST["category"]))){
            $category_err = "Please enter a category.";
        } else{
            $category = trim($_POST["category"]);
        }
        
        // Check input errors before inserting in database
        if(empty($name_err) && empty($category_err) && empty($csrf_err)){
            // Prepare an insert statement
            $sql = "INSERT INTO participants (name, category) VALUES (?, ?)";
             
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ss", $param_name, $param_category);
                
                // Set parameters
                $param_name = $name;
                $param_category = $category;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Set success message and redirect
                    $_SESSION["success_message"] = "Participant added successfully.";
                    header("location: index.php");
                    exit();
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<h2>Add Participant</h2>
<p>Please fill this form to add a participant.</p>

<?php 
if(!empty($csrf_err)){
    echo '<div class="alert alert-danger">' . $csrf_err . '</div>';
}        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Name</label>
        <input type="text" name="name" value="<?php echo $name; ?>">
        <span class="text-danger"><?php echo $name_err; ?></span>
    </div>    
    <div>
        <label>Category</label>
        <select name="category" id="category">
            <option value="">Select Category</option>
            <?php 
            // Display existing categories
            while($cat = mysqli_fetch_assoc($categories_result)): 
            ?>
                <option value="<?php echo htmlspecialchars($cat["category"]); ?>"
                    <?php echo ($category == $cat["category"]) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat["category"]); ?>
                </option>
            <?php endwhile; ?>
            <option value="new">-- Add New Category --</option>
        </select>
        <div id="new-category-div" style="display: none; margin-top: 10px;">
            <input type="text" id="new-category" placeholder="Enter new category">
            <button type="button" id="add-category-btn">Add</button>
        </div>
        <span class="text-danger"><?php echo $category_err; ?></span>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
    <div>
        <input type="submit" value="Submit">
        <input type="reset" value="Reset">
    </div>
    <p>Back to <a href="index.php">Admin Panel</a>.</p>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const newCategoryDiv = document.getElementById('new-category-div');
    const newCategoryInput = document.getElementById('new-category');
    const addCategoryBtn = document.getElementById('add-category-btn');
    
    categorySelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newCategoryDiv.style.display = 'block';
        } else {
            newCategoryDiv.style.display = 'none';
        }
    });
    
    addCategoryBtn.addEventListener('click', function() {
        const newCategory = newCategoryInput.value.trim();
        if (newCategory) {
            // Add new option to select
            const option = document.createElement('option');
            option.value = newCategory;
            option.textContent = newCategory;
            
            // Insert before the "Add New Category" option
            categorySelect.insertBefore(option, categorySelect.lastElementChild);
            
            // Select the new option
            categorySelect.value = newCategory;
            
            // Hide the new category div
            newCategoryDiv.style.display = 'none';
        }
    });
});
</script>

<?php 
mysqli_free_result($categories_result);
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
