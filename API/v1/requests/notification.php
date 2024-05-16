<?php
// notifications.php 
require_once 'db.php';

function sendNotification($responderId, $alertId, $description, $location) {
    global $DB;

    $message = "New Alert! ID: $alertId\nDescription: $description\nLocation: $location";

    $sql = "INSERT INTO tb_notification (responder_id, alert_id, message) VALUES (?, ?, ?)";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("iis", $responderId, $alertId, $message);
    
    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Error sending notification: " . $DB->error);
        return false;
    }
}
