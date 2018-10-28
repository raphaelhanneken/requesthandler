<?php

namespace Middleware\Tests;

use BadMethodCallException;
use Middleware\Exception\RequestHandlerNotFoundException;
use Middleware\RequestHandler;
use Middleware\Tests\Fixtures\UserController;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestHandlerTest
 *
 * @package Middleware\Tests
 * @covers \Middleware\RequestHandler
 */
class RequestHandlerTest extends TestCase
{
    /** @var ServerRequestInterface */
    private $request;

    /** @var ContainerInterface|MockObject */
    private $container;


    public function setUp(): void
    {
        $this->request = Factory::createServerRequest('GET', '/users');
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @covers \Middleware\RequestHandler::handle
     */
    public function testHandleRequest(): void
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with(UserController::class)
            ->willReturn(true);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with(UserController::class)
            ->willReturn(new UserController());

        $this->request = $this->request
            ->withAttribute('Request-Handler', UserController::class . ':index');

        $response = (new RequestHandler($this->container))->handle($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    /**
     * @covers \Middleware\RequestHandler::handle
     */
    public function testThrowsRequestHandlerNotFoundException(): void
    {
        $this->expectException(RequestHandlerNotFoundException::class);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $this->container
            ->expects($this->never())
            ->method('get');

        $this->request = $this->request
            ->withAttribute('Request-Handler', 'Unknown\\Class\\Name:show');

        (new RequestHandler($this->container))->handle($this->request);
    }

    /**
     * @covers \Middleware\RequestHandler::handle
     */
    public function testThrowsBadMethodCallException(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with(UserController::class)
            ->willReturn(true);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with(UserController::class)
            ->willReturn(new UserController());

        $this->request = $this->request
            ->withAttribute('Request-Handler', UserController::class . ':undefined');

        (new RequestHandler($this->container))->handle($this->request);
    }
}
