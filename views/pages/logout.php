<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page (adjust path if needed)
header("Location: /login");
exit();
?>