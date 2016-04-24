<?php
require_once('login_config.php');
require_once('utils.php');

$user_info->logout();
header('Location: index.php'); 
?>
