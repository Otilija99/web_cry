<?php declare(strict_types=1);

namespace App\Repositories\Currency;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use GuzzleHttp\Client;
use stdClass;

class CoinGeckoApiCurrencyRepository implements CurrencyRepository
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coingecko.com/api/v3/',
            'headers' => [
                'accept' => 'application/json',
                'x-cg-demo-api-key' => $_ENV['COIN_GECKO_API_KEY'],
            ],
        ]);
    }

    public function fetchCurrencyData(): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'coins/markets',
                [
                    'query' => [
                        'vs_currency' => 'USD',
                        'per_page' => '20',
                    ]
                ]);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to fetch currency data with CoinGecko API.',
                    $response->getStatusCode()
                );
            }

            $currenciesData = $response->getBody()->getContents();
            $currencies = json_decode($currenciesData);
            return array_map('self::deserialize', $currencies);
        } catch (HttpFailedRequestException $e) {
            throw new HttpFailedRequestException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function searchCurrencyBySymbol(string $symbol): ?Currency
    {
        try {
            $response = $this->client->request(
                'GET',
                'coins/list'
            );

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to find currency data with CoinGecko API.',
                    $response->getStatusCode()
                );
            }
            $currenciesData = $response->getBody()->getContents();
            $currencies = json_decode($currenciesData);
            foreach ($currencies as $currency) {
                if ($currency->symbol === strtolower($symbol)) {
                    $response = $this->client->request(
                        'GET',
                        'coins/' . $currency->id
                    );
                    break;
                }
            }

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to find currency data with CoinGecko API.',
                    $response->getStatusCode()
                );
            }
            $currencyData = $response->getBody()->getContents();
            $currency = json_decode($currencyData);
            if (!isset($currency)) {
                return null;
            }
            return $this->deserializeSearchResult($currency);
        } catch (HttpFailedRequestException $e) {
            throw new HttpFailedRequestException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function deserialize(stdClass $object): Currency
    {
        return new Currency(
            $object->name,
            strtoupper($object->symbol),
            $object->current_price
        );
    }

    private function deserializeSearchResult(stdClass $object): Currency
    {
        return new Currency(
            $object->name,
            strtoupper($object->symbol),
            $object->market_data->current_price->usd
        );
    }
}