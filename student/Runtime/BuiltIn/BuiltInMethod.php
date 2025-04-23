<?php

namespace IPP\Student\Runtime\BuiltIn;

use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Interface for built-in methods
 */
interface BuiltInMethod
{
    /**
     * Execute the built-in method
     * 
     * @param Environment $env Runtime environment
     * @param SOLObject $receiver Method receiver
     * @param SOLObject[] $arguments Method arguments
     * @return SOLObject Method result
     */
    public function execute(Environment $env, SOLObject $receiver, array $arguments): SOLObject;
}