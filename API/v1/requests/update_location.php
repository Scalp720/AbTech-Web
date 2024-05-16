// update_location.php
<?php
require_once '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($data['user_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
        die(json_encode(['error' => 'Missing parameters']));
    }
    
    // Assuming you are updating the user's location
    $sql = "UPDATE tb_user SET latitude = ?, longitude = ? WHERE user_id = ?";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("ddi", $data['latitude'], $data['longitude'], $data['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
