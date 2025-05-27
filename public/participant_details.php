<?php
require_once "../config/db.php";
require_once "../includes/header.php";

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
    
    // Get scores for this participant
    $sql = "SELECT s.*, j.name as judge_name 
            FROM scores s 
            JOIN judges j ON s.judge_id = j.id 
            WHERE s.participant_id = ? 
            ORDER BY s.score DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $participant_id);
        
        if(mysqli_stmt_execute($stmt)){
            $scores_result = mysqli_stmt_get_result($stmt);
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Calculate statistics
    $sql = "SELECT 
            AVG(score) as avg_score,
            MIN(score) as min_score,
            MAX(score) as max_score,
            COUNT(*) as num_scores,
            STDDEV(score) as std_dev
            FROM scores 
            WHERE participant_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $participant_id);
        
        if(mysqli_stmt_execute($stmt)){
            $stats_result = mysqli_stmt_get_result($stmt);
            $stats = mysqli_fetch_assoc($stats_result);
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

<h2>Participant Details: <?php echo htmlspecialchars($participant["name"]); ?></h2>
<p>Category: <?php echo htmlspecialchars($participant["category"]); ?></p>

<h3>Statistics</h3>
<table>
    <tr>
        <th>Average Score</th>
        <td><?php echo $stats["avg_score"] ? number_format($stats["avg_score"], 2) : "No scores yet"; ?></td>
    </tr>
    <tr>
        <th>Minimum Score</th>
        <td><?php echo $stats["min_score"] ? number_format($stats["min_score"], 2) : "-"; ?></td>
    </tr>
    <tr>
        <th>Maximum Score</th>
        <td><?php echo $stats["max_score"] ? number_format($stats["max_score"], 2) : "-"; ?></td>
    </tr>
    <tr>
        <th>Number of Judges</th>
        <td><?php echo $stats["num_scores"]; ?></td>
    </tr>
    <tr>
        <th>Standard Deviation</th>
        <td><?php echo $stats["std_dev"] ? number_format($stats["std_dev"], 2) : "-"; ?></td>
    </tr>
</table>

<h3>Individual Scores</h3>
<?php if(mysqli_num_rows($scores_result) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Judge</th>
                <th>Score</th>
                <th>Comments</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while($score = mysqli_fetch_assoc($scores_result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($score["judge_name"]); ?></td>
                <td><?php echo htmlspecialchars($score["score"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($score["comments"])); ?></td>
                <td><?php echo htmlspecialchars($score["timestamp"]); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No scores have been submitted for this participant yet.</p>
<?php endif; ?>

<p><a href="results.php">Back to Results</a></p>

<?php require_once "../includes/footer.php"; ?>
