<?php

namespace Justijndepover\Teamleader\Exceptions;

use Exception;

class CouldNotAquireAccessTokenException extends Exception
{
    public static function make(string $code, string $message): self
    {
        return new static("Error $code: Could not aquire or refresh access token: $message", $code);
    }
}
