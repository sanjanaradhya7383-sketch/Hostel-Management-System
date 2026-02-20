<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* Allow only warden */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

require_once 'send_email.php';

/* Database connection */
$conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
if ($conn->connect_error) {
    die("Database connection failed");
}

$complaint_id = $_GET['id'] ?? null;
if (!$complaint_id) {
    die("Invalid complaint");
}

/* Handle update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status  = $_POST['status'];
    $remarks = $_POST['remarks'];

    /* Upload resolved image */
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = null;
    if (!empty($_FILES['resolved_image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['resolved_image']['name']);
        move_uploaded_file(
            $_FILES['resolved_image']['tmp_name'],
            $upload_dir . $image_name
        );
    }

    /* Update complaint */
    $conn->query("
        UPDATE complaints 
        SET status='$status' 
        WHERE complaint_id='$complaint_id'
    ");

    /* Insert / update resolution */
    $conn->query("
        INSERT INTO resolutions (complaint_id, resolved_image, remarks)
        VALUES ('$complaint_id', '$image_name', '$remarks')
        ON DUPLICATE KEY UPDATE
        resolved_image='$image_name', remarks='$remarks'
    ");

    /* 🔔 IF RESOLVED → EMAIL STUDENT */
    if ($status === 'Resolved') {

        $res = $conn->query("
            SELECT u.email, u.name
            FROM complaints c
            JOIN users u ON c.student_id = u.user_id
            WHERE c.complaint_id = '$complaint_id'
        ");

        $student = $res->fetch_assoc();

        $subject = "Your Hostel Complaint Has Been Resolved";
        $body = "
            <h3>Hello {$student['name']},</h3>
            <p>Your hostel complaint has been <b>resolved successfully</b>.</p>
            <p><b>Remarks:</b> $remarks</p>
            <p>Please login to the Hostel Complaint System to view details.</p>
            <br>
            <p>Regards,<br>Hostel Administration</p>
        ";

        sendEmail($student['email'], $subject, $body);
    }

    echo "<script>
        alert('Complaint updated successfully');
        window.location='warden_dashboard.php';
    </script>";
    exit();
}

/* Fetch complaint */
$complaint = $conn->query("
    SELECT * FROM complaints WHERE complaint_id='$complaint_id'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Complaint</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Update Complaint</h2>

        <form method="post" enctype="multipart/form-data">

            <div class="input-group">
                <label>Status</label>
                <select name="status" required>
                    <option <?= $complaint['status']=='Raised'?'selected':'' ?>>Raised</option>
                    <option <?= $complaint['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                    <option <?= $complaint['status']=='Resolved'?'selected':'' ?>>Resolved</option>
                </select>
            </div>

            <div class="input-group">
                <label>Remarks</label>
                <textarea name="remarks" rows="4" required></textarea>
            </div>

            <div class="input-group">
                <label>Upload Resolved Image</label>
                <input type="file" name="resolved_image" accept="image/*">
            </div>

            <button type="submit">Update Complaint</button>
        </form>

        <br>
        <a href="warden_dashboard.php">Back</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
