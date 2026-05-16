<?php
$host     = getenv('DB_HOST')     ?: 'switchyard.proxy.rlwy.net';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASS')     ?: 'eVcyCCDNCocDdWcyFAKYqupisbkSVvXW';
$dbname   = getenv('DB_NAME')     ?: 'railway';
$port     = getenv('DB_PORT')     ?: '37306';

$conn = mysqli_connect($host, $username, $password, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>