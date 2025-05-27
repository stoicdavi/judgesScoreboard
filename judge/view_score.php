<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if participant ID is provided
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $participant_id = trim($_GET["id"]);
    
    // Get participant details
    $sql = "SELECT * FROM participants WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $participant_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1){
                $participant = mysqli_fetch_assoc($result);
            } else {
                header("location: error.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Get judge's score for this participant
    $sql = "SELECT * FROM scores WHERE judge_id = ? AND participant_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION["judge_id"], $participant_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1){
                $score = mysqli_fetch_assoc($result);
            } else {
                $_SESSION["error_message"] = "You haven't scored this participant yet.";
                header("location: dashboard.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    header("location: error.php");
    exit();
}

mysqli_close($conn);
?>

<h2>Your Score for <?php echo htmlspecialchars($participant["name"]); ?></h2>
<p>Category: <?php echo htmlspecialchars($participant["category"]); ?></p>

<div>
    <p><strong>Score:</strong> <?php echo htmlspecialchars($score["score"]); ?></p>
    <p><strong>Comments:</strong> <?php echo nl2br(htmlspecialchars($score["comments"])); ?></p>
    <p><strong>Submitted:</strong> <?php echo htmlspecialchars($score["timestamp"]); ?></p>
</div>

<a href="dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once "../includes/footer.php"; ?>
