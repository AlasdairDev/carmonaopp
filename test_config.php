<?php
require_once 'config.php';
echo "BASE_URL: " . BASE_URL . "<br>";
echo "Environment: " . ($isLocal ? 'LOCAL' : 'PRODUCTION') . "<br>";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'];
?>