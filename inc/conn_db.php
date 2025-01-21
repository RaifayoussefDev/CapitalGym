<?php
// $servername = "localhost";
// $username = "root";
// $password = ""; // Replace with your password
// $dbname = "privilage";
$servername = "51.77.194.236";
$username = "admin";
$password = "C@p1t@l$0ft2022"; // Replace with your password
$dbname = "privilage";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
};
