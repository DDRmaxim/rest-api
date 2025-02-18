<?php

namespace App\Service;

use Pecee\Http\Request;
use Pecee\Http\Middleware\IMiddleware;

class ParsingRequest implements IMiddleware
{
    /**
     * @inheritDoc
     */
    public function handle(Request $request): void
    {
        $input = file_get_contents('php://input');

        $request->data = null;

        if ($input) {
            try {
                $request->data = json_decode($input, false);
            } catch (\Throwable $e) {}
        }
    }
}