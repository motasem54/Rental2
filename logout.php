<?php
// logout.php
require_once 'config/database.php';
require_once 'core/Auth.php';

$auth = new Auth();
$result = $auth->logout();

// Redirect to login page
header('Location: login.php');
exit();
?>
