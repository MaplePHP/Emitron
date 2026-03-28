<?php

declare(strict_types=1);

namespace MaplePHP\Emitron;

use Closure;
use MaplePHP\Container\Reflection;
use MaplePHP\Http\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ControllerRequestHandler implements RequestHandlerInterface
{
	public function __construct(
		private readonly ResponseFactoryInterface $factory,
		private readonly array|Closure            $controller,
		private readonly ?Closure                 $call = null
	)
	{
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$response = $this->factory->createResponse();

		$this->appendInterfaces([
			ResponseInterface::class => $response,
		]);

		$controller = $this->controller;
		if (is_callable($controller)) {
			return $controller($request, $response);
		}

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

		$call = $this->call;
		if ($call !== null) {
			$call($classInst, $response);
		}
		// This should INVOKE the method and return its result (ResponseInterface or something else)
		$result = $reflect->dependencyInjector($classInst, $method);

		return $this->createResponse($response, $result);
	}


	/**
	 * Will create a PSR valid Response instance form mixed result
	 *
	 * @param ResponseInterface $response
	 * @param mixed $result
	 * @return ResponseInterface
	 */
	protected function createResponse(ResponseInterface $response, mixed $result): ResponseInterface
	{
		if ($result instanceof ResponseInterface) {
			return $result;
		}

		if ($result instanceof StreamInterface) {
			return $response->withBody($result);
		}

		if (is_array($result) || is_object($result)) {
			return $this->createStream($response, json_encode($result, JSON_UNESCAPED_UNICODE))
				->withHeader("Content-Type", "application/json");
		}

		if (is_string($result) || is_numeric($result)) {
			return $this->createStream($response, $result);
		}
		return $response;
	}

	/**
	 * A helper method to create a new stream instance
	 *
	 * @param ResponseInterface $response
	 * @param mixed $result
	 * @return ResponseInterface
	 */
	protected function createStream(ResponseInterface $response, mixed $result): ResponseInterface
	{
		$streamFactory = new StreamFactory();
		$stream = $streamFactory->createStream($result);
		return $response->withBody($stream);

	}

	/**
	 * Append interface helper method
	 *
	 * @param array $bindings
	 * @return void
	 */
	protected function appendInterfaces(array $bindings)
	{
		Reflection::interfaceFactory(function (string $className) use ($bindings) {
			return $bindings[$className] ?? null;
		});
	}
}