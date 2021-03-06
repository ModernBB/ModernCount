<?php

if (!file_exists("../config.php")) {
    header("Location: ../installer");
    exit;
}

require_once("../config.php");

session_start();

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

mysql_select_db(DB_NAME, $con);

//If cookie is set, skip login
if (isset($_COOKIE["luna_user_rememberme"])) {
    $id = $_COOKIE["luna_user_rememberme"];
    $getid = mysql_query("SELECT `id` FROM `Users` WHERE `id` = \"$id\"");
    if (mysql_num_rows($getid) == 0) {
        header("Location: logout.php");
        exit;
    }
    $userinforesult = mysql_fetch_assoc($getid); 
    $_SESSION["luna_user"] = $userinforesult["id"];
}

if (isset($_POST["password"]) && isset($_POST["username"])) {
    $username = mysql_real_escape_string($_POST["username"]);
    $password = $_POST["password"];
    $userinfo = mysql_query("SELECT `id`, `user`, `password`, `salt` FROM `Users` WHERE `user` = \"$username\"");
    $userinforesult = mysql_fetch_assoc($userinfo);
    if (mysql_num_rows($userinfo) == 0) {
        header("Location: login.php?login_error=true");
        exit;
    }
    $salt = $userinforesult["salt"];
    $hashedpassword = hash("sha256", $salt . hash("sha256", $password));
    if ($hashedpassword == $userinforesult["password"]) {
        $_SESSION["luna_user"] = $userinforesult["id"];
		if (isset($_POST["rememberme"])) {
            setcookie("luna_user_rememberme", $userinforesult["id"], time()+1209600);
        }
    } else {
        header("Location: login.php?login_error=true");
        exit;
    }
}

if (!isset($_SESSION["luna_user"])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ModernCount &middot; Login</title>
<meta name="robots" content="noindex, nofollow">
<link href="../assets/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<link href="../assets/style.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 40px;
    padding-bottom: 40px;
    background-color: #f8f8f8;
}
.form-signin {
    max-width: 300px;
    padding: 10px 30px 50px;
    margin: 0 auto 20px;
    background-color: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.form-signin .form-signin-heading {
    margin-bottom: 10px;
}
</style>
</head>
<body>
<div class="container">
<form role="form" class="form-signin" method="post">
<h2>ModernCount</h2>
<?php 
if (isset($_GET["login_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Incorrect login.</div>";
} elseif (isset($_GET["logged_out"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Successfully logged out.</div>";
}
?>
<div class="form-group">
<input type="text" class="form-control" id="username" name="username" placeholder="Username">
</div>
<div class="form-group">
<input type="password" class="form-control" id="password" name="password" placeholder="Password">
</div>
<div class="control-group">
<div class="controls">
<label class="checkbox">
<input type="checkbox" id="rememberme" name="rememberme"> Remember Me
</label>
</div>
</div>
<button type="submit" class="btn btn-default pull-right">Login</button>
</form>
</div>
<script src="../assets/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php
} else {
    header("Location: index.php");
    exit;
}
?>