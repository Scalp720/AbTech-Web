<?php
require_once 'db.php'; // Adjust the path if your db.php is in a different location

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Input Validation
    if (!isset($data['responder_id']) || !isset($data['alert_id'])) {
        die(json_encode(['error' => 'Missing parameters']));
    }
    
    $responderId = intval($data['responder_id']);
    $alertId = intval($data['alert_id']);

    // Update tb_alert to ongoing
    $sql = "UPDATE tb_alert SET status = 'ongoing' WHERE alert_id = ?";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("i", $alertId);

    if ($stmt->execute()) {
        // Create a new entry in tb_respondee
        $sql2 = "INSERT INTO respondee (responder_id, alert_id, response_time, status) 
                 VALUES (?, ?, NOW(), 'accepted')";
        $stmt2 = $DB->prepare($sql2);
        $stmt2->bind_param("ii", $responderId, $alertId);
        $stmt2->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $DB->error]); // Provide detailed error
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

// (Close the statements and database connection if you're not doing it elsewhere)
