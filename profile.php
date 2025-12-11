<?php
    error_reporting(E_ALL^E_NOTICE);
    session_start();
    require 'check.php';
    require 'connect.php';

    $id = $_SESSION['user_id'];

    $db = new Connect();

    $user = $db->executePreparedSelect(
        "SELECT email, userName FROM user WHERE user_id = ?", 
        [$id]
    );
    
    $employee = $user[0]?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> Profile Page </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'master.php';?>

    <div class="container text-center">
    <h2>Welcome, <?= htmlspecialchars($employee['userName']) ?>!</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
    <p><strong>First Name:</strong> <?= htmlspecialchars($employee['userName']) ?></p>
    </div>

<?php include_once 'footer.php';?>
</body>
</html>
