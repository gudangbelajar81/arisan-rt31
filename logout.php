<?php
session_start();
session_destroy();
header("Location: peserta.php");
exit();
?>
