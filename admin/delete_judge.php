<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if admin is logged in
if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: login.php");
    exit;
}

// Process delete operation
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get URL parameter
    $id = trim($_GET["id"]);
    
    // Check if there are scores from this judge
    $check_sql = "SELECT COUNT(*) as count FROM scores WHERE judge_id = ?";
    if($check_stmt = mysqli_prepare($conn, $check_sql)){
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $row = mysqli_fetch_assoc($check_result);
        
        if($row["count"] > 0){
            // Judge has scores, ask for confirmation to delete scores too
            if(isset($_GET["confirm"]) && $_GET["confirm"] == "yes"){
                // Delete scores first
                $delete_scores_sql = "DELETE FROM scores WHERE judge_id = ?";
                if($delete_scores_stmt = mysqli_prepare($conn, $delete_scores_sql)){
                    mysqli_stmt_bind_param($delete_scores_stmt, "i", $id);
                    mysqli_stmt_execute($delete_scores_stmt);
                    mysqli_stmt_close($delete_scores_stmt);
                }
                
                // Now delete the judge
                $delete_judge_sql = "DELETE FROM judges WHERE id = ?";
                if($delete_judge_stmt = mysqli_prepare($conn, $delete_judge_sql)){
                    mysqli_stmt_bind_param($delete_judge_stmt, "i", $id);
                    
                    if(mysqli_stmt_execute($delete_judge_stmt)){
                        $_SESSION["success_message"] = "Judge deleted successfully.";
                        header("location: index.php");
                        exit();
                    } else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    
                    mysqli_stmt_close($delete_judge_stmt);
                }
            } else {
                // Show confirmation page
                ?>
                <h2>Delete Judge</h2>
                <p>This judge has submitted scores. Deleting this judge will also delete all their scores.</p>
                <p>Are you sure you want to delete this judge and all their scores?</p>
                
                <div>
                    <a href="delete_judge.php?id=<?php echo $id; ?>&confirm=yes" class="btn">Yes, Delete</a>
                    <a href="index.php" class="btn">No, Cancel</a>
                </div>
                <?php
                require_once "../includes/footer.php";
                exit();
            }
        } else {
            // No scores, just delete the judge
            $delete_sql = "DELETE FROM judges WHERE id = ?";
            if($delete_stmt = mysqli_prepare($conn, $delete_sql)){
                mysqli_stmt_bind_param($delete_stmt, "i", $id);
                
                if(mysqli_stmt_execute($delete_stmt)){
                    $_SESSION["success_message"] = "Judge deleted successfully.";
                    header("location: index.php");
                    exit();
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($delete_stmt);
            }
        }
        
        mysqli_stmt_close($check_stmt);
    }
} else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}

mysqli_close($conn);
?>
