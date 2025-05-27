<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if the user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username/email
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username or email.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Check if input is email or username
        $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        if($is_email) {
            // Login with email
            $sql = "SELECT id, username, name, email, password FROM judges WHERE email = ?";
        } else {
            // Login with username
            $sql = "SELECT id, username, name, email, password FROM judges WHERE username = ?";
        }
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $db_username, $name, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["judge_id"] = $id;
                            $_SESSION["judge_username"] = $db_username;
                            $_SESSION["judge_name"] = $name;
                            $_SESSION["judge_email"] = $email;
                            
                            // Redirect user to dashboard
                            header("location: dashboard.php");
                        } else{
                            // Password is not valid
                            $login_err = "Invalid username/email or password.";
                        }
                    }
                } else{
                    // Username/email doesn't exist
                    $login_err = "Invalid username/email or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<h2>Judge Login</h2>
<p>Please fill in your credentials to login.</p>

<?php 
if(!empty($login_err)){
    echo '<div class="alert alert-danger">' . $login_err . '</div>';
}        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Username or Email</label>
        <input type="text" name="username" value="<?php echo $username; ?>">
        <span class="text-danger"><?php echo $username_err; ?></span>
    </div>    
    <div>
        <label>Password</label>
        <input type="password" name="password">
        <span class="text-danger"><?php echo $password_err; ?></span>
    </div>
    <div>
        <input type="submit" value="Login">
    </div>
</form>

<?php require_once "../includes/footer.php"; ?>
