<?php

require_once '../../db.php';

/* $user_id = $_GET[ 'user_id' ] ;
$location = $_GET[ 'location']; */

$alerts = $DB->query( "SELECT * FROM " . PREF_ . "alert" );

$response = [ 'hello' => 'world' ];

/* var_dump($alerts); */

/* select profile from user with user_id =123 */