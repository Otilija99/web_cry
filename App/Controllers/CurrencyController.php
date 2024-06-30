<?php

namespace App\Controllers;

use App\Repositories\Currency\CoinMarketApiCurrencyRepository;
use Exception;
use Twig\Environment;

class CurrencyController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): string
    {
        try {
            $topCurrencies = (new CoinMarketApiCurrencyRepository())->fetchCurrencyData();
            return $this->twig->render(
                'index.html.twig',
                ['currency' => $topCurrencies]
            );
        } catch (Exception $e) {
            return $this->twig->render(
                'error.html.twig',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function show(string $symbol): string
    {
        try {
            $currency = (new CoinMarketApiCurrencyRepository())->searchCurrencyBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
            return $this->twig->render('show.html.twig', ['currency' => $currency]);
        } catch (Exception $e) {
            return $this->twig->render('error.html.twig', ['message' => $e->getMessage()]);
        }
    }
    public function search()
    {
        // Your implementation for search
    }
}