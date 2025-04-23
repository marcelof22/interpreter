<?php

namespace IPP\Student\Runtime;

use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Execution frame for a method or block invocation
 */
class Frame
{
    /** @var array<string, SOLObject> */
    private array $variables = [];
    
    /**
     * Create a new frame
     * 
     * @param SOLObject $self The self object for this frame
     * @param Frame|null $parent The parent frame
     * @param bool $isSuper Whether this is a super call
     */
    public function __construct(
        private SOLObject $self,
        private ?Frame $parent = null,
        private bool $isSuper = false
    ) {
        // Initialize self and super pseudo-variables
        $this->variables['self'] = $self;
        
        // Note: we actually store the same object for self and super,
        // but the difference is handled during message sending
    }
    
    /**
     * Get the self object
     */
    public function getSelf(): SOLObject
    {
        return $this->self;
    }
    
    /**
     * Check if this is a super call frame
     */
    public function isSuper(): bool
    {
        return $this->isSuper;
    }
    
    /**
     * Get the parent frame
     */
    public function getParent(): ?Frame
    {
        return $this->parent;
    }
    
    /**
     * Get a variable from this frame
     * 
     * @param string $name Variable name
     * @return SOLObject|null Variable value or null if not found
     */
    public function getVariable(string $name): ?SOLObject
    {
        return $this->variables[$name] ?? null;
    }
    
    /**
     * Set a variable in this frame
     * 
     * @param string $name Variable name
     * @param SOLObject $value Variable value
     */
    public function setVariable(string $name, SOLObject $value): void
    {
        $this->variables[$name] = $value;
    }
    
    /**
     * Check if a parameter name conflicts with any existing parameter
     * 
     * @param string $name Parameter name
     * @return bool True if parameter name conflicts
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->variables[$name]);
    }
}