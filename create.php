<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    $sql = "SELECT * FROM tb_user WHERE user_id = ?"; // Example query
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $_SESSION['user_type'] = $user['user_type'];

}
// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $user['user_type'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input Validation and Sanitization 
    $requiredFields = ['username', 'password', 'user_type'];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            die(json_encode(["error" => "Missing or empty required field: $field"]));
        }

        // Sanitize input (adjust as needed)
        $data[$field] = htmlspecialchars(strip_tags($_POST[$field]));
    }

    // Prepare data
    $username = $DB->real_escape_string($data['username']);
    $password = md5($data['password'], PASSWORD_DEFAULT); // Use bcrypt or Argon2
    $userType = $DB->real_escape_string($data['user_type']);
    // Start a transaction
    $DB->begin_transaction();

    try {


        // Insert into tb_user
        $userStmt = $DB->prepare("INSERT INTO tb_user (username, password, user_type) VALUES (?, ?, ?)");
        $userStmt->bind_param("sss", $username, $password, $userType); // Corrected parameter order
        $userStmt->execute();

        // Commit the transaction
        $DB->commit();
        $success = "User created successfully";
        // Optional: Redirect back to manageuser.php
        header("Location: manageuser.php"); 
        exit(); 
    } catch (mysqli_sql_exception $e) {
        $DB->rollback(); // Rollback on error
        $error = "Error creating user: " . $e->getMessage();
    }

    // Close statements (optional, as PHP will close them automatically at the end of the script)
    if ($profileStmt) $profileStmt->close();
    if ($userStmt) $userStmt->close();
    if (isset($responderStmt)) $responderStmt->close();
}
?>
<!DOCTYPE html>
<?php require_once 'header.php'; ?>

<div class="container">
    <h2>Create New User</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="user_type">
            <option value="admin">Admin</option>
            <option value="responder">Responder</option>
        </select><br>
        <button type="submit">Create User</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>
