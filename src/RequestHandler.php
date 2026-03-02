<?php

namespace MaplePHP\Emitron;

use MaplePHP\Container\Reflection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandler implements RequestHandlerInterface
{
    private int $index = 0;

    /** @var list<MiddlewareInterface> */
    private array $middlewareQueue;

    public function __construct(
        array $middlewares,
        private readonly RequestHandlerInterface $finalHandler
    ) {
        $this->middlewareQueue = $middlewares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // End of chain -> call controller (or whatever final handler you set)
        if (!isset($this->middlewareQueue[$this->index])) {
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->middlewareQueue[$this->index];
        $this->index++;

        if (is_string($middleware)) {
            $reflect = new Reflection($middleware);
            $middleware = $reflect->dependencyInjector();
        }

        return $middleware->process($request, $this);
    }
}