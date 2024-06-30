<?php

namespace App\Exceptions;

use Exception;

class CurrencyNotFoundException extends Exception
{
    public function __construct($currencyId, $code = 0, Exception $previous = null)
    {
        $message = "Currency not found for ID: " . $currencyId;
        parent::__construct($message, $code, $previous);
    }
}
