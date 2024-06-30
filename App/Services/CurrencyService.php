<?php declare(strict_types=1);

namespace App\Services;

use App\Repositories\Currency\CurrencyRepository;
use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class CurrencyService
{
    private CurrencyRepository $client;

    public function __construct(CurrencyRepository $client)
    {
        $this->client = $client;
    }

    public function displayList(): void
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

        $outputCrypto = new ConsoleOutput();
        $tableCurrencies = new Table($outputCrypto);
        $tableCurrencies
            ->setHeaders(['Index', 'Name', 'Symbol', 'Price']);
        $tableCurrencies
            ->setRows(array_map(function (int $index, Currency $cryptoCurrency): array {
                return [
                    $index + 1,
                    $cryptoCurrency->getName(),
                    $cryptoCurrency->getSymbol(),
                    new TableCell(
                        number_format($cryptoCurrency->getPrice(), 2),
                        ['style' => new TableCellStyle(['align' => 'right',])]
                    ),
                ];
            }, array_keys($currencies), $currencies));
        $tableCurrencies->setStyle('box-double');
        $tableCurrencies->render();
    }
}