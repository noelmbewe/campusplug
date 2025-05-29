<?php
require_once '../db_connect.php';
if (isset($_POST['collapsed'])) {
    $_SESSION['sidebar_collapsed'] = (bool)$_POST['collapsed'];
}
?>