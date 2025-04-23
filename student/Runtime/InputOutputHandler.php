<?php

namespace IPP\Student\Runtime;

use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Runtime\SOL\SOLObject;
use IPP\Student\Runtime\SOL\SOLString;

/**
 * Handler for input and output operations
 */
class InputOutputHandler
{
    private ?InputReader $input = null;
    private ?OutputWriter $output = null;
    private ?OutputWriter $error = null;
    
    /**
     * Set the input reader
     */
    public function setInput(InputReader $input): void
    {
        $this->input = $input;
    }
    
    /**
     * Set the output writer
     */
    public function setOutput(OutputWriter $output): void
    {
        $this->output = $output;
    }
    
    /**
     * Set the error writer
     */
    public function setError(OutputWriter $error): void
    {
        $this->error = $error;
    }
    
    /**
     * Read a line from input
     * 
     * @return string|null The read line or null if EOF
     * @throws TypeErrorException If the input reader is not set
     */
    public function readLine(): ?string
    {
        if (!$this->input) {
            throw new TypeErrorException('Input reader not set');
        }
        
        return $this->input->readString();
    }
    
    /**
     * Write a string to output
     * 
     * @param string $text Text to write
     * @throws TypeErrorException If the output writer is not set
     */
    public function write(string $text): void
    {
        if (!$this->output) {
            throw new TypeErrorException('Output writer not set');
        }
        
        $this->output->writeString($text);
    }
    
    /**
     * Write a SOL object to output
     * 
     * @param SOLObject $object Object to write
     * @throws TypeErrorException If the output writer is not set
     */
    public function writeObject(SOLObject $object): void
    {
        if ($object instanceof SOLString) {
            $this->write($object->getValue());
        } else {
            $this->write((string)$object);
        }
    }
    
    /**
     * Write a string to error output
     * 
     * @param string $text Text to write
     * @throws TypeErrorException If the error writer is not set
     */
    public function writeError(string $text): void
    {
        if (!$this->error) {
            throw new TypeErrorException('Error writer not set');
        }
        
        $this->error->writeString($text);
    }
}