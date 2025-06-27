<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header('Location: adminadd.php');
} else {
    header('Location: profile.php');
}
exit();
