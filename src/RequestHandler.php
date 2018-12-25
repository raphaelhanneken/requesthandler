<?php

namespace Middleware;

use BadMethodCallException;
use Middleware\Exception\RequestHandlerNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface, MiddlewareInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $requestHandlerAttributeName;

    /**
     * Create a new RequestHandler instance
     *
     * @param ContainerInterface $container
     * @param string             $requestHandlerAttributeName
     */
    public function __construct(ContainerInterface $container, string $requestHandlerAttributeName = 'Request-Handler')
    {
        $this->container                   = $container;
        $this->requestHandlerAttributeName = $requestHandlerAttributeName;
    }

    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        list($controller, $method) = explode(':', $request->getAttribute($this->requestHandlerAttributeName));

        if (!$this->container->has($controller)) {
            throw new RequestHandlerNotFoundException("Request handler $controller not found.");
        }

        $controller = $this->container->get($controller);
        if (!method_exists($controller, $method)) {
            throw new BadMethodCallException(
                'Request handler ' . get_class($controller) . " does not implement method $method."
            );
        }

        if (empty($request->getAttribute('Request-Params', []))) {
            return $controller->{$method}();
        }

        return $controller->{$method}($request->getAttribute('Request-Params'));
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return $this->handle($request);
    }
}
