<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get user ID to delete
$userId = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($userId) {
    // Check if user exists (optional, for better error handling)
    $checkSql = "SELECT user_id FROM tb_user WHERE user_id = ?";
    $stmtCheck = $DB->prepare($checkSql);
    $stmtCheck->bind_param("i", $userId);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    if ($result->num_rows == 0) {
        die("User not found.");
    }

    // Begin transaction (important for data integrity)
    $DB->begin_transaction();

    try {
        // 1. Delete from tb_responder (if applicable)
        $responderSql = "DELETE FROM tb_responder WHERE user_id = ?";
        $stmtResponder = $DB->prepare($responderSql);
        $stmtResponder->bind_param("i", $userId);
        $stmtResponder->execute();

        // 2. Delete from tb_user
        $userSql = "DELETE FROM tb_user WHERE user_id = ?";
        $stmtUser = $DB->prepare($userSql);
        $stmtUser->bind_param("i", $userId);
        $stmtUser->execute();

        // 3. Delete from tb_profile (due to ON DELETE CASCADE, this should happen automatically)
        // You might want to check if the profile was deleted successfully

        // Commit the transaction
        $DB->commit();

        header("Location: manageuser.php?success=User+deleted+successfully");
        exit();
    } catch (mysqli_sql_exception $e) {
        $DB->rollback(); // Rollback in case of an error
        die("Error deleting user: " . $e->getMessage());
    } finally {
        // Close statements (optional, as PHP will close them automatically at the end of the script)
        if ($stmtResponder) $stmtResponder->close();
        if ($stmtUser) $stmtUser->close();
        if ($stmtCheck) $stmtCheck->close();
    }
} else {
    die("Invalid user ID");
}

$DB->close();
