<?php

namespace App\Controller;

use Pecee\Http\Request;
use Pecee\Http\Response;
use Pecee\SimpleRouter\SimpleRouter as Router;

abstract class AbstractController
{
    /*
     * @var Request
     */
    protected Request $request;

    /*
     * @var Response
     */
    protected Response $response;

    public function __construct()
    {
        $this->request = Router::router()->getRequest();
        $this->response = new Response($this->request);

        $this->validateData();
        $this->fillingData();
    }

    abstract public function validateData();

    abstract public function fillingData();

    public function error(string $message, int $code = 422) {
        $this->response->httpCode($code);
        return $this->response->json([
           'status' => 'error',
           'message' => $message
        ]);
    }
}