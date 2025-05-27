<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if admin is logged in
if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: login.php");
    exit;
}

// Get all judges
$judges_sql = "SELECT id, username, name, email FROM judges ORDER BY name";
$judges_result = mysqli_query($conn, $judges_sql);

// Get all participants
$participants_sql = "SELECT id, name, category FROM participants ORDER BY category, name";
$participants_result = mysqli_query($conn, $participants_sql);

// Display success/error messages
if(isset($_SESSION["success_message"])) {
    echo '<div class="alert alert-success">' . $_SESSION["success_message"] . '</div>';
    unset($_SESSION["success_message"]);
}

if(isset($_SESSION["error_message"])) {
    echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
    unset($_SESSION["error_message"]);
}
?>

<h2>Admin Panel</h2>

<div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
    <a href="add_judge.php" class="btn">Add New Judge</a>
    <a href="add_participant.php" class="btn">Add New Participant</a>
    <a href="export.php" class="btn">Export Results</a>
</div>

<h3>Judges</h3>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Display Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($judge = mysqli_fetch_assoc($judges_result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($judge["username"] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($judge["name"]); ?></td>
            <td><?php echo htmlspecialchars($judge["email"]); ?></td>
            <td>
                <a href="edit_judge.php?id=<?php echo $judge["id"]; ?>">Edit</a> | 
                <a href="delete_judge.php?id=<?php echo $judge["id"]; ?>" onclick="return confirm('Are you sure you want to delete this judge?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h3>Participants</h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $current_category = "";
        while($participant = mysqli_fetch_assoc($participants_result)): 
            if($current_category != $participant["category"]) {
                $current_category = $participant["category"];
                echo "<tr><th colspan='3'>{$current_category}</th></tr>";
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($participant["name"]); ?></td>
            <td><?php echo htmlspecialchars($participant["category"]); ?></td>
            <td>
                <a href="edit_participant.php?id=<?php echo $participant["id"]; ?>">Edit</a> | 
                <a href="delete_participant.php?id=<?php echo $participant["id"]; ?>" onclick="return confirm('Are you sure you want to delete this participant?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="logout.php">Logout</a></p>

<?php 
mysqli_free_result($judges_result);
mysqli_free_result($participants_result);
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
