<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "hostel_complaint_system");
if ($conn->connect_error) {
    die("DB Connection failed");
}

/* ==========================
   INCLUDE MAIL SERVICE
========================== */
require_once 'send_email.php';

$successMsg = $errorMsg = "";

/* ==========================
   SEND WARNING MAIL
========================== */
if (isset($_POST['send_warning'])) {

    $complaintId = $_POST['complaint_id'];
    $studentName = $_POST['student_name'];
    $category    = $_POST['category'];
    $minutes     = $_POST['minutes'];

    $wardenEmail = "sanjanabodapatibodapati@gmail.com";

    $subject = "⚠ Complaint Delay Warning";

    $body = "
        <h3>Hostel Complaint Warning</h3>
        <p><b>Complaint ID:</b> $complaintId</p>
        <p><b>Student:</b> $studentName</p>
        <p><b>Category:</b> $category</p>
        <p><b>Pending Since:</b> $minutes minutes</p>
        <p>
            This complaint is still unresolved.
            Please take immediate action.
        </p>
        <p style='color:gray;font-size:13px;'>
            (Demo note: 1 minute = 1 day)
        </p>
    ";

    if (sendEmail($wardenEmail, $subject, $body)) {
        $successMsg = "Warning mail successfully sent to Warden.";
    } else {
        $errorMsg = "Mail failed. Please check SMTP configuration.";
    }
}

/* ==========================
   STATISTICS
========================== */
$total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$raised = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status='Raised'")->fetch_assoc()['c'];
$progress = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status='In Progress'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status='Resolved'")->fetch_assoc()['c'];

/* ==========================
   FETCH COMPLAINTS
========================== */
$complaints = $conn->query("
    SELECT c.*, u.name AS student
    FROM complaints c
    JOIN users u ON c.student_id = u.user_id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #2b5876, #4e4376);
            color: #fff;
        }
        header {
            padding: 20px;
            background: rgba(0,0,0,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: #fff;
            text-decoration: none;
            background: #ff4d4d;
            padding: 8px 14px;
            border-radius: 6px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
            gap: 20px;
            padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.15);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .table-box { padding: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.1);
        }
        th, td {
            padding: 14px;
            text-align: center;
        }
        th {
            background: rgba(0,0,0,0.3);
        }
        .status {
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: bold;
        }
        .Raised { background: #ff4d4d; }
        .In\ Progress { background: #ffb300; }
        .Resolved { background: #00c853; }
        button {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            background: #ff9800;
            color: #fff;
            cursor: pointer;
        }
    </style>
</head>

<body>

<header>
    <h1>Administrator Dashboard</h1>
    <a href="logout.php">Logout</a>
</header>

<?php if ($successMsg): ?>
<p style="color:#00e676;text-align:center;"><?= $successMsg ?></p>
<?php endif; ?>

<?php if ($errorMsg): ?>
<p style="color:#ff5252;text-align:center;"><?= $errorMsg ?></p>
<?php endif; ?>

<div class="stats">
    <div class="card"><h2><?= $total ?></h2><p>Total</p></div>
    <div class="card"><h2><?= $raised ?></h2><p>Raised</p></div>
    <div class="card"><h2><?= $progress ?></h2><p>In Progress</p></div>
    <div class="card"><h2><?= $resolved ?></h2><p>Resolved</p></div>
</div>

<div class="table-box">
<table>
<tr>
    <th>Student</th>
    <th>Category</th>
    <th>Description</th>
    <th>Status</th>
    <th>Minutes Pending</th>
    <th>Admin Action</th>
</tr>

<?php while ($c = $complaints->fetch_assoc()):
    $diffSeconds = time() - strtotime($c['created_at']);
    $minutes = max(0, floor($diffSeconds / 60));
?>
<tr>
    <td><?= $c['student'] ?></td>
    <td><?= $c['category'] ?></td>
    <td><?= $c['description'] ?></td>
    <td>
        <span class="status <?= $c['status'] ?>">
            <?= $c['status'] ?>
        </span>
    </td>
   <td>
    <?php if ($c['status'] === 'Resolved'): ?>
        <span style="color:#00e676;font-weight:bold;">Resolved</span>
    <?php else: ?>
        <?= $minutes ?> mins
    <?php endif; ?>
</td>

    <td>
        <form method="POST">
            <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
            <input type="hidden" name="student_name" value="<?= $c['student'] ?>">
            <input type="hidden" name="category" value="<?= $c['category'] ?>">
            <input type="hidden" name="minutes" value="<?= $minutes ?>">
            <button type="submit" name="send_warning">
                Send Warning
            </button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>
</div>

</body>
</html>
