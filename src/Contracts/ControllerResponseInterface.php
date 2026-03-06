<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerResponseInterface
{
    /**
     * Controller response can be empty, string, array, object
     *
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response): mixed;
}