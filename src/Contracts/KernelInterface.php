<?php

namespace MaplePHP\Emitron\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface KernelInterface
{
    /**
     * Run the emitter and init all routes, middlewares and configs
     *
     * @param ServerRequestInterface $request
     * @param StreamInterface|null $stream
     * @return void
     */
    public function run(ServerRequestInterface $request, ?StreamInterface $stream): void;

}
