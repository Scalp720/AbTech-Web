<?php
require_once '../../db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the username and password from the request body
    $username = isset( $_POST['username'] ) ? $_POST['username'] : null;
    $password = isset( $_POST['password'] ) ? md5($_POST['password']) : null;

    if( !$username || !$password ) {
      echo json_encode( [ 'message' => 'Username or password not found.', 'error' => 1 ] );
      exit;
    }

    // Prepare and execute the SQL query to check if the user exists
    $query = "SELECT * FROM " . PREF_ . "user WHERE username='$username' AND password='$password'";
    $result = $DB->query($query);

    // Check if the query was successful and if any rows were returned
    if ($result && $result->num_rows > 0) {
        // User exists, you can perform further actions here
        echo json_encode(array('message' => 'Login successful', 'error' => 0));
    } else {
        // User does not exist or incorrect credentials
        echo json_encode(array('message' => 'Invalid credentials', 'error' => 1));
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'));
}

// Always exit after handling the request
exit;