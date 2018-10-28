<?php


namespace Middleware\Tests\Fixtures;


use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;

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
}
