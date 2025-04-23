<?php

namespace IPP\Student\Runtime\BuiltIn;

use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Simple implementation of a built-in method using a callback
 */
class SimpleBuiltInMethod implements BuiltInMethod
{
    /**
     * Create a new simple built-in method
     * 
     * @param callable $implementation Method implementation
     */
    public function __construct(
        /** @var callable */
        private $implementation
    ) {
    }
    
    /**
     * Execute the built-in method
     * 
     * @param Environment $env Runtime environment
     * @param SOLObject $receiver Method receiver
     * @param SOLObject[] $arguments Method arguments
     * @return SOLObject Method result
     */
    public function execute(Environment $env, SOLObject $receiver, array $arguments): SOLObject
    {
        return call_user_func($this->implementation, $env, $receiver, $arguments);
    }
    
    /**
     * Create a simple built-in method from a callable
     * 
     * @param callable $implementation Method implementation
     * @return SimpleBuiltInMethod
     */
    public static function fromCallable(callable $implementation): self
    {
        return new self($implementation);
    }
}