<?php

namespace IPP\Student\AST;

/**
 * AST node representing a variable reference
 */
class Variable extends Expression
{
    /**
     * @param string $name Variable name
     */
    public function __construct(
        private string $name
    ) {
    }

    /**
     * Get variable name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitVariable($this);
    }
    
    /**
     * Evaluate the variable expression
     * 
     * @param \IPP\Student\Runtime\Environment $env Runtime environment
     * @param \IPP\Student\Runtime\SOL\SOLObject $self Self object for context
     * @return \IPP\Student\Runtime\SOL\SOLObject Result of expression evaluation
     */
    public function evaluate(\IPP\Student\Runtime\Environment $env, \IPP\Student\Runtime\SOL\SOLObject $self): \IPP\Student\Runtime\SOL\SOLObject
    {
        // Special variables: self, super, true, false, nil
        switch ($this->name) {
            case 'self':
                return $self;
            case 'super':
                // Super také odkazuje na self, ale při zaslání zprávy se začíná hledat v nadtřídě
                return $self;
            case 'true':
                return $env->getObjectFactory()->getTrue();
            case 'false':
                return $env->getObjectFactory()->getFalse();
            case 'nil':
                return $env->getObjectFactory()->getNil();
        }
        
        // Normální proměnná - vyhledat v aktuálním rámci
        return $env->lookupVariable($this->name);
    }
}