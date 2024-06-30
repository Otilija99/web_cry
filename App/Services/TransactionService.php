<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class TransactionService
{
    private function getUserTransaction(int $userId): array
    {
        $results = (new SqliteService())->findByUserId('transactions', $userId);
        $transactions = [];
        foreach ($results as $result) {
            $transactions[] = new Transaction(
                $result['user_id'],
                $result['kind'],
                $result['symbol'],
                (float)$result['price'],
                (float)$result['quantity'],
                Carbon::parse($result['created_at'])
            );
        }
        return $transactions;
    }

    public function log(
        int    $userId,
        string $kind,
        string $symbol,
        float  $price,
        float  $quantity
    ): void
    {
        (new SqliteService())
            ->create(
                'transactions',
                [
                    'user_id' => $userId,
                    'kind' => $kind,
                    'symbol' => $symbol,
                    'price' => $price,
                    'quantity' => $quantity,
                    'created_at' => Carbon::now()->toIso8601String(),
                ]);
    }

    public function display($userId)
    {
        $transactions = $this->getUserTransaction($userId);
        $tableTransactions = new Table(new ConsoleOutput());
        $tableTransactions
            ->setHeaders(['User ID', 'Kind', 'Symbol', 'Quantity', 'Price', 'Date']);
        $tableTransactions
            ->setRows(array_map(function (Transaction $transaction): array {
                return [
                    $transaction->getUserId(),
                    $transaction->getKind(),
                    $transaction->getSymbol(),
                    $transaction->getQuantity(),
                    number_format($transaction->getPrice(), 2),
                    $transaction->getCreatedAt(),
                ];
            }, $transactions));
        $tableTransactions->setStyle('box');
        $tableTransactions->render();
    }
}