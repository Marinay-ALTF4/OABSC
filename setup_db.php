<?php
$conn = new mysqli('127.0.0.1', 'root', '');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($conn->query('CREATE DATABASE IF NOT EXISTS oabsc') === TRUE) {
    echo "Database created successfully\n";
} else {
    echo 'Error: ' . $conn->error . "\n";
}

$conn->close();
?>
