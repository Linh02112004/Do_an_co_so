<?php
session_start();
session_destroy();
header("Location: tc_dang_nhap.php");
exit();
?>
