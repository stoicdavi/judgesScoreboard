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
        // Validate username
        if(empty(trim($_POST["username"]))){
            $username_err = "Please enter a username.";
        } else{
            // Prepare a select statement
            $sql = "SELECT id FROM judges WHERE username = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = trim($_POST["username"]);
                
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
            // Prepare a select statement
            $sql = "SELECT id FROM judges WHERE email = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
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
        
        // Validate password
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter a password.";     
        } elseif(strlen(trim($_POST["password"])) < 6){
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
        
        // Check input errors before inserting in database
        if(empty($username_err) && empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($csrf_err)){
            
            // Prepare an insert statement
            $sql = "INSERT INTO judges (username, name, email, password) VALUES (?, ?, ?, ?)";
             
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_name, $param_email, $param_password);
                
                // Set parameters
                $param_username = $username;
                $param_name = $name;
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Set success message and redirect
                    $_SESSION["success_message"] = "Judge added successfully.";
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
    
    // Close connection
    mysqli_close($conn);
}
?>

<h2>Add Judge</h2>
<p>Please fill this form to create a judge account.</p>

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
        <label>Password</label>
        <input type="password" name="password" value="<?php echo $password; ?>">
        <span class="text-danger"><?php echo $password_err; ?></span>
    </div>
    <div>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" value="<?php echo $confirm_password; ?>">
        <span class="text-danger"><?php echo $confirm_password_err; ?></span>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
    <div>
        <input type="submit" value="Submit">
        <input type="reset" value="Reset">
    </div>
    <p>Back to <a href="index.php">Admin Panel</a>.</p>
</form>

<?php require_once "../includes/footer.php"; ?>
