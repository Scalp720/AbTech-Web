<?php
session_start(); // Start or resume the session

// 1. Unset All Session Variables
$_SESSION = array(); // Clear all data in the $_SESSION superglobal

// 2. Destroy the Session
session_destroy(); // Invalidate the session

// 3. (Optional) Clear Session Cookie
// If you're using cookies to store session IDs, you can also delete the cookie:
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Redirect to Login Page
header("Location: login.php"); // Send the user back to the login page
exit(); // Terminate further script execution
