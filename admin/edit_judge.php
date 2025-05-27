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
$username = $name = $email = $password = $confirm_password = "";
$username_err = $name_err = $email_err = $password_err = $confirm_password_err = $csrf_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $csrf_err = "CSRF token validation failed.";
    } else {
        $id = trim($_POST["id"]);
        
        // Validate username
        if(empty(trim($_POST["username"]))){
            $username_err = "Please enter a username.";
        } else{
            // Prepare a select statement to check if username exists for another judge
            $sql = "SELECT id FROM judges WHERE username = ? AND id != ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "si", $param_username, $param_id);
                
                // Set parameters
                $param_username = trim($_POST["username"]);
                $param_id = $id;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $username_err = "This username is already taken.";
                    } else{
                        $username = trim($_POST["username"]);
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate name
        if(empty(trim($_POST["name"]))){
            $name_err = "Please enter a name.";
        } else{
            $name = trim($_POST["name"]);
        }
        
        // Validate email
        if(empty(trim($_POST["email"]))){
            $email_err = "Please enter an email.";
        } else{
            // Prepare a select statement to check if email exists for another judge
            $sql = "SELECT id FROM judges WHERE email = ? AND id != ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "si", $param_email, $param_id);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                $param_id = $id;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $email_err = "This email is already taken.";
                    } else{
                        $email = trim($_POST["email"]);
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate password only if it's provided (optional update)
        if(!empty(trim($_POST["password"]))){
            if(strlen(trim($_POST["password"])) < 6){
                $password_err = "Password must have at least 6 characters.";
            } else{
                $password = trim($_POST["password"]);
            }
            
            // Validate confirm password
            if(empty(trim($_POST["confirm_password"]))){
                $confirm_password_err = "Please confirm password.";     
            } else{
                $confirm_password = trim($_POST["confirm_password"]);
                if(empty($password_err) && ($password != $confirm_password)){
                    $confirm_password_err = "Password did not match.";
                }
            }
        }
        
        // Check input errors before updating in database
        if(empty($username_err) && empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($csrf_err)){
            // Prepare an update statement
            if(!empty($password)){
                // Update with password
                $sql = "UPDATE judges SET username = ?, name = ?, email = ?, password = ? WHERE id = ?";
                
                if($stmt = mysqli_prepare($conn, $sql)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "ssssi", $param_username, $param_name, $param_email, $param_password, $param_id);
                    
                    // Set parameters
                    $param_username = $username;
                    $param_name = $name;
                    $param_email = $email;
                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    $param_id = $id;
                }
            } else {
                // Update without password
                $sql = "UPDATE judges SET username = ?, name = ?, email = ? WHERE id = ?";
                
                if($stmt = mysqli_prepare($conn, $sql)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_name, $param_email, $param_id);
                    
                    // Set parameters
                    $param_username = $username;
                    $param_name = $name;
                    $param_email = $email;
                    $param_id = $id;
                }
            }
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Set success message and redirect
                $_SESSION["success_message"] = "Judge updated successfully.";
                header("location: index.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
} else {
    // Check if id parameter exists
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id = trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM judges WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $username = $row["username"] ?? '';
                    $name = $row["name"];
                    $email = $row["email"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    } else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}

// Close connection
mysqli_close($conn);
?>

<h2>Edit Judge</h2>
<p>Please edit the judge information.</p>

<?php 
if(!empty($csrf_err)){
    echo '<div class="alert alert-danger">' . $csrf_err . '</div>';
}        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Username (Unique ID)</label>
        <input type="text" name="username" value="<?php echo $username; ?>">
        <span class="text-danger"><?php echo $username_err; ?></span>
    </div>
    <div>
        <label>Display Name</label>
        <input type="text" name="name" value="<?php echo $name; ?>">
        <span class="text-danger"><?php echo $name_err; ?></span>
    </div>    
    <div>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo $email; ?>">
        <span class="text-danger"><?php echo $email_err; ?></span>
    </div>    
    <div>
        <label>Password (leave blank to keep current password)</label>
        <input type="password" name="password" value="<?php echo $password; ?>">
        <span class="text-danger"><?php echo $password_err; ?></span>
    </div>
    <div>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" value="<?php echo $confirm_password; ?>">
        <span class="text-danger"><?php echo $confirm_password_err; ?></span>
    </div>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
    <div>
        <input type="submit" value="Submit">
        <a href="index.php" class="btn">Cancel</a>
    </div>
</form>

<?php require_once "../includes/footer.php"; ?>
