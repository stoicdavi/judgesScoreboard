<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoring Application</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <h1>Scoring Application</h1>
        <nav>
            <ul>
                <li><a href="/public/index.php">Scoreboard</a></li>
                <?php if(isset($_SESSION["judge_id"])): ?>
                    <li><a href="/judge/dashboard.php">Judge Dashboard</a></li>
                    <li><a href="/judge/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/judge/login.php">Judge Login</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["admin"]) && $_SESSION["admin"] === true): ?>
                    <li><a href="/admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
