<?php
include("header.php");
include('redirect.php');
session_start();
unset($_SESSION["username"]);
unset($_SESSION["admin"]);

header('URL = login.php');
?>
