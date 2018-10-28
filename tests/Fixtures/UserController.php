<?php


namespace Middleware\Tests\Fixtures;

use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;

use function json_encode;


class UserController
{
    /**
     * GET /users
     */
    public function index(): ResponseInterface
    {
        return Factory::createResponse(200)
            ->withBody(Factory::createStream('Hello World'));
    }

    /**
     * GET /users/{id}
     *
     * @param array $params
     * @return ResponseInterface
     */
    public function show(array $params)
    {
        return Factory::createResponse(200)
            ->withBody(Factory::createStream(json_encode($params)));
    }
}
