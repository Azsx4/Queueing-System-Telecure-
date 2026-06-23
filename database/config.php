<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "queue_system_ver2.0";
$admin_password = "TeleCureAdmin123";

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed.");
}
?>