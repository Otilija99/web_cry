<?php

namespace App\Repositories\Currency;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;

interface CurrencyRepository
{
    /**
     * @return array<Currency>
     * @throws HttpFailedRequestException
     */
    public function fetchCurrencyData(): array;

    public function searchCurrencyBySymbol(string $symbol): ?Currency;
}