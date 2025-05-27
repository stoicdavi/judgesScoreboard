<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if the user is already logged in as admin
if(isset($_SESSION["admin"]) && $_SESSION["admin"] === true){
    header("location: index.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
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
        // Hardcoded admin credentials for simplicity
        // In a real application, you would store these securely in the database
        $admin_username = "admin";
        $admin_password = "admin123"; // In production, use password_hash
        
        if($username === $admin_username && $password === $admin_password){
            // Password is correct, start a new session
            session_start();
            
            // Store data in session variables
            $_SESSION["admin"] = true;
            $_SESSION["admin_username"] = $username;
            
            // Redirect user to admin panel
            header("location: index.php");
        } else{
            // Username or password is invalid
            $login_err = "Invalid username or password.";
        }
    }
}
?>

<h2>Admin Login</h2>
<p>Please fill in your credentials to login.</p>

<?php 
if(!empty($login_err)){
    echo '<div class="alert alert-danger">' . $login_err . '</div>';
}        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Username</label>
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
    <p>Back to <a href="../public/index.php">Scoreboard</a>.</p>
</form>

<?php require_once "../includes/footer.php"; ?>
