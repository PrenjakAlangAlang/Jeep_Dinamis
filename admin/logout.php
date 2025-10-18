<?php
session_start();
session_destroy();
// After logging out, redirect back to the public site homepage (index.php in parent folder)
header("Location: ../index.php");
exit;
?>