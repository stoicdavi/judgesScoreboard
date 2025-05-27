<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Generate CSRF token if it doesn't exist
if(!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$participant_id = $score = $comments = "";
$score_err = $csrf_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Verify CSRF token
    if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $csrf_err = "CSRF token validation failed.";
    } else {
        // Validate score
        if(empty(trim($_POST["score"]))){
            $score_err = "Please enter a score.";
        } elseif(!is_numeric($_POST["score"]) || $_POST["score"] < 0 || $_POST["score"] > 100){
            $score_err = "Please enter a valid score between 0 and 100.";
        } else{
            $score = trim($_POST["score"]);
        }
        
        $participant_id = trim($_POST["participant_id"]);
        $comments = trim($_POST["comments"]);
        
        // Check input errors before inserting in database
        if(empty($score_err) && empty($csrf_err)){
            // Check if judge has already scored this participant
            $check_sql = "SELECT id FROM scores WHERE judge_id = ? AND participant_id = ?";
            if($check_stmt = mysqli_prepare($conn, $check_sql)){
                mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION["judge_id"], $participant_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);
                
                if(mysqli_stmt_num_rows($check_stmt) > 0){
                    $_SESSION["error_message"] = "You have already scored this participant.";
                    header("location: dashboard.php");
                    exit();
                }
                
                mysqli_stmt_close($check_stmt);
            }
            
            // Prepare an insert statement
            $sql = "INSERT INTO scores (judge_id, participant_id, score, comments) VALUES (?, ?, ?, ?)";
             
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "iids", $param_judge_id, $param_participant_id, $param_score, $param_comments);
                
                // Set parameters
                $param_judge_id = $_SESSION["judge_id"];
                $param_participant_id = $participant_id;
                $param_score = $score;
                $param_comments = $comments;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Set success message and redirect to dashboard
                    $_SESSION["success_message"] = "Score submitted successfully.";
                    header("location: dashboard.php");
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
} else {
    // Check if participant ID is provided
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $participant_id = trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM participants WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $participant_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $participant_name = $row["name"];
                    $participant_category = $row["category"];
                } else{
                    // URL doesn't contain valid id parameter. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Check if judge has already scored this participant
        $sql = "SELECT * FROM scores WHERE judge_id = ? AND participant_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ii", $_SESSION["judge_id"], $participant_id);
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) > 0){
                    $_SESSION["error_message"] = "You have already scored this participant.";
                    header("location: dashboard.php");
                    exit();
                }
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        // Close connection
        mysqli_close($conn);
    } else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>

<h2>Submit Score</h2>
<p>Participant: <?php echo htmlspecialchars($participant_name); ?> (<?php echo htmlspecialchars($participant_category); ?>)</p>

<?php 
if(!empty($csrf_err)){
    echo '<div class="alert alert-danger">' . $csrf_err . '</div>';
}        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Score (0-100)</label>
        <input type="number" name="score" min="0" max="100" step="0.1" value="<?php echo $score; ?>">
        <span class="text-danger"><?php echo $score_err; ?></span>
    </div>
    <div>
        <label>Comments</label>
        <textarea name="comments" rows="4"><?php echo $comments; ?></textarea>
    </div>
    <input type="hidden" name="participant_id" value="<?php echo $participant_id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
    <div>
        <input type="submit" value="Submit Score">
        <a href="dashboard.php" class="btn">Cancel</a>
    </div>
</form>

<?php require_once "../includes/footer.php"; ?>
