<?php

require_once '../../db.php';

$username = $_POST[ 'username' ];
$password = md5( $_POST[ 'password' ] );


$result = $DB->query( "SELECT * FROM " . PREF_ . "user WHERE username='$username' AND password='$password'" );

if( $result ) {

}