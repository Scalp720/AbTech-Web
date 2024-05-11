<?php

  $request = $_REQUEST[ 'request' ];

  if( isset( $request ) && !empty( $request )) {
    if( file_exists( "./requests/$request.php" ) ) {
      require_once "./requests/$request.php";
    } else {
      echo 'Unknown request.';
    }
  } else {
    echo 'Invalid Request.';
  }