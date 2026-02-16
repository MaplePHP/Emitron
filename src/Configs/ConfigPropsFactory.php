<?php

namespace MaplePHP\Emitron\Configs;

use MaplePHP\Emitron\AbstractConfigProps;
use MaplePHP\Emitron\Contracts\ConfigPropsInterface;

class ConfigPropsFactory
{
    /**
     * Get expected instance of Config Props
     *
     * @param array $props
     * @return ConfigPropsInterface
     */
    public static function create(array $props): ConfigPropsInterface
    {
        $override = '\\Configs\\ConfigProps';
        $default  = \MaplePHP\Unitary\Config\ConfigProps::class;
        $name = class_exists($override) ? $override : $default;
        if (!is_subclass_of($name, ConfigPropsInterface::class)) {
            $name = $default;
        }

        if (!class_exists($name)) {
            return self::resolver();
        }

        return new $name($props);
    }

    /**
     * Will resolve minimum required config dependencies
     *
     * @param array $props
     * @return ConfigPropsInterface
     */
    private static function resolver(array $props): ConfigPropsInterface
    {
        return new class($props) extends AbstractConfigProps {
            protected function propsHydration(bool|string $key, mixed $value): void
            {
                // Intentionally no-op (or implement minimal hydration rules)
            }
        };
    }
}