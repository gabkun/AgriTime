<?php
// logout.php
session_start();

// clear session
session_unset();
session_destroy();

// redirect to login page
header("Location: page/Loginpage.php");
exit();
?>
