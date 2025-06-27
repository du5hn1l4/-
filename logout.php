<?php
session_start();
session_destroy();
echo 'Вы вышли из системы.';
header("Location: index.php");
exit();
?>
