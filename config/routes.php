<?php

use App\Service\ParsingRequest;

use Pecee\Http\Request;
use Pecee\SimpleRouter\SimpleRouter as Router;

Router::setDefaultNamespace('App\Controller');

Router::group([
    'prefix' => 'api/v1',
    'middleware' => [
        ParsingRequest::class
    ]
], function () {
    Router::post('/order/cost', 'OrderController@cost');
});

Router::error(function (Request $request, Exception $exception) {
    $response = Router::response();
    switch (get_class($exception)) {
        case Exception::class: {
            $response->httpCode(500);
            break;
        }
    }
    
    return $response->json([
        'status' => 'error',
        'message' => $exception->getMessage()
    ]);
});