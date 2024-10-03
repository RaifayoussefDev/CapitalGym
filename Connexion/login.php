<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password using SHA-256 (if your passwords are stored hashed in the database)
    $hashedPassword = hash('sha256', $password);

    // Database connection details
    $servername = "localhost"; // Replace with your server name
    $username = "root"; // Replace with your database username
    $dbpassword = ""; // Replace with your database password
    $dbname = "privilage"; // Replace with your database name

    // Create a new MySQLi connection
    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    $hashedPassword = $conn->real_escape_string($hashedPassword);

    // Prepare SQL query (use prepared statements to prevent SQL injection)
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);

    // Check if query executed successfully
    if ($result) {
        // Check if a single row was returned
        if ($result->num_rows === 1) {
            // Fetch the user details
            $row = $result->fetch_assoc();

            // Start session and store user ID
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $row['id']; // Assuming 'id' is the column name in your 'users' table
            $_SESSION['profil']=$row['role_id'];
            $_SESSION['current_page']='';

            // Return success response
            echo json_encode(['success' => true]);
        } else {
            // Login failed
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } else {
        // Query execution failed
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    }

    // Close database connection
    $conn->close();
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
