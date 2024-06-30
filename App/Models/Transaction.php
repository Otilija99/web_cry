<?php

namespace App\Models;

use Carbon\Carbon;

class Transaction
{
    private int $userId;
    private string $kind;
    private string $symbol;
    private float $price;
    private float $quantity;
    private Carbon $createdAt;


    public function __construct(
        int     $userId,
        string  $kind,
        string  $symbol,
        float   $price,
        float   $quantity,
        ?Carbon $createdAt = null)
    {
        $this->userId = $userId;
        $this->kind = $kind;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}