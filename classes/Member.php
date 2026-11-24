<?php

class Member
{
    private int $id;
    private string $name;
    private string $email;
    private string $membershipNumber;
    private array $borrowedBooks = []; // list of Book objects

    public function __construct(int $id, string $name, string $email, string $membershipNumber)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->membershipNumber = $membershipNumber;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getMembershipNumber(): string { return $this->membershipNumber; }
    public function getBorrowedBooks(): array { return $this->borrowedBooks; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setMembershipNumber(string $number): void { $this->membershipNumber = $number; }

    // Maximum books a member can borrow (child classes can change this)
    public function getBorrowLimit(): int
    {
        return 2;
    }

    public function getMemberType(): string
    {
        return 'Regular Member';
    }

    public function canBorrow(): bool
    {
        return count($this->borrowedBooks) < $this->getBorrowLimit();
    }

    public function addBorrowedBook(Book $book): void
    {
        $this->borrowedBooks[$book->getId()] = $book;
    }

    public function removeBorrowedBook(Book $book): void
    {
        unset($this->borrowedBooks[$book->getId()]);
    }

    public function hasBorrowed(Book $book): bool
    {
        return isset($this->borrowedBooks[$book->getId()]);
    }

    public function getDetails(): string
    {
        return "ID: {$this->id} | Name: {$this->name} | Email: {$this->email} | "
             . "Membership No: {$this->membershipNumber} | Type: {$this->getMemberType()} | "
             . "Borrow Limit: {$this->getBorrowLimit()}";
    }
}
