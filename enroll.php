<?php
error_reporting(E_ALL ^ E_NOTICE);
session_start();
require 'check.php';       
require 'connect.php';     // creates $conn mysqli instance

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_SESSION['user_id'];

$messages = [];
$errors   = [];

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['course_id'])) {

    $courseId = (int)$_POST['course_id'];
    $semester = $conn->real_escape_string($_POST['semester'] ?? 'Fall 2025');

    // Sanitize
    $courseId = (int)$courseId;

    // Check for DB connection errors
    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    // Check if user already registered
    $stmt = $conn->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $id, $courseId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "You are already registered for this course.";
    } else {
        // Count enrolled
        $stmtCount = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE course_id = ?");
        $stmtCount->bind_param("i", $courseId);
        $stmtCount->execute();
        $stmtCount->bind_result($enrolled);
        $stmtCount->fetch();
        $stmtCount->close();

        // Capacity lookup
        $stmtCap = $conn->prepare("SELECT capacity FROM courses WHERE course_id = ?");
        $stmtCap->bind_param("i", $courseId);
        $stmtCap->execute();
        $stmtCap->bind_result($capacity);
        $stmtCap->fetch();
        $stmtCap->close();

        // Course full → add to waitlist
        if ($capacity > 0 && $enrolled >= $capacity) {

            // Check if already waitlisted
            $stmtWL = $conn->prepare("SELECT 1 FROM waitlist WHERE user_id = ? AND course_id = ?");
            $stmtWL->bind_param("ii", $id, $courseId);
            $stmtWL->execute();
            $stmtWL->store_result();

            if ($stmtWL->num_rows > 0) {
                $errors[] = "You are already on the waitlist for this course.";
            } else {
                $stmtInsertWL = $conn->prepare("INSERT INTO waitlist (user_id, course_id) VALUES (?, ?)");
                $stmtInsertWL->bind_param("ii", $id, $courseId);
                $stmtInsertWL->execute();
                $messages[] = "Course is full. You have been added to the waitlist.";
            }
            $stmtWL->close();

        } else {
            // Register the user
            $stmtReg = $conn->prepare(
                "INSERT INTO registrations (user_id, course_id, semester) VALUES (?, ?, ?)"
            );
            $stmtReg->bind_param("iis", $id, $courseId, $semester);
            $stmtReg->execute();

            $messages[] = "Successfully registered for the course.";
        }
    }
    $stmt->close();
}

// Fetch courses + availability
$courses = [];
$sql = "
    SELECT c.course_id, c.course_code, c.course_name, c.credits, c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.course_id) AS enrolled
    FROM courses c
    ORDER BY c.course_code
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Course Registration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>

<?php include 'master.php'; ?>

<div class="container">
    <h2>Available Courses</h2>

    <?php foreach ($messages as $m): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th><th>Name</th><th>Credits</th><th>Capacity</th><th>Enrolled</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                <td><?php echo htmlspecialchars($c['credits']); ?></td>
                <td><?php echo ($c['capacity'] == 0) ? 'Unlimited' : (int)$c['capacity']; ?></td>
                <td><?php echo (int)$c['enrolled']; ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="course_id" value="<?php echo (int)$c['course_id']; ?>">
                        <input type="hidden" name="semester" value="Fall 2025">
                        <button class="btn btn-primary btn-sm">Register</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
