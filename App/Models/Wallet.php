<?php declare(strict_types=1);

namespace App\Models;

class Wallet
{
    private string $symbol;
    private float $amount;
    private float $averagePrice;
    private string $userId;

    public function __construct(
        string $symbol,
        float  $amount,
        float  $averagePrice,
        string $userId)
    {
        $this->symbol = $symbol;
        $this->amount = $amount;
        $this->averagePrice = $averagePrice;
        $this->userId = $userId;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAveragePrice(): float
    {
        return $this->averagePrice;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function calculateProfitability(float $currentPrice): float
    {
        return (($currentPrice - $this->averagePrice) / $this->averagePrice) * 100;
    }
}