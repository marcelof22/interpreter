<?php

namespace IPP\Student\AST;

use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * AST node representing a block of code
 */
class Block extends Expression
{
    /**
     * @param int $arity Block arity (number of parameters)
     * @param Parameter[] $parameters Block parameters
     * @param Assignment[] $statements Block statements
     */
    public function __construct(
        private int $arity,
        private array $parameters = [],
        private array $statements = []
    ) {
    }

    /**
     * Get block arity
     */
    public function getArity(): int
    {
        return $this->arity;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return Assignment[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * Add a parameter to the block
     */
    public function addParameter(Parameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }

    /**
     * Add a statement to the block
     */
    public function addStatement(Assignment $statement): void
    {
        $this->statements[] = $statement;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitBlock($this);
    }
    
    /**
     * Evaluate block as expression (creates Block object)
     * 
     * @param Environment $env Runtime environment
     * @param SOLObject $self Self object for context
     * @return SOLObject Result of expression evaluation
     */
    public function evaluate(Environment $env, SOLObject $self): \IPP\Student\Runtime\SOL\SOLObject
    {
        // Vytvoření nové instance SOLBlock
        return $env->getObjectFactory()->createBlock($this);
    }
}