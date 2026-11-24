<?php

class Book
{
    private int $id;
    private string $title;
    private string $author;
    private string $isbn;
    private bool $available;

    public function __construct(int $id, string $title, string $author, string $isbn, bool $available = true)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->available = $available;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getIsbn(): string { return $this->isbn; }
    public function isAvailable(): bool { return $this->available; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function setAuthor(string $author): void { $this->author = $author; }
    public function setIsbn(string $isbn): void { $this->isbn = $isbn; }
    public function setAvailable(bool $available): void { $this->available = $available; }

    public function getDetails(): string
    {
        $status = $this->available ? 'Available' : 'Issued';
        return "ID: {$this->id} | Title: {$this->title} | Author: {$this->author} | ISBN: {$this->isbn} | Status: {$status}";
    }
}
