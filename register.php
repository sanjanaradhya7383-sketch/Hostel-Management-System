<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['register_submit'])) {

    $conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
    if ($conn->connect_error) {
        die("Database connection failed");
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name', '$email', '$password', 'student')";

    if ($conn->query($sql)) {
        echo "<script>alert('Registration successful! Please login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Email already exists!');</script>";
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Student Registration</h2>
        <p class="subtitle">Create your hostel account</p>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="register_submit" value="1">

           
    <div class="input-group">
        <label>Name</label>
        <input type="text" name="name" required>
    </div>

    <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>

    <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <button type="submit">Register</button>

</form>


            
    </div>
</div>

</body>
</html>
