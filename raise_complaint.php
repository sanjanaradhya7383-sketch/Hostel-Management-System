<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* Allow only students */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

require_once 'send_email.php';

/* Database connection */
$conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
if ($conn->connect_error) {
    die("Database connection failed");
}

/* Get student details */
$student_id = $_SESSION['user_id'];
$studentRes = $conn->query(
    "SELECT name, email FROM users WHERE user_id = $student_id"
);
$student = $studentRes->fetch_assoc();

$studentName  = $student['name'];
$studentEmail = $student['email'];

/* Handle form submission */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category    = $_POST['category'];
    $description = $_POST['description'];

    /* Upload proof image */
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name  = time() . "_" . basename($_FILES['proof_image']['name']);
    $target_path = $upload_dir . $image_name;

    if (!move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_path)) {
        echo "<script>alert('Image upload failed');</script>";
    } else {

        $sql = "INSERT INTO complaints 
                (student_id, category, description, proof_image, status)
                VALUES 
                ('$student_id', '$category', '$description', '$image_name', 'Raised')";

        if ($conn->query($sql)) {

            /* Get warden email */
            $wardenRes = $conn->query(
                "SELECT email FROM users WHERE role='warden' LIMIT 1"
            );
            $warden = $wardenRes->fetch_assoc();
            $wardenEmail = $warden['email'];

            /* =========================
               UPDATED EMAIL CONTENT
            ========================= */
            $subject = "New Hostel Complaint Raised – $category";

            $body = "
                <h2>New Hostel Complaint Raised</h2>
                <p>A student has raised a new complaint in the system.</p>
                <hr>
                <p><strong>Student Name:</strong> $studentName</p>
                <p><strong>Student Email:</strong> $studentEmail</p>
                <p><strong>Category:</strong> $category</p>
                <p><strong>Description:</strong> $description</p>
                <p><strong>Raised On:</strong> " . date('d M Y, h:i A') . "</p>
                <hr>
                <p>Please log in to the Hostel Complaint Management System to take action.</p>
                <br>
                <p>Regards,<br><b>Hostel Complaint Management System</b></p>
            ";

            /* Send email */
            sendEmail($wardenEmail, $subject, $body);

            echo "<script>
                alert('Complaint submitted successfully');
                window.location='student_dashboard.php';
            </script>";
        } else {
            echo "<script>alert('Database error while saving complaint');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Raise Complaint</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Raise a Complaint</h2>

        <form method="post" enctype="multipart/form-data">

            <div class="input-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">-- Select --</option>
                    <option>Electricity</option>
                    <option>Water</option>
                    <option>Food</option>
                    <option>Cleaning</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>
            </div>

            <div class="input-group">
                <label>Upload Proof Image</label>
                <input type="file" name="proof_image" accept="image/*" required>
            </div>

            <button type="submit">Submit Complaint</button>
        </form>

        <br>
        <a href="student_dashboard.php">Back to Dashboard</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
