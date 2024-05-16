<?php
require_once 'db.php';
require_once 'notifications.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Input Validation
    if (!isset($data['user_id']) || !isset($data['latitude']) || !isset($data['longitude']) || !isset($data['description'])) {
        die(json_encode(['error' => 'Missing required parameters']));
    }

    // Sanitization
    $userId = intval($data['user_id']);

    // Validate latitude and longitude (adjust ranges as needed)
    $latitude = floatval($data['latitude']);
    if ($latitude < -90 || $latitude > 90) {
        die(json_encode(['error' => 'Invalid latitude']));
    }
    
    $longitude = floatval($data['longitude']);
    if ($longitude < -180 || $longitude > 180) {
        die(json_encode(['error' => 'Invalid longitude']));
    }

    // Sanitize description (prevent XSS)
    $description = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8'); 

    // Check if user exists (optional)
    $checkUserSql = "SELECT user_id FROM tb_user WHERE user_id = ?";
    $stmtCheckUser = $DB->prepare($checkUserSql);
    $stmtCheckUser->bind_param("i", $userId);
    $stmtCheckUser->execute();
    $result = $stmtCheckUser->get_result();
    if ($result->num_rows == 0) {
        die(json_encode(['error' => 'User not found']));
    }

    // Insert alert into tb_alert (set status to 'new')
    $insertSql = "INSERT INTO tb_alert (user_id, location, latitude, longitude, description, status) 
                   VALUES (?, ?, ?, ?, ?, 'new')";
    $stmt = $DB->prepare($insertSql);
    $stmt->bind_param("isdds", $userId, $data['location'], $latitude, $longitude, $description);

    if ($stmt->execute()) {
        $alertId = $stmt->insert_id;

        // Find nearest responders based on user's location
        $nearestResponders = findNearestResponders($latitude, $longitude); // Implement this function

        // Send notifications to responders
        foreach ($nearestResponders as $responder) {
            sendNotification($responder['user_id'], $alertId, $description, $data['location']);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error inserting alert: ' . $DB->error]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

// Function to find nearest responders based on location (Haversine formula or similar)
function findNearestResponders($alertLatitude, $alertLongitude, $limit = 3) {
    global $DB; // Access the global database connection
    $radius = 100; // Radius in kilometers to search for responders (you can adjust this)

    // SQL Query to find responders within the radius and available
    $sql = "SELECT r.user_id, 
                   o.latitude AS office_lat, o.longitude AS office_lon,
                   (6371 * acos(cos(radians(?)) * cos(radians(o.latitude)) * cos(radians(o.longitude) - radians(?)) 
                        + sin(radians(?)) * sin(radians(o.latitude)))) AS distance
            FROM tb_responder r
            JOIN tb_user u ON r.user_id = u.user_id
            JOIN tb_office o ON r.office_id = o.office_id
            WHERE r.availability = 'available'
            HAVING distance <= ?
            ORDER BY distance ASC
            LIMIT ?";

    $stmt = $DB->prepare($sql);
    $stmt->bind_param("dddi", $alertLatitude, $alertLongitude, $alertLatitude, $radius, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $nearestResponders = [];
    while ($row = $result->fetch_assoc()) {
        $nearestResponders[] = $row;
    }

    return $nearestResponders;
}


// Function to send FCM notification (You'll need the Firebase Admin SDK for PHP)
function sendFCMNotification($fcmToken, $alertId, $description, $location) {
    // ... (Your implementation using Firebase Admin SDK to send notification with alertId and description)
}
