<?php
session_start();

// Allow only students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Student Dashboard</h2>
        <p>Welcome! You can raise hostel complaints here.</p>

        <a href="raise_complaint.php">
            <button style="margin-top:15px;">Raise Complaint</button>
        </a>
        <a href="student_notifications.php">
    <button style="margin-top:10px; background:#764ba2;">View Notifications</button>
</a>


        <br><br>
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
