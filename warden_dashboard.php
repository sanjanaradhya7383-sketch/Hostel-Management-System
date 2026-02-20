<?php
session_start();

// Allow only warden
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
if ($conn->connect_error) {
    die("Database connection failed");
}

// Fetch complaints with student name
$sql = "SELECT c.*, u.name AS student_name
        FROM complaints c
        JOIN users u ON c.student_id = u.user_id
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warden Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="dashboard-container">

    <div class="dashboard-header">
        <h2>Warden Dashboard</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Category</th>
                <th>Description</th>
                <th>Proof</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>

                <td>
                    <img src="uploads/<?php echo htmlspecialchars($row['proof_image']); ?>" alt="Proof">
                </td>

                <td>
                    <?php if ($row['status'] === 'Raised') { ?>
                        <span class="status status-raised">Raised</span>
                    <?php } elseif ($row['status'] === 'In Progress') { ?>
                        <span class="status status-progress">In Progress</span>
                    <?php } else { ?>
                        <span class="status status-resolved">Resolved</span>
                    <?php } ?>
                </td>

                <td>
                    <a class="update-link"
                       href="update_complaint.php?id=<?php echo $row['complaint_id']; ?>">
                        Update
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>

<?php $conn->close(); ?>
