<?php

require_once '../../db.php';

/* $user_id = $_GET[ 'user_id' ] ;
$location = $_GET[ 'location']; */

//if((isset($_POST[ 'accelerometerTriggered' ]) && !empty($_POST[ 'accelerometerTriggered' ])) && $_POST[ 'accelerometerTriggered' ] == true ) {
  /* $alerts = $DB->query( "SELECT * FROM " . PREF_ . "alert" );

  $accelerometerTriggered = $_POST[ 'accelerometerTriggered' ];

  $response = [ 'hello' => 'world' ]; */
  $DB->query( "INSERT INTO `tb_drive_mode` (user_id, latitude, longitude, created_at) VALUES(1, '20.1', '22.2', NOW())" );
//}


/* var_dump($alerts); */

/* select profile from user with user_id =123 */