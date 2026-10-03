<?php
require 'db.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $stmt = $conn->prepare(
        'SELECT id, full_name, email, phone, address
         FROM contacts
         WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?
         ORDER BY id ASC'
    );
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare(
        'SELECT id, full_name, email, phone, address
         FROM contacts
         ORDER BY id ASC'
    );
}

$stmt->execute();
$contacts = $stmt->get_result();

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Lists</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Contact Lists</a>
    </div>
</nav>

<main class="container py-4">
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Contact added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Contact updated successfully.</div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Contact deleted successfully.</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <h1 class="h3 mb-0">Contacts</h1>
                <a class="btn btn-primary" href="add.php">Add Contact</a>
            </div>

            <form class="row g-2 mb-3" method="get" action="index.php">
                <div class="col-sm">
                    <label class="visually-hidden" for="search">Search contacts</label>
                    <input class="form-control" type="search" id="search" name="search"
                           placeholder="Search by name, email, or phone"
                           value="<?= escape($search) ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                    <?php if ($search !== ''): ?>
                        <a class="btn btn-outline-secondary" href="index.php">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Address</th>
                        <th scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($contacts->num_rows > 0): ?>
                        <?php while ($contact = $contacts->fetch_assoc()): ?>
                            <tr>
                                <td><?= escape($contact['id']) ?></td>
                                <td><?= escape($contact['full_name']) ?></td>
                                <td><?= escape($contact['email']) ?></td>
                                <td><?= escape($contact['phone']) ?></td>
                                <td><?= escape($contact['address']) ?></td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="edit.php?id=<?= escape($contact['id']) ?>">Edit</a>
                                    <form class="d-inline" action="delete.php" method="post"
                                          onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                        <input type="hidden" name="id" value="<?= escape($contact['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No contacts found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
