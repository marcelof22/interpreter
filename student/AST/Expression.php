<?php

namespace IPP\Student\AST;

use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Abstract AST node representing an expression
 */
abstract class Expression extends Node
{
    /**
     * Evaluate the expression
     * 
     * @param Environment $env Runtime environment
     * @param SOLObject $self Self object for context
     * @return SOLObject Result of expression evaluation
     */
    abstract public function evaluate(Environment $env, SOLObject $self): SOLObject;
}