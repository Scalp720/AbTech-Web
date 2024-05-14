<?php
session_start();
require_once 'db.php';

// Fetch user data based on session user_id (using prepared statements)
$user = null; // Initialize to null
if (isset($_SESSION['user_id'])) {
    $stmt = $DB->prepare("SELECT * FROM tb_user WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Error fetching user data");
    }
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || ($user !== null && $user['user_type'] !== 'admin')) { 
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

// Handle CRUD actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $userId = isset($_GET['id']) ? intval($_GET['id']) : null; 

    // DELETE
    if ($action === 'delete' && $userId !== null) {
        // ... (your code to delete the user after confirmation)
    }
}
?>
<!DOCTYPE html>
<?php require_once 'header.php'; ?>

<div class="container">
    <h2>User Management</h2>

    <h3>All Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>

        <?php
        // READ (All Users)
        $sql = "SELECT u.user_id, u.username,u.user_type FROM tb_user u";
        $result = $DB->query($sql);

        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row["user_id"]; ?></td>
                <td><?php echo $row["username"]; ?></td>
                <td><?php echo $row["user_type"]; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row["user_id"]; ?>">Edit</a> | 
                    <a href="deleteuser.php?id=<?php echo $row["user_id"]; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3><a href="create.php">Create New User</a></h3>
</div>

<?php require_once 'footer.php'; ?>
