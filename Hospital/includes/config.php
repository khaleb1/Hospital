<?php
// Database configuration
$host = 'localhost'; // Database host
$dbname = 'hospital_db'; // Database name
$username = 'root'; // Database username
$password = 'gob3'; // Database password

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to UTF-8
$conn->set_charset("utf8");
?>