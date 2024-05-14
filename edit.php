<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Get user ID to edit (assuming the 'id' parameter is passed in the URL)
$userId = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$userId) {
    die(json_encode(["error" => "Invalid user ID"]));
}

// Fetch user data
$sql = "SELECT * FROM tb_user WHERE user_id = ?";
$stmt = $DB->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(["error" => "User not found"]));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Your validation and sanitization logic for updated user data)

    // Update user data in tb_user and tb_profile (using prepared statements)
    $updateSql = "UPDATE tb_user SET
                    username = ?, user_type = ?
                  WHERE user_id = ?";
    $stmtUpdate = $DB->prepare($updateSql);
    $stmtUpdate->bind_param("ssi", $username, $userType, $userId);

    // Update the password if it was changed
    if (!empty($password)) {
        $hashedPassword = md5($password, PASSWORD_DEFAULT);
        $passwordUpdateSql = "UPDATE tb_user SET password = ? WHERE user_id = ?";
        $stmtPasswordUpdate = $DB->prepare($passwordUpdateSql);
        $stmtPasswordUpdate->bind_param("si", $hashedPassword, $userId);
        $stmtPasswordUpdate->execute();
    }
    
    if ($stmtUpdate->execute()) {
        $success = "User updated successfully";
    } else {
        $error = "Error updating user: " . $stmtUpdate->error;
    }
    
    $stmtUpdate->close();

}
?>

<!DOCTYPE html>
<?php require_once 'header.php'; ?>

<div class="container">
    <h2>Edit User (ID: <?php echo $userId; ?>)</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <input type="password" name="password" placeholder="Password" > 
        <select name="user_type">
            <option value="admin" <?php echo ($user['user_type'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            <option value="responder" <?php echo ($user['user_type'] === 'responder') ? 'selected' : ''; ?>>Responder</option>
        </select>
        <button type="submit">Update User</button>
    </form>
</div>
<?php require_once 'footer.php'; ?>
