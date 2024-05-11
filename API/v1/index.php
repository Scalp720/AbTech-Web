<?php

  $request = $_REQUEST[ 'request' ];
  $msg = "";
  $response = '';
  if( isset( $request ) && !empty( $request )) {
    if( file_exists( "./requests/$request.php" ) ) {
      require_once "./requests/$request.php";
    } else {
      $msg = 'Unknown request.';
    }
  } else {
    $msg = 'Invalid Request.';
  }


header('Content-Type: application/json');
echo json_encode( [
  'message' => $msg,
  'response' => $response
] );