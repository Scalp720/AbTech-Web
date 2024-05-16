<?php

require_once '../../db.php';

$username = $_POST[ 'username' ];
$password = md5( $_POST[ 'password' ] );
$user_type = $_POST[ 'user_type' ];


$result = $DB->query( "SELECT * FROM " . PREF_ . "user WHERE username='$username' AND password='$password'" );

if( $result ) {

}

$userId = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$userId) {
    die(json_encode(["error" => "Invalid user ID"]));
}