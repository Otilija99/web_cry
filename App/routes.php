<?php

return [
    ['GET', '/index', [App\Controllers\CurrencyController::class, 'index']],
    ['GET', '/currencies/{symbol}', [App\Controllers\CurrencyController::class, 'show']],
    ['POST', '/currency/search',  [App\Controllers\CurrencyController::class, 'search']],
    ['POST', '/currency/buy', [App\Controllers\WalletController::class, 'buyCurrency']],
    ['POST', '/wallets',  [App\Controllers\CurrencyController::class, 'sell']],
    ['GET', '/transactions', [App\Controllers\CurrencyController::class, 'change']],
];
