<?php
    error_reporting(E_ALL ^ E_NOTICE);
    //ini_set('session.use_only_cookies','1');
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-cale=1, maximum-scale=1"/>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrp/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0jquery.min.js"></script>
    <script src="https//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js.bootstrap.min.js"></script>
</head>
<body>
<div class="jumbotron">
    <div class="container text-center">
        <h1>School Site</h1>
    </div>
</div>
<nav class="navbar navbar-inverse">
    <div class= "container-fluid">
        <div vlass="navbar-header">
            <button type="button" class="navabr-toggle" data-toggle="collapse" data-target-"#myNavbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
                <li class="active"><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
                <li class="active"><a href="about.php"><span class="glyphicon glyphicon-exclamation-sign"></span> About Us</a></li>
                <li class="active"><a href="contact.php"><span class="glyphicon glyphicon-earphone"></span> Contact Us</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
        <?php
           // ini_set('session.use_only_cookies','1');
            session_start();

            if( isset($_SESSION['user_id']))
            {
                echo '<li><a href="profile.php"><span class="glyphicon glyphicon-briefcase"></span> Profile</a></li>';
                echo '<li><a href="logout.php?Logout=1"><span class="glyphicon glyphicon-off"></span> Logout</a></li>';
            }
            else
            {
                echo '<li><a href="login.php"><span class="glyphicon glyphicon-user"></span> Login</a></li>';
                echo '<li><a href="registration.php"><span class-"glyphicon glyphicon-pencil"></span> Registration</a></li>';
            }
        ?>
            </ul>
        </div>
    </div>
</nav>
</body>
</html>