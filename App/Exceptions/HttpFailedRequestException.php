<?php

namespace App\Exceptions;

use Exception;

class HttpFailedRequestException extends Exception
{
    public function __construct($message = "HTTP request failed", $code = 0, Exception $previous = null)
    {
        $messages = [
            400 => " - Bad Request",
            401 => " - Unauthorized request",
            403 => " - Forbidden",
            404 => " - Not Found",
            429 => " - Too many requests",
            500 => " - Internal Server Error",
            503 => " - Service Unavailable",
            1020 => " - Access Denied",
            10002 => " - Api Key Missing",
            10005 => " - This request is limited to Pro API subscribers",
        ];

        if (array_key_exists($code, $messages)) {
            $message .= $messages[$code];
        }

        parent::__construct($message, $code, $previous);
    }
}