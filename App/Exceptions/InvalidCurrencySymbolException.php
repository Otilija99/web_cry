<?php

namespace App\Exceptions;

use Exception;

class InvalidCurrencySymbolException extends Exception
{
    public function __construct($symbol, $code = 0, Exception $previous = null)
    {
        $message = "Invalid currency symbol: " . $symbol;
        parent::__construct($message, $code, $previous);
    }
}
