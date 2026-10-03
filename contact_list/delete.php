<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('This page only accepts contact deletion requests.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('A valid contact ID is required.');
}

$stmt = $conn->prepare('DELETE FROM contacts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: index.php?deleted=1');
exit;
