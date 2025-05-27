<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Get all participants with their average scores
$sql = "SELECT p.id, p.name, p.category, 
        AVG(s.score) as avg_score, 
        COUNT(s.id) as num_scores,
        MIN(s.score) as min_score,
        MAX(s.score) as max_score
        FROM participants p 
        LEFT JOIN scores s ON p.id = s.participant_id 
        GROUP BY p.id 
        ORDER BY p.category, avg_score DESC";
$result = mysqli_query($conn, $sql);
?>

<h2>Scoreboard</h2>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Average Score</th>
            <th>Min Score</th>
            <th>Max Score</th>
            <th>Number of Judges</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $current_category = "";
        while($row = mysqli_fetch_assoc($result)): 
            if($current_category != $row["category"]) {
                $current_category = $row["category"];
                echo "<tr><th colspan='6'>{$current_category}</th></tr>";
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo htmlspecialchars($row["category"]); ?></td>
            <td><?php echo $row["avg_score"] ? number_format($row["avg_score"], 2) : "No scores yet"; ?></td>
            <td><?php echo $row["min_score"] ? number_format($row["min_score"], 2) : "-"; ?></td>
            <td><?php echo $row["max_score"] ? number_format($row["max_score"], 2) : "-"; ?></td>
            <td><?php echo $row["num_scores"]; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="results.php">View Detailed Results</a></p>

<?php 
mysqli_free_result($result);
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
