<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

/**
 * Exception for "do not understand" errors (message not understood by object)
 */
class DoNotUnderstandException extends IPPException
{
    public function __construct(string $message = "Message not understood", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::INTERPRET_DNU_ERROR, $previous);
    }
}