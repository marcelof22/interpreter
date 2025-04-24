<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for internal errors in the interpreter
 */
class InternalErrorException extends IPPException
{
    public function __construct(string $message = "Internal interpreter error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::INTERNAL_ERROR, $previous);
    }
}