<?php

// Local base URL
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $base_url = 'http://localhost/small_crowd/';
}
else {
    $base_url = 'http://small_crowd.xyz/';
}

// Cron Token
$cron_token = file_get_contents('auth.php');    
if (is_dev()) {
    $cron_token = '1234';
}
else {
    $cron_token = file_get_contents('auth.php');    
}

// Taxes
$route = 'cron/';
echo file_get_contents($base_url . $route . $cron_token);