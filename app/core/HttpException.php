<?php
declare(strict_types=1);
namespace HotSpot\Core;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(private int $httpStatus, string $message)
    {
        parent::__construct($message);
    }
    public function status(): int { return $this->httpStatus; }
}
