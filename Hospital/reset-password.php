<?php
require_once 'includes/config.php';
session_start();

// Redirect users to the login page
header("Location: index.php");
exit();
?>