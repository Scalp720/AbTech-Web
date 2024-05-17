<?php
header('Content-Type: application/json');
// Check if the 'request' parameter is set in the POST or GET request
if (isset($_REQUEST['request']) && !empty($_REQUEST['request'])) {
    // Extract the request parameter
    $request = $_REQUEST['request'];
    
    // Define the directory where the request files are located
    $requestDirectory = "./requests/";
    
    // Define the path to the requested file
    $requestFile = $requestDirectory . $request . ".php";

    // Check if the requested file exists
    if (file_exists($requestFile)) {
        // Include the requested file
        require_once $requestFile;
    } else {
        // Requested file does not exist
        $msg = 'Unknown request.';
        $response = '';
    }
} else {
    // 'request' parameter is not set or empty
    $msg = 'Invalid request.';
    $response = '';
}
/* 
// Set the Content-Type header to indicate JSON response


// Encode the response as JSON and output
echo json_encode([
    'message' => $msg,
    'response' => $response
]); */
exit;