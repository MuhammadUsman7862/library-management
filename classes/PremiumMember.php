<?php

require_once __DIR__ . '/Member.php';

// PremiumMember inherits from Member
class PremiumMember extends Member
{
    private int $discountPercent;

    public function __construct(int $id, string $name, string $email, string $membershipNumber, int $discountPercent = 20)
    {
        parent::__construct($id, $name, $email, $membershipNumber);
        $this->discountPercent = $discountPercent;
    }

    public function getDiscountPercent(): int { return $this->discountPercent; }
    public function setDiscountPercent(int $discount): void { $this->discountPercent = $discount; }

    // Premium members can borrow more books
    public function getBorrowLimit(): int
    {
        return 5;
    }

    public function getMemberType(): string
    {
        return 'Premium Member';
    }

    public function getDetails(): string
    {
        return parent::getDetails() . " | Discount: {$this->discountPercent}%";
    }
}
