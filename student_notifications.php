<?php
session_start();

// Only students allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
if ($conn->connect_error) {
    die("Database connection failed");
}

$student_id = $_SESSION['user_id'];

// Fetch notifications (latest first)
$sql = "SELECT * FROM notifications 
        WHERE user_id = $student_id
        ORDER BY created_at DESC";

$result = $conn->query($sql);

// Mark all as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $student_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Notifications</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .notification {
            background: #f9f9ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 5px solid #667eea;
        }

        .notification-time {
            font-size: 12px;
            color: #777;
            margin-top: 6px;
        }

        .empty-msg {
            text-align: center;
            color: #666;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">

    <div class="dashboard-header">
        <h2>🔔 Notifications</h2>
        <a href="student_dashboard.php" class="logout-btn">Back</a>
    </div>

    <?php if ($result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="notification">
                <?php echo htmlspecialchars($row['message']); ?>
                <div class="notification-time">
                    <?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="empty-msg">No notifications yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php $conn->close(); ?>
