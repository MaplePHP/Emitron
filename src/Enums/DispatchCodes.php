<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Enums;

enum DispatchCodes: int
{
	case FOUND = 1;
	case NOT_FOUND = 0;
	case METHOD_NOT_ALLOWED = 2;
}
