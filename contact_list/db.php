<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'contact_list_db';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset('utf8mb4');
