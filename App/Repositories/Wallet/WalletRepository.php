<?php declare(strict_types=1);

namespace App\Repositories;

use App\Services\SqliteService;
use App\Models\Wallet;

class WalletRepository
{
    private SqliteService $database;

    public function __construct(SqliteService $database)
    {
        $this->database = $database;
    }

    public function getUserWallets(int $userId): array
    {
        $results = $this->database->findByUserId('wallets', $userId);
        $wallets = [];
        foreach ($results as $result) {
            if ((float)$result['amount'] > 0) {
                $wallets[] = new Wallet(
                    $result['symbol'],
                    (float)$result['amount'],
                    (float)$result['average_price'],
                    $result['user_id']
                );
            }
        }
        return $wallets;
    }

    public function updateWallet(int $userId, string $symbol, float $amount, float $averagePrice): void
    {
        $this->database->update(
            'wallets',
            [
                'amount' => $amount,
                'average_price' => $averagePrice
            ],
            [
                'user_id' => $userId,
                'symbol' => $symbol
            ]);
    }

    public function createWallet(string $symbol, float $amount, float $averagePrice, int $userId): void
    {
        $this->database->create(
            'wallets',
            [
                'symbol' => $symbol,
                'amount' => $amount,
                'average_price' => $averagePrice,
                'user_id' => $userId
            ],
        );
    }
}