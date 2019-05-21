<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require "vendor/autoload.php";

const MNB_PASSWORD = 'MNB_PASSWORD';

const ONE_DAY_IN_HOURS = 24 * 60 * 60;

$exchangeService = new \Icetee\MNB\ExchangeRate();

$app = new \Slim\App;

$app->add(
    function (Request $request, Response $response, callable $next) {
        $params = $request->getQueryParams();

        if (empty($params['p']) || !getenv(MNB_PASSWORD) || $params['p'] !== getenv(MNB_PASSWORD)) {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_UNAUTHORIZED);
        }

        return $next($request, $response);
    }
);

$app->get(
    '/',
    function (Request $request, Response $response) use ($exchangeService) {
        $params = $request->getQueryParams();

        $startDate  = empty($params['startDate']) ? date('Y-m-d') : $params['startDate'];
        $endDate    = empty($params['endDate']) ? date('Y-m-d') : $params['endDate'];
        $currencies = empty($params['currencies']) ? 'EUR' : $params['currencies'];
        $delimeter  = empty($params['delimeter']) ? "\t" : $params['delimeter'];

        // Request validation
        try {
            $today = new DateTime();

            $sd = new DateTime($startDate);
            if ($sd > $today) {
                $response->getBody()->write(sprintf('Invalid startDate: %s', $startDate));

                return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
            }

            $ed = new DateTime($endDate);
            if ($ed > $today || $ed < $sd) {
                $response->getBody()->write(sprintf('Invalid endDate: %s', $endDate));

                return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
            }

            if (!preg_match('/^([A-Z]{3}\,)*$/Ums', $currencies . ',')) {
                $response->getBody()->write(sprintf('Invalid currencies: %s', $currencies));

                return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
            }

            if (strlen($delimeter) > 100) {
                $response->getBody()->write(sprintf('Invalid delimeter: %s', $delimeter));

                return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            $response->getBody()->write(sprintf('Unexpected error during validation'));

            return $response->withStatus(\Slim\Http\StatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retrieve data
        /** @var \Icetee\MNB\RateEntityCollection $rates */
        try {
            $rates = $exchangeService->getExchangeRates($startDate, $endDate, $currencies);
        } catch (\Exception $e) {
            $response->getBody()->write(sprintf('Internal error: %s', $e->getMessage()));

            return $response->withStatus(\Slim\Http\StatusCode::HTTP_BAD_REQUEST);
        }

        // Create response
        $lines = '';
        /** @var \Icetee\MNB\RateEntity $rate */
        foreach ($rates->getCollection() as $rate) {
            $lines .= sprintf(
                "%s$delimeter%s$delimeter%s$delimeter%s\n",
                $rate->getDate(),
                $rate->getCurrency(),
                $rate->getUnit(),
                $rate->getValue()
            );
        }

        $response->getBody()->write($lines);

        return $response->withHeader('Content-type', 'text/csv');
    }
);

$app->run();
