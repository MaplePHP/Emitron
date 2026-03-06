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

use MaplePHP\Http\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use MaplePHP\Emitron\Enums\DispatchCodes;
use MaplePHP\Http\ResponseFactory;

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


	        $parts = isset($data[2]) && is_array($data[2]) ? $data[2] : [];
	        $dispatchCode = (int)($data[0] ?? DispatchCodes::FOUND->value);


	        if($dispatchCode !== DispatchCodes::FOUND->value) {
		        $data['handler'] = function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
		        {
			        return $res->withStatus(404);
		        };
	        }
            //$dispatchCode = $data[0] ?? RouterDispatcher::FOUND;
            [$data, $args, $middlewares] = $this->reMap($data, $args, $middlewares);

            $this->container->set("request", $request);
            $this->container->set("args", $args);
            $this->container->set("configuration", $this->getDispatchConfig());

            $bodyStream = $this->getBody($stream);
            $factory = new ResponseFactory($bodyStream);
            $finalHandler = new ControllerRequestHandler($factory, $data['handler'] ?? []);
			$path = new Path($parts, $request);


            $response = $this->initRequestHandler(
                request: $request,
                stream: $bodyStream,
				path: $path,
                finalHandler: $finalHandler,
                middlewares: $middlewares
            );
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

	    if (!is_array($args)) {
		    $args = [];
	    }
        return [$data, $args, $middlewares];
    }
}