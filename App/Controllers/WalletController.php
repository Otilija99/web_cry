<?php declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Currency\CoinMarketApiCurrencyRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\SqliteService;
use App\Services\WalletService;
use Exception;
use Twig\Environment;

class WalletController
{
    private CoinMarketApiCurrencyRepository $client;
    private SqliteService $database;
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private Environment $twig;
    private WalletService $walletService;

    public function __construct(Environment $twig)
    {
        $this->client = new CoinMarketApiCurrencyRepository();
        $this->database = new SqliteService();
        $this->userRepository = new UserRepository($this->database);
        $this->walletRepository = new WalletRepository($this->database);
        $this->walletService = new WalletService($this->client, $this->database, $this->userRepository);
        $this->twig = $twig;
    }

    public function buy(): string
    {
        $user = $this->userRepository->findByUsername('Customer');
        $userId = $user->getId();

        $symbol = (string)$_POST['symbol'] ?? null;
        $quantity = (int)$_POST['quantity'] ?? null;

        if ($symbol === null || $quantity === null) {
            return $this->twig->render(
                'error.html.twig',
                ['message' => 'Invalid input.']
            );
        }

        try {
            $message = $this->walletService->buyCurrency($userId, $symbol, $quantity);
            return $this->twig->render(
                'success.html.twig',
                ['message' => $message]
            );
        } catch (Exception $e) {
            return $this->twig->render(
                'error.html.twig',
                ['message' => $e->getMessage()]
            );
        }
    }
}