<?php
require_once 'db.php'; // Adjust the path if your db.php is in a different location

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Get responder ID from query parameter
    if (!isset($_GET['responder_id'])) {
        die(json_encode(['error' => 'Missing responder_id parameter']));
    }
    $responderId = intval($_GET['responder_id']);

    // Fetch active alerts for the responder
    $sql = "SELECT a.* FROM tb_alert a
            JOIN respondee r ON a.alert_id = r.alert_id
            WHERE r.responder_id = ? AND a.status = 'ongoing'"; 
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("i", $responderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $alerts = [];
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }

    echo json_encode(['success' => true, 'alerts' => $alerts]);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
