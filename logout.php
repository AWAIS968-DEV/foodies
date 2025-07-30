<?php
require_once 'includes/auth.php';

// Log the user out
$auth->logout();

// The logout method will handle the redirection, but just in case:
header('Location: login.php');
exit();
?>
