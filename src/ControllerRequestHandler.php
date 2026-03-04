<?php
namespace MaplePHP\Emitron;


use FastRoute\Dispatcher;
use MaplePHP\Container\Reflection;
use MaplePHP\Core\Router\RouterDispatcher;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ControllerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $factory,
        private readonly array $controller, // [$class, $method] or [$class]
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->factory->createResponse();

        $this->appendInterfaces([
            "ResponseInterface" => $response,
        ]);

        $controller = $this->controller;
        if (!isset($controller[1])) {
            $controller[1] = '__invoke';
        }

        if (count($controller) !== 2) {
            $response->getBody()->write("ERROR: Invalid controller handler.\n");
            return $response;
        }

        [$class, $method] = $controller;

        if (!method_exists($class, $method)) {
            $response->getBody()->write("ERROR: Could not load Controller {$class}::{$method}().\n");
            return $response;
        }

        // Your DI wiring
        $reflect = new Reflection($class);
        $classInst = $reflect->dependencyInjector();


        // This should INVOKE the method and return its result (ResponseInterface or something else)
        $result = $reflect->dependencyInjector($classInst, $method);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        // If controller didn’t return a response:
        // - treat it as “controller wrote to $response->getBody() somewhere”
        // - or treat non-response as error
        return $response;
    }


    protected function appendInterfaces(array $bindings)
    {
        Reflection::interfaceFactory(function (string $className) use ($bindings) {
            return $bindings[$className] ?? null;
        });
    }

}