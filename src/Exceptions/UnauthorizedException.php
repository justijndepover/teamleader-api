<?php

namespace Justijndepover\Teamleader\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public static function make(string $code, string $message): self
    {
        return new static("Error $code: $message", $code);
    }
}