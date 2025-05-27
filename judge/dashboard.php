<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get all participants
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM scores WHERE judge_id = ? AND participant_id = p.id) as scored 
        FROM participants p 
        ORDER BY p.category, p.name";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["judge_id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION["judge_name"]); ?> (<?php echo htmlspecialchars($_SESSION["judge_username"] ?? $_SESSION["judge_email"]); ?>)</h2>
<p>Select a participant to score:</p>

<?php
if(isset($_SESSION["success_message"])) {
    echo '<div class="alert alert-success">' . $_SESSION["success_message"] . '</div>';
    unset($_SESSION["success_message"]);
}

if(isset($_SESSION["error_message"])) {
    echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
    unset($_SESSION["error_message"]);
}
?>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $current_category = "";
        while($row = mysqli_fetch_assoc($result)): 
            if($current_category != $row["category"]) {
                $current_category = $row["category"];
                echo "<tr><th colspan='4'>{$current_category}</th></tr>";
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo htmlspecialchars($row["category"]); ?></td>
            <td>
                <?php if($row["scored"] > 0): ?>
                    <span style="color: green;">Scored</span>
                <?php else: ?>
                    <span style="color: red;">Not scored</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($row["scored"] > 0): ?>
                    <a href="view_score.php?id=<?php echo $row["id"]; ?>">View Score</a>
                <?php else: ?>
                    <a href="submit_score.php?id=<?php echo $row["id"]; ?>">Score</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php 
mysqli_free_result($result);
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
