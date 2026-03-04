<?php

/**
 * Unit — Part of the MaplePHP Unitary Kernel/ Dispatcher,
 * A simple and fast dispatcher, will work great for this solution
 *
 * @package:    MaplePHP\Unitary
 * @author:     Daniel Ronkainen
 * @licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
 *              Don't delete this comment, it's part of the license.
 */

declare(strict_types=1);

namespace MaplePHP\Emitron;

use FastRoute\Dispatcher;
use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Http\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use MaplePHP\Log\InvalidArgumentException;

class Kernel extends AbstractKernel
{
    /**
     * Run the emitter and init all routes, middlewares and configs
     *
     * @param ServerRequestInterface $request
     * @param StreamInterface|null $stream
     * @return void
     */
    public function run(ServerRequestInterface $request, ?StreamInterface $stream = null): void
    {

        $this->dispatchConfig->getRouter()->dispatch(function ($data, $args, $middlewares) use ($request, $stream) {

            $dispatchCode = $data[0] ?? RouterDispatcher::FOUND;

            [$data, $args, $middlewares] = $this->reMap($data, $args, $middlewares);

            if (!isset($data['handler'])) {
                throw new InvalidArgumentException("Missing 'handler' key.");
            }


            $this->container->set("request", $request);
            $this->container->set("args", $args);
            $this->container->set("configuration", $this->getDispatchConfig());

            $bodyStream = $this->getBody($stream);
            $factory = new ResponseFactory($bodyStream);
            $finalHandler = new ControllerRequestHandler($factory, $data['handler'] ?? []);


            $response = $this->initRequestHandler(
                request: $request,
                stream: $bodyStream,
                finalHandler: $finalHandler,
                middlewares: $middlewares
            );

            if ($dispatchCode === Dispatcher::NOT_FOUND) {
                $response = $response->withStatus(404);
            }

            if ($dispatchCode === Dispatcher::METHOD_NOT_ALLOWED) {
                $response = $response->withStatus(405);
            }

            $this->createEmitter()->emit($response, $request);
        });
    }


    function reMap($data, $args, $middlewares)
    {
        if (isset($data[1]) && $middlewares instanceof ServerRequestInterface) {
            $item = $data[1];
            return [
                ["handler" => $item['controller']], $_REQUEST, ($item['data'] ?? [])
            ];
        }
        if (!is_array($middlewares)) {
            $middlewares = [];
        }
        return [$data, $args, $middlewares];
    }
}