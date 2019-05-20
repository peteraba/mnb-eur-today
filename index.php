<?php

declare(strict_types=1);

require "vendor/autoload.php";

$exchangeService = new \Icetee\MNB\ExchangeRate();

$currentRate = $exchangeService->getCurrentExchangeRate('EUR');

echo $currentRate->getValue();
