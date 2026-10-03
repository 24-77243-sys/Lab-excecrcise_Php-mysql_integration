<?php
require 'db.php';

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('A valid contact ID is required.');
}

$stmt = $conn->prepare(
    'SELECT id, full_name, email, phone, address FROM contacts WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$contact = $stmt->get_result()->fetch_assoc();

if (!$contact) {
    http_response_code(404);
    exit('Contact not found.');
}

$fullName = $contact['full_name'];
$email = $contact['email'];
$phone = $contact['phone'];
$address = $contact['address'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($phone === '' || !preg_match('/\A\+?[0-9](?:[0-9\s().-]*[0-9])?\z/', $phone)) {
        $errors[] = 'Enter a valid phone number using digits and optional phone formatting. Negative numbers are not allowed.';
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            'UPDATE contacts SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?'
        );
        $stmt->bind_param('ssssi', $fullName, $email, $phone, $address, $id);
        $stmt->execute();

        header('Location: index.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Contact - Simple Contact List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Simple Contact List</a>
    </div>
</nav>

<main class="container py-4">
    <div class="card shadow-sm form-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Edit Contact</h1>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="edit.php?id=<?= escape($id) ?>">
                <div class="mb-3">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input class="form-control" type="text" id="full_name" name="full_name"
                           maxlength="100" required value="<?= escape($fullName) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email"
                           maxlength="100" required value="<?= escape($email) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input class="form-control" type="text" id="phone" name="phone"
                           maxlength="30" pattern="[+]?[0-9]([0-9 .()\-]*[0-9])?"
                           title="Use digits and optional spaces, +, hyphens, parentheses, or periods. Do not use a negative number."
                           required value="<?= escape($phone) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <textarea class="form-control" id="address" name="address"
                              maxlength="255" rows="3"><?= escape($address) ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save Changes</button>
                <a class="btn btn-outline-secondary" href="index.php">Cancel</a>
            </form>
        </div>
    </div>
</main>
</body>
</html>
