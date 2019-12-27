<?php

namespace EasyDoc\Exception;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct("HTTP error: $message", $code, $previous);
    }
}
