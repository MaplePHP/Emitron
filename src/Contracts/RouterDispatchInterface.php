<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Contracts;

interface RouterDispatchInterface
{
    /**
     * Dispatch matched router
     *
     * @param callable $call
     * @return bool
     */
    public function dispatch(callable $call): bool;
}