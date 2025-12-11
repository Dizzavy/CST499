<?php
    error_reporting(E_ALL ^ E_NOTICE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> Registration Page </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>

<?php include 'master.php';?>

    <div class="conatainer text-center">
        <form action="enroll.php" method ="POST">
            <input type="enroll" name="email" placeholder="Email" required><br>
            <button type="submit">Register</button>
        </form>
    </div>

<?php include_once 'footer.php';?>
</body>
</html>