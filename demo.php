<?php
// Main file: run this to see the Library Management System in action.

require_once __DIR__ . '/classes/Book.php';
require_once __DIR__ . '/classes/Member.php';
require_once __DIR__ . '/classes/PremiumMember.php';
require_once __DIR__ . '/classes/Library.php';

$isCli = php_sapi_name() === 'cli';
if (!$isCli) {
    echo '<pre>';
}

$library = new Library("City Central Library");

// Add books
$library->addBook(new Book(1, "Clean Code", "Robert C. Martin", "9780132350884"));
$library->addBook(new Book(2, "The Pragmatic Programmer", "Andrew Hunt", "9780201616224"));
$library->addBook(new Book(3, "Head First PHP & MySQL", "Lynn Beighley", "9780596006303"));
$library->addBook(new Book(4, "Design Patterns", "Erich Gamma", "9780201633610"));

// Register members
$library->registerMember(new Member(1, "Ali Khan", "ali@example.com", "MEM-001"));
$library->registerMember(new PremiumMember(2, "Sara Ahmed", "sara@example.com", "PRM-001", 25));

$library->displayMembers();
$library->displayAllBooks();

// Search
$library->searchBook("clean");
$library->searchBook("martin");

// Issue books
echo PHP_EOL . "=== Issuing Books ===" . PHP_EOL;
$library->issueBook(1, 1);  // Ali borrows Clean Code
$library->issueBook(1, 2);  // Already issued
$library->issueBook(2, 1);  // Ali borrows a second book
$library->issueBook(3, 1);  // Ali reaches his limit (2 books)
$library->issueBook(3, 2);  // Sara (premium) can still borrow

$library->displayAvailableBooks();
$library->displayIssuedBooks();

// Return books
echo PHP_EOL . "=== Returning Books ===" . PHP_EOL;
$library->returnBook(1, 1);
$library->returnBook(4, 1); // Ali never borrowed this

$library->displayAvailableBooks();
$library->displayIssuedBooks();

if (!$isCli) {
    echo '</pre>';
}
