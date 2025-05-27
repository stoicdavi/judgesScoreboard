<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Get category filter if provided
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Get all categories for filter dropdown
$categories_sql = "SELECT DISTINCT category FROM participants ORDER BY category";
$categories_result = mysqli_query($conn, $categories_sql);

// Build the SQL query based on filter
$sql = "SELECT p.id, p.name, p.category, 
        AVG(s.score) as avg_score, 
        COUNT(s.id) as num_scores,
        MIN(s.score) as min_score,
        MAX(s.score) as max_score
        FROM participants p 
        LEFT JOIN scores s ON p.id = s.participant_id";

if (!empty($category_filter)) {
    $sql .= " WHERE p.category = '" . mysqli_real_escape_string($conn, $category_filter) . "'";
}

$sql .= " GROUP BY p.id ORDER BY p.category, avg_score DESC";

$result = mysqli_query($conn, $sql);
?>

<h2>Detailed Results</h2>

<!-- Category filter -->
<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div style="margin-bottom: 20px;">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                <option value="<?php echo htmlspecialchars($cat["category"]); ?>" 
                    <?php echo ($category_filter == $cat["category"]) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat["category"]); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</form>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Average Score</th>
            <th>Min Score</th>
            <th>Max Score</th>
            <th>Number of Judges</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $current_category = "";
        while($row = mysqli_fetch_assoc($result)): 
            if($current_category != $row["category"]) {
                $current_category = $row["category"];
                echo "<tr><th colspan='7'>{$current_category}</th></tr>";
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo htmlspecialchars($row["category"]); ?></td>
            <td><?php echo $row["avg_score"] ? number_format($row["avg_score"], 2) : "No scores yet"; ?></td>
            <td><?php echo $row["min_score"] ? number_format($row["min_score"], 2) : "-"; ?></td>
            <td><?php echo $row["max_score"] ? number_format($row["max_score"], 2) : "-"; ?></td>
            <td><?php echo $row["num_scores"]; ?></td>
            <td><a href="participant_details.php?id=<?php echo $row["id"]; ?>">View Details</a></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="index.php">Back to Scoreboard</a></p>

<?php 
mysqli_free_result($result);
mysqli_free_result($categories_result);
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
