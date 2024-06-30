<?php declare(strict_types=1);

namespace App\Services;

use App\Repositories\Currency\CurrencyRepository;
use App\Exceptions\HttpFailedRequestException;
use App\Models\Wallet;
use App\Repositories\User\UserRepository;

class WalletService
{
    private CurrencyRepository $client;
    private SqliteService $database;
    private UserRepository $userRepository;

    public function __construct(
        CurrencyRepository $client,
        SqliteService $database,
        UserRepository $userRepository
    ) {
        $this->client = $client;
        $this->database = $database;
        $this->userRepository = $userRepository;
    }

    private function getUserWallet(int $userId): array
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

    private function fetchCurrentPrices(): array
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

        $currentPrices = [];
        foreach ($currencies as $currency) {
            $currentPrices[$currency->getSymbol()] = $currency->getPrice();
        }

        return $currentPrices;
    }

    public function display(int $userId): array
    {
        $wallets = $this->getUserWallet($userId);
        $currentPrices = $this->fetchCurrentPrices();

        $walletDetails = array_map(function (Wallet $wallet) use ($currentPrices): array {
            $symbol = $wallet->getSymbol();
            $currentPrice = $currentPrices[$symbol] ?? 0;
            $profitability = $wallet->calculateProfitability($currentPrice);

            return [
                'symbol' => $symbol,
                'amount' => $wallet->getAmount(),
                'average_price' => number_format($wallet->getAveragePrice(), 2),
                'profitability' => number_format($profitability, 2) . "%",
            ];
        }, $wallets);

        $totalBalance = number_format($this->userRepository->findById($userId)->getBalance(), 2);

        return [
            'wallet_details' => $walletDetails,
            'total_balance' => $totalBalance,
        ];
    }

    public function buyCurrency(int $userId, string $symbol, float $quantity): string
    {
        $currencies = $this->fetchCurrentPrices();
        $kind = 'buy';

        if (isset($currencies[$symbol])) {
            $currency = $currencies[$symbol];
            $price = $currency->getPrice();
            $symbol = $currency->getSymbol();
            $totalCost = $price * $quantity;

            $user = $this->userRepository->findById($userId);
            $balance = $user->getBalance();

            if ($balance < $totalCost) {
                return "You need \$" . number_format($totalCost, 2) . " but you have \$" . number_format($balance, 2) . ".";
            }

            $existingWallet = $this->findExistingWallet($userId, $symbol);

            if ($existingWallet) {
                $this->updateExistingWallet($userId, $existingWallet, $quantity, $totalCost);
            } else {
                $this->createNewWallet($userId, $symbol, $quantity, $price);
            }

            $newBalance = $balance - $totalCost;
            $this->userRepository->updateBalance($user, $newBalance);
            (new TransactionService())->log($userId, $kind, $symbol, $price, $quantity);

            return "You bought $quantity $symbol for \$" . number_format($totalCost, 2) . ".";
        }

        return "Invalid index.";
    }

    private function findExistingWallet(int $userId, string $symbol): ?Wallet
    {
        $wallets = $this->getUserWallet($userId);
        foreach ($wallets as $wallet) {
            if ($wallet->getSymbol() === $symbol) {
                return $wallet;
            }
        }
        return null;
    }

    private function updateExistingWallet(int $userId, Wallet $wallet, float $quantity, float $totalCost): void
    {
        $newAmount = $wallet->getAmount() + $quantity;
        $newAveragePrice = ($totalCost + $wallet->getAveragePrice() * $wallet->getAmount()) / $newAmount;

        $this->database->update(
            'wallets',
            [
                'amount' => $newAmount,
                'average_price' => $newAveragePrice,
            ],
            [
                'user_id' => $userId,
                'symbol' => $wallet->getSymbol(),
            ]
        );
    }

    private function createNewWallet(int $userId, string $symbol, float $quantity, float $price): void
    {
        $this->database->create(
            'wallets',
            [
                'symbol' => $symbol,
                'amount' => $quantity,
                'average_price' => $price,
                'user_id' => $userId,
            ]
        );
    }

    public function sell(int $userId, string $symbol, float $quantity): string
    {
        $currencies = $this->fetchCurrentPrices();
        $wallets = $this->getUserWallet($userId);

        if (count($wallets) === 0) {
            return "There are no items in your wallet.";
        }

        $kind = 'sell';
        $wallet = $this->findExistingWallet($userId, $symbol);

        if ($wallet === null) {
            return "There are no items in your wallet with the symbol $symbol.";
        }

        if ($wallet->getAmount() < $quantity) {
            return "You only have " . $wallet->getAmount() . " of $symbol to sell.";
        }

        $currentPrice = $currencies[$symbol] ?? 0;
        $totalValue = $quantity * $currentPrice;
        $newAmount = $wallet->getAmount() - $quantity;

        if ($newAmount > 0) {
            $this->database->update(
                'wallets',
                [
                    'amount' => $newAmount,
                ],
                [
                    'user_id' => $userId,
                    'symbol' => $symbol,
                ]
            );
        } else {
            $this->database->delete(
                'wallets',
                [
                    'user_id' => $userId,
                    'symbol' => $symbol,
                ]
            );
        }

        $user = $this->userRepository->findById($userId);
        $newBalance = $user->getBalance() + $totalValue;
        $this->userRepository->updateBalance($user, $newBalance);

        (new TransactionService())->log($userId, $kind, $symbol, $currentPrice, $quantity);

        return "You sold $quantity $symbol for \$" . number_format($totalValue, 2) . ".";
    }
}
