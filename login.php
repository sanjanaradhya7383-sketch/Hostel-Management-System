<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
    if ($conn->connect_error) {
        die("Database connection failed");
    }

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE LOWER(TRIM(email)) = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (trim($user['password']) === $password) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = strtolower(trim($user['role']));

            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($_SESSION['role'] === 'warden') {
                header("Location: warden_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();

        } else {
            echo "<script>alert('Invalid password');</script>";
        }

    } else {
        echo "<script>alert('Invalid email');</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hostel Complaint System | Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Hostel Complaint Management System</p>

        <form method="post">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Login</button>
            <p class="register-link">
    New student? <a href="register.php">Register here</a>
</p>

        </form>
    </div>
</div>

</body>
</html>
