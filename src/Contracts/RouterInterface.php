<?php

namespace MaplePHP\Emitron\Contracts;

interface RouterInterface extends RouterDispatchInterface
{
    /**
     * Map one or more needles to controller
     */
    public function map(string|array $methods, string $pattern, array $controller): void;
}