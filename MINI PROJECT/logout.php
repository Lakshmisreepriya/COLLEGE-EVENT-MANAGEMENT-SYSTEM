<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
session_destroy();

ob_end_clean();
header('Location: index.php');
exit();
?>
