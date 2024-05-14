<?php
session_start(); // Start or resume the session
require_once 'db.php'; // Connect to the database
$error = '';
$success = '';
// Check if the user is NOT logged in AND it's NOT a POST request (login attempt)
if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php"); // Redirect to login only if not logged in AND not posting login data
    exit();
} 

// If logged in, fetch user data
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT * FROM tb_user WHERE user_id = ?"; // Example query
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user || $user['user_type'] !== 'admin') {
      header("Location: login.php"); // Redirect to login if not logged in or not an admin
      exit();
    }
}
// Check if a success message is set (after login)
if (isset($_SESSION['login_success'])) {
    $loginSuccess = $_SESSION['login_success'];
    unset($_SESSION['login_success']); // Remove the success message after displaying it
}
?>

<!DOCTYPE html>
<?php require_once 'header.php'; ?>
<div class="container">
    <?php if ($loginSuccess): ?>
        <div class="success"><?php echo $loginSuccess; ?></div>
    <?php endif; ?>

    <?php if (isset($user)): ?>
        <h2>Welcome, <?php echo $user['username']; ?>!</h2>
        <a href="logout.php">Logout</a> 
        <a href="manageuser.php">Manage Users</a>
        <a href="view_alerts.php">Alerts</a>
    <?php endif; ?>

</div>
<?php require_once 'footer.php'; ?>
