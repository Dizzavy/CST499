<?php
error_reporting(E_ALL ^ E_NOTICE);
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>CST499 Student System</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
<div class="jumbotron">
  <div class="container text-center">
    <h1>CST499 - Student System</h1>
  </div>
</div>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/cst499/index.php">CST499</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="/cst499/index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
        <li><a href="/cst499/about.php"><span class="glyphicon glyphicon-exclamation-sign"></span> About</a></li>
        <li><a href="/cst499/contact.php"><span class="glyphicon glyphicon-earphone"></span> Contact</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if (!empty($_SESSION['user'])): ?>
          <li><a href="/cst499/register_class.php"><span class="glyphicon glyphicon-education"></span> Enroll</a></li>
          <li><a href="/cst499/my_classes.php"><span class="glyphicon glyphicon-briefcase"></span> My Classes</a></li>
          <li><a href="/cst499/waitlist.php"><span class="glyphicon glyphicon-time"></span> Waitlist</a></li>
          <li><a href="/cst499/logout.php?Logout=1"><span class="glyphicon glyphicon-off"></span> Logout (<?=htmlspecialchars($_SESSION['user'])?>)</a></li>
        <?php else: ?>
          <li><a href="/cst499/login.php"><span class="glyphicon glyphicon-user"></span> Login</a></li>
          <li><a href="/cst499/register.php"><span class="glyphicon glyphicon-pencil"></span> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
