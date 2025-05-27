<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'scoring_user');
define('DB_PASSWORD', 'your_secure_password');
define('DB_NAME', 'scoring_app');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
