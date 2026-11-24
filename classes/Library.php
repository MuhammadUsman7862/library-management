<?php

require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Member.php';

class Library
{
    private string $name;
    private array $books = [];   // id => Book
    private array $members = []; // id => Member

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getBooks(): array { return $this->books; }
    public function getMembers(): array { return $this->members; }

    // Add a book
    public function addBook(Book $book): void
    {
        $this->books[$book->getId()] = $book;
        echo "Book added: {$book->getTitle()}" . PHP_EOL;
    }

    // Display all books
    public function displayAllBooks(): void
    {
        echo PHP_EOL . "=== All Books in {$this->name} ===" . PHP_EOL;
        if (empty($this->books)) {
            echo "No books in the library." . PHP_EOL;
            return;
        }
        foreach ($this->books as $book) {
            echo $book->getDetails() . PHP_EOL;
        }
    }

    // Register a member
    public function registerMember(Member $member): void
    {
        $this->members[$member->getId()] = $member;
        echo "Member registered: {$member->getName()} ({$member->getMemberType()})" . PHP_EOL;
    }

    // Search for a book by title, author or ISBN
    public function searchBook(string $keyword): array
    {
        $results = [];
        $needle = strtolower($keyword);
        foreach ($this->books as $book) {
            if (
                str_contains(strtolower($book->getTitle()), $needle) ||
                str_contains(strtolower($book->getAuthor()), $needle) ||
                str_contains(strtolower($book->getIsbn()), $needle)
            ) {
                $results[] = $book;
            }
        }

        echo PHP_EOL . "=== Search results for \"{$keyword}\" ===" . PHP_EOL;
        if (empty($results)) {
            echo "No books found." . PHP_EOL;
        } else {
            foreach ($results as $book) {
                echo $book->getDetails() . PHP_EOL;
            }
        }
        return $results;
    }

    // Issue a book to a member
    public function issueBook(int $bookId, int $memberId): bool
    {
        if (!isset($this->books[$bookId])) {
            echo "Error: Book not found." . PHP_EOL;
            return false;
        }
        if (!isset($this->members[$memberId])) {
            echo "Error: Member not found." . PHP_EOL;
            return false;
        }

        $book = $this->books[$bookId];
        $member = $this->members[$memberId];

        if (!$book->isAvailable()) {
            echo "Sorry, \"{$book->getTitle()}\" is already issued." . PHP_EOL;
            return false;
        }
        if (!$member->canBorrow()) {
            echo "{$member->getName()} has reached the borrow limit of {$member->getBorrowLimit()} books." . PHP_EOL;
            return false;
        }

        $book->setAvailable(false);
        $member->addBorrowedBook($book);
        echo "\"{$book->getTitle()}\" issued to {$member->getName()}." . PHP_EOL;
        return true;
    }

    // Return a book
    public function returnBook(int $bookId, int $memberId): bool
    {
        if (!isset($this->books[$bookId]) || !isset($this->members[$memberId])) {
            echo "Error: Book or member not found." . PHP_EOL;
            return false;
        }

        $book = $this->books[$bookId];
        $member = $this->members[$memberId];

        if (!$member->hasBorrowed($book)) {
            echo "{$member->getName()} did not borrow \"{$book->getTitle()}\"." . PHP_EOL;
            return false;
        }

        $book->setAvailable(true);
        $member->removeBorrowedBook($book);
        echo "\"{$book->getTitle()}\" returned by {$member->getName()}." . PHP_EOL;
        return true;
    }

    // Display available books
    public function displayAvailableBooks(): void
    {
        echo PHP_EOL . "=== Available Books ===" . PHP_EOL;
        $found = false;
        foreach ($this->books as $book) {
            if ($book->isAvailable()) {
                echo $book->getDetails() . PHP_EOL;
                $found = true;
            }
        }
        if (!$found) {
            echo "No books are available right now." . PHP_EOL;
        }
    }

    // Display issued books and who has them
    public function displayIssuedBooks(): void
    {
        echo PHP_EOL . "=== Issued Books ===" . PHP_EOL;
        $found = false;
        foreach ($this->members as $member) {
            foreach ($member->getBorrowedBooks() as $book) {
                echo $book->getDetails() . " | Issued to: {$member->getName()}" . PHP_EOL;
                $found = true;
            }
        }
        if (!$found) {
            echo "No books are currently issued." . PHP_EOL;
        }
    }

    // Display all members
    public function displayMembers(): void
    {
        echo PHP_EOL . "=== Registered Members ===" . PHP_EOL;
        if (empty($this->members)) {
            echo "No members registered." . PHP_EOL;
            return;
        }
        foreach ($this->members as $member) {
            echo $member->getDetails() . PHP_EOL;
        }
    }
}
