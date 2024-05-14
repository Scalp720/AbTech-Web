<?php
// Check if session already exists
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 80%; max-width: 500px; margin: 20px auto; padding: 20px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        input, select, button { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container"> 
