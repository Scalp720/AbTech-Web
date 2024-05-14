<?php
session_start();

$loginError = '';
$loginSuccess = '';

// If already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    $loginSuccess = "You are already logged in.";
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include your database connection file
    require_once 'db.php';

    // Check for database connection errors:
    if ($DB->connect_error) {
        $loginError = "Database connection failed: " . $DB->connect_error; 
    } else {
         echo "Connected successfully!";

        $username = $DB->real_escape_string($_POST['username']);
        $password = $DB->real_escape_string($_POST['password']);

        // Fetch user data based on username (using prepared statements)
        $sql = "SELECT * FROM tb_user WHERE username = ?";
        $stmt = $DB->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if a user was found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (md5($password) == $user['password']) {
                if ($user['user_type'] === 'admin') {
                    // Successful login
                    $_SESSION['user_id'] = $user['user_id'];
                    header("Location: index.php"); 
                    session_regenerate_id(true);
                    exit();
                } else {
                    $loginError = "Not authorized as admin.";
                }
            } else {
                $loginError = "Invalid credentials.";
            }
        } else {
            $loginError = "User not found.";
        }
    }
    
    // Close the database connection
    $DB->close();
}
?>

<!DOCTYPE html>
<?php require_once 'header.php'; ?>
<div class="container">
    <h2>Admin Login</h2>

    <?php if ($loginError): ?>
        <div class="error"><?php echo $loginError; ?></div>
    <?php endif; ?>

    <?php if ($loginSuccess): ?>
        <div class="success"><?php echo $loginSuccess; ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
<?php require_once 'footer.php'; ?>
