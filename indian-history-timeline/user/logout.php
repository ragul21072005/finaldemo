<?php
include '../config/database.php';

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>s