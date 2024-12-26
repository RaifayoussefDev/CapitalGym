<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];


    // // Database connection details
    // $servername = "localhost"; // Replace with your server name
    // $username = "root"; // Replace with your database username
    // $dbpassword = ""; // Replace with your database password
    // $dbname = "privilage"; // Replace with your database name

    // // Create a new MySQLi connection
    // $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // // Check connection
    // if ($conn->connect_error) {
    //     die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    // }

    require_once('../inc/conn_db.php');

    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn,$password);

    // Prepare SQL query using prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? or matricule = ?");
    $stmt->bind_param('ss', $email , $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if query executed successfully
    if ($result) {
        // Check if a single row was returned
        if ($result->num_rows === 1) {
            // Fetch the user details
            $row = $result->fetch_assoc();

            
            if (password_verify($password, $row['password'])) {
                // Start session and store user details
                $_SESSION['email'] = $email;
                $_SESSION['id'] = $row['id']; // Assuming 'id' is the column name in your 'users' table
                $_SESSION['profil'] = $row['role_id'];
                $_SESSION['nom'] = $row['nom'];
                $_SESSION['prenom'] = $row['prenom'];
                $_SESSION['current_page'] = '';
                $_SESSION['user_insert'] = 0;


                // Return success response
                echo json_encode(['success' => true]);
            } else {
                // Password verification failed
                echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            }
        } else {
            // No user found or multiple users with the same email (unlikely but a check)
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } else {
        // Query execution failed
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    }

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
