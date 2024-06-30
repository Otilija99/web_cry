<?php declare(strict_types=1);

namespace App\Repositories\Currency;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class CoinMarketApiCurrencyRepository implements CurrencyRepository
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'headers' => [
                'Accepts' => 'application/json',
                'X-CMC_PRO_API_KEY' => $_ENV['CRYPTO_API_KEY']
            ],
        ]);
    }

    public function fetchCurrencyData(): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'cryptocurrency/listings/latest',
                [
                    'query' => [
                        'start' => '1',
                        'limit' => '20',
                        'convert' => 'USD'
                    ],
                ]);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to fetch currency data with CoinMarket API. Status code: ' . $response->getStatusCode()
                );
            }

            $currenciesData = $response->getBody()->getContents();
            $currencies = json_decode($currenciesData);
            return array_map('self::deserialize', $currencies->data);
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
            $response = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
                'query' => ['symbol' => $symbol]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to find currency with CoinMarket API. Status code: ' . $response->getStatusCode()
                );
            }
            $currencyData = $response->getBody()->getContents();
            $currency = json_decode($currencyData);
            if (!isset($currency->data->$symbol)) {
                return null;
            }
            return $this->deserialize($currency->data->$symbol);
        } catch (RequestException $e) {
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
            $object->quote->USD->price
        );
    }
}