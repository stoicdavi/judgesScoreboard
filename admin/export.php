<?php
require_once "../config/db.php";
require_once "../includes/header.php";

// Check if admin is logged in
if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true){
    header("location: login.php");
    exit;
}

// Check if export type is specified
if(isset($_GET["type"])){
    $export_type = $_GET["type"];
    
    if($export_type === "csv"){
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="scores_export_' . date('Y-m-d') . '.csv"');
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Output the column headings
        fputcsv($output, array('Participant ID', 'Participant Name', 'Category', 'Judge ID', 'Judge Name', 'Score', 'Comments', 'Timestamp'));
        
        // Get all scores with participant and judge details
        $sql = "SELECT s.*, p.name as participant_name, p.category, j.name as judge_name 
                FROM scores s 
                JOIN participants p ON s.participant_id = p.id 
                JOIN judges j ON s.judge_id = j.id 
                ORDER BY p.category, p.name, s.score DESC";
        $result = mysqli_query($conn, $sql);
        
        // Loop over the rows, outputting them
        while($row = mysqli_fetch_assoc($result)){
            fputcsv($output, array(
                $row['participant_id'],
                $row['participant_name'],
                $row['category'],
                $row['judge_id'],
                $row['judge_name'],
                $row['score'],
                $row['comments'],
                $row['timestamp']
            ));
        }
        
        // Close the file pointer
        fclose($output);
        exit();
    } elseif($export_type === "summary_csv"){
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="scores_summary_' . date('Y-m-d') . '.csv"');
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Output the column headings
        fputcsv($output, array('Participant ID', 'Participant Name', 'Category', 'Average Score', 'Min Score', 'Max Score', 'Number of Judges', 'Standard Deviation'));
        
        // Get summary statistics for all participants
        $sql = "SELECT p.id, p.name, p.category, 
                AVG(s.score) as avg_score, 
                MIN(s.score) as min_score, 
                MAX(s.score) as max_score, 
                COUNT(s.id) as num_scores,
                STDDEV(s.score) as std_dev
                FROM participants p 
                LEFT JOIN scores s ON p.id = s.participant_id 
                GROUP BY p.id 
                ORDER BY p.category, avg_score DESC";
        $result = mysqli_query($conn, $sql);
        
        // Loop over the rows, outputting them
        while($row = mysqli_fetch_assoc($result)){
            fputcsv($output, array(
                $row['id'],
                $row['name'],
                $row['category'],
                $row['avg_score'],
                $row['min_score'],
                $row['max_score'],
                $row['num_scores'],
                $row['std_dev']
            ));
        }
        
        // Close the file pointer
        fclose($output);
        exit();
    }
}
?>

<h2>Export Data</h2>
<p>Select the type of export you want to generate:</p>

<div>
    <a href="export.php?type=csv" class="btn">Export All Scores (CSV)</a>
    <a href="export.php?type=summary_csv" class="btn">Export Summary Statistics (CSV)</a>
</div>

<p><a href="index.php">Back to Admin Panel</a></p>

<?php 
mysqli_close($conn);
require_once "../includes/footer.php"; 
?>
