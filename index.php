<?php
// Library Management System - web page (HTML + CSS front end)
// Classes must be loaded BEFORE session_start() so the Library object can be stored in the session.

require_once __DIR__ . '/classes/Book.php';
require_once __DIR__ . '/classes/Member.php';
require_once __DIR__ . '/classes/PremiumMember.php';
require_once __DIR__ . '/classes/Library.php';

session_start();

/* ---------- Helper functions ---------- */

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// The Library methods print messages with echo. This captures that text so we can show it nicely.
function capture(callable $fn): array
{
    ob_start();
    $result = $fn();
    $message = trim(ob_get_clean());
    return [$result !== false, $message];
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function nextId(array $items): int
{
    return $items ? max(array_keys($items)) + 1 : 1;
}

function seedLibrary(): Library
{
    $lib = new Library('City Central Library');
    ob_start();
    $lib->addBook(new Book(1, 'Clean Code', 'Robert C. Martin', '9780132350884'));
    $lib->addBook(new Book(2, 'The Pragmatic Programmer', 'Andrew Hunt', '9780201616224'));
    $lib->addBook(new Book(3, 'Head First PHP & MySQL', 'Lynn Beighley', '9780596006303'));
    $lib->addBook(new Book(4, 'Design Patterns', 'Erich Gamma', '9780201633610'));
    $lib->registerMember(new Member(1, 'Ali Khan', 'ali@example.com', 'MEM-001'));
    $lib->registerMember(new PremiumMember(2, 'Sara Ahmed', 'sara@example.com', 'PRM-001', 25));
    ob_end_clean();
    return $lib;
}

/* ---------- Load the library ---------- */

if (!isset($_SESSION['library']) || !($_SESSION['library'] instanceof Library)) {
    $_SESSION['library'] = seedLibrary();
}
$library = $_SESSION['library'];

/* ---------- Handle form actions ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_book':
            $title  = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $isbn   = trim($_POST['isbn'] ?? '');
            if ($title === '' || $author === '' || $isbn === '') {
                flash('error', 'Please fill in title, author and ISBN.');
                break;
            }
            $duplicate = false;
            foreach ($library->getBooks() as $b) {
                if ($b->getIsbn() === $isbn) { $duplicate = true; }
            }
            if ($duplicate) {
                flash('error', 'A book with this ISBN already exists.');
                break;
            }
            [$ok, $msg] = capture(function () use ($library, $title, $author, $isbn) {
                return $library->addBook(new Book(nextId($library->getBooks()), $title, $author, $isbn));
            });
            flash('success', $msg);
            break;

        case 'register_member':
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $type  = $_POST['type'] ?? 'regular';
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Please enter a name and a valid email.');
                break;
            }
            $id = nextId($library->getMembers());
            if ($type === 'premium') {
                $member = new PremiumMember($id, $name, $email, sprintf('PRM-%03d', $id), 20);
            } else {
                $member = new Member($id, $name, $email, sprintf('MEM-%03d', $id));
            }
            [$ok, $msg] = capture(function () use ($library, $member) {
                return $library->registerMember($member);
            });
            flash('success', $msg);
            break;

        case 'issue_book':
            $bookId   = (int)($_POST['book_id'] ?? 0);
            $memberId = (int)($_POST['member_id'] ?? 0);
            [$ok, $msg] = capture(function () use ($library, $bookId, $memberId) {
                return $library->issueBook($bookId, $memberId);
            });
            flash($ok ? 'success' : 'error', $msg);
            break;

        case 'return_book':
            $bookId   = (int)($_POST['book_id'] ?? 0);
            $memberId = (int)($_POST['member_id'] ?? 0);
            [$ok, $msg] = capture(function () use ($library, $bookId, $memberId) {
                return $library->returnBook($bookId, $memberId);
            });
            flash($ok ? 'success' : 'error', $msg);
            break;

        case 'reset':
            $_SESSION['library'] = seedLibrary();
            flash('success', 'Library reset to the sample data.');
            break;
    }

    header('Location: index.php');
    exit;
}

/* ---------- Prepare data for the page ---------- */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$books   = $library->getBooks();
$members = $library->getMembers();

$availableBooks = array_filter($books, fn($b) => $b->isAvailable());
$issuedRows = [];
foreach ($members as $m) {
    foreach ($m->getBorrowedBooks() as $b) {
        $issuedRows[] = ['book' => $b, 'member' => $m];
    }
}

$query = trim($_GET['q'] ?? '');
$searchResults = null;
if ($query !== '') {
    ob_start();
    $searchResults = $library->searchBook($query);
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($library->getName()) ?> - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="container topbar-inner">
        <div class="brand">
            <span class="brand-icon">&#128214;</span>
            <div>
                <h1><?= h($library->getName()) ?></h1>
                <p>Library Management System</p>
            </div>
        </div>
        <form method="post" onsubmit="return confirm('Reset all data to the sample data?');">
            <input type="hidden" name="action" value="reset">
            <button type="submit" class="btn btn-light">Reset data</button>
        </form>
    </div>
</header>

<main class="container">

    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <section class="stats">
        <div class="stat">
            <span class="stat-number"><?= count($books) ?></span>
            <span class="stat-label">Total books</span>
        </div>
        <div class="stat stat-green">
            <span class="stat-number"><?= count($availableBooks) ?></span>
            <span class="stat-label">Available</span>
        </div>
        <div class="stat stat-amber">
            <span class="stat-number"><?= count($issuedRows) ?></span>
            <span class="stat-label">Issued</span>
        </div>
        <div class="stat stat-purple">
            <span class="stat-number"><?= count($members) ?></span>
            <span class="stat-label">Members</span>
        </div>
    </section>

    <div class="layout">

        <!-- Left column: forms -->
        <aside class="sidebar">

            <div class="card">
                <h2>Issue a book</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="issue_book">
                    <label>Book
                        <select name="book_id" required <?= $availableBooks ? '' : 'disabled' ?>>
                            <?php if (!$availableBooks): ?>
                                <option value="">No books available</option>
                            <?php endif; ?>
                            <?php foreach ($availableBooks as $b): ?>
                                <option value="<?= $b->getId() ?>"><?= h($b->getTitle()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Member
                        <select name="member_id" required <?= $members ? '' : 'disabled' ?>>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m->getId() ?>"><?= h($m->getName()) ?> (<?= h($m->getMemberType()) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary" <?= ($availableBooks && $members) ? '' : 'disabled' ?>>Issue book</button>
                </form>
            </div>

            <div class="card">
                <h2>Add a book</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="add_book">
                    <label>Title
                        <input type="text" name="title" placeholder="e.g. Refactoring" required>
                    </label>
                    <label>Author
                        <input type="text" name="author" placeholder="e.g. Martin Fowler" required>
                    </label>
                    <label>ISBN
                        <input type="text" name="isbn" placeholder="e.g. 9780134757599" required>
                    </label>
                    <button type="submit" class="btn btn-primary">Add book</button>
                </form>
            </div>

            <div class="card">
                <h2>Register a member</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="register_member">
                    <label>Name
                        <input type="text" name="name" placeholder="Full name" required>
                    </label>
                    <label>Email
                        <input type="email" name="email" placeholder="name@example.com" required>
                    </label>
                    <label>Membership type
                        <select name="type">
                            <option value="regular">Regular (up to 2 books)</option>
                            <option value="premium">Premium (up to 5 books)</option>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary">Register member</button>
                </form>
            </div>

        </aside>

        <!-- Right column: lists -->
        <div class="content">

            <div class="card">
                <h2>Search books</h2>
                <form method="get" class="search">
                    <input type="text" name="q" value="<?= h($query) ?>" placeholder="Search by title, author or ISBN">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($query !== ''): ?>
                        <a href="index.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </form>

                <?php if ($searchResults !== null): ?>
                    <p class="muted">
                        <?= count($searchResults) ?> result(s) for "<strong><?= h($query) ?></strong>"
                    </p>
                    <?php if ($searchResults): ?>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>Title</th><th>Author</th><th>ISBN</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($searchResults as $b): ?>
                                    <tr>
                                        <td><?= h($b->getTitle()) ?></td>
                                        <td><?= h($b->getAuthor()) ?></td>
                                        <td><?= h($b->getIsbn()) ?></td>
                                        <td>
                                            <span class="badge <?= $b->isAvailable() ? 'badge-green' : 'badge-amber' ?>">
                                                <?= $b->isAvailable() ? 'Available' : 'Issued' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>All books</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>ID</th><th>Title</th><th>Author</th><th>ISBN</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if (!$books): ?>
                            <tr><td colspan="5" class="empty">No books yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($books as $b): ?>
                            <tr>
                                <td><?= $b->getId() ?></td>
                                <td class="strong"><?= h($b->getTitle()) ?></td>
                                <td><?= h($b->getAuthor()) ?></td>
                                <td><?= h($b->getIsbn()) ?></td>
                                <td>
                                    <span class="badge <?= $b->isAvailable() ? 'badge-green' : 'badge-amber' ?>">
                                        <?= $b->isAvailable() ? 'Available' : 'Issued' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Issued books</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Title</th><th>Issued to</th><th></th></tr></thead>
                        <tbody>
                        <?php if (!$issuedRows): ?>
                            <tr><td colspan="3" class="empty">No books are issued right now.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($issuedRows as $row): ?>
                            <tr>
                                <td class="strong"><?= h($row['book']->getTitle()) ?></td>
                                <td><?= h($row['member']->getName()) ?></td>
                                <td class="right">
                                    <form method="post">
                                        <input type="hidden" name="action" value="return_book">
                                        <input type="hidden" name="book_id" value="<?= $row['book']->getId() ?>">
                                        <input type="hidden" name="member_id" value="<?= $row['member']->getId() ?>">
                                        <button type="submit" class="btn btn-outline btn-small">Return</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Members</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Membership no.</th><th>Type</th><th>Borrowed</th></tr></thead>
                        <tbody>
                        <?php if (!$members): ?>
                            <tr><td colspan="5" class="empty">No members yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="strong"><?= h($m->getName()) ?></td>
                                <td><?= h($m->getEmail()) ?></td>
                                <td><?= h($m->getMembershipNumber()) ?></td>
                                <td>
                                    <span class="badge <?= $m instanceof PremiumMember ? 'badge-purple' : 'badge-grey' ?>">
                                        <?= h($m->getMemberType()) ?>
                                    </span>
                                </td>
                                <td><?= count($m->getBorrowedBooks()) ?> / <?= $m->getBorrowLimit() ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">Library Management System &middot; PHP OOP project</div>
</footer>

</body>
</html>
