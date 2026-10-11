<?php
declare(strict_types=1);

namespace HotSpot\Core;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(int $httpStatusCode, string $errorMessage)
    {
        parent::__construct($errorMessage, $httpStatusCode);
    }
}