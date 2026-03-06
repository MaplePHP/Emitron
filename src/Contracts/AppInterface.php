<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Contracts;

use MaplePHP\Core\Support\Dir;

interface AppInterface
{

	/**
	 * This is a single to set App globals
	 *
	 * @param Dir $dir
	 * @param array $config
	 * @return self
	 */
	public static function boot(Dir $dir, array $config = []): self;

	/**
	 * Get App singleton instance
	 *
	 * @return self
	 */
	public static function get(): self;

	/**
	 * Check if the environment is in prod
	 *
	 * @return bool
	 */
	public function isProd(): bool;

	/**
	 * Check if the environment is in stage
	 *
	 * @return bool
	 */
	public function isStage(): bool;

	/**
	 * Check if the environment is in test
	 *
	 * @return bool
	 */
	public function isTest(): bool;

	/**
	 * Check if the environment is in dev
	 *
	 * @return bool
	 */
	public function isDev(): bool;

	/**
	 * Get current Environment
	 *
	 * @return string
	 */
	public function env(): string;

	/**
	 * Get core/boot dir where code app boot originate
	 *
	 * @return string
	 */
	public function coreDir(): string;

	/**
	 * Get the app core Dir instance
	 *
	 * @return Dir
	 */
	public function dir(): Dir;

}