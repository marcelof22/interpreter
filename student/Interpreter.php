<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Student\AST\Program;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Exception\ValueErrorException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\InputOutputHandler;
use IPP\Student\Runtime\Interpreter as RuntimeInterpreter;
use IPP\Student\XML\ASTBuilder;

/**
 * Main interpreter class that implements the IPP\Core\AbstractInterpreter
 */
class Interpreter extends AbstractInterpreter
{
    private ASTBuilder $astBuilder;
    private RuntimeInterpreter $interpreter;
    private InputOutputHandler $ioHandler;
    
    /**
     * Initialize the interpreter
     */
    protected function init(): void
    {
        parent::init();
        
        $this->astBuilder = new ASTBuilder();
        $this->interpreter = RuntimeInterpreter::getInstance();
        $this->ioHandler = new InputOutputHandler();
        
        // Set up I/O
        $this->ioHandler->setInput($this->input);
        $this->ioHandler->setOutput($this->stdout);
        $this->ioHandler->setError($this->stderr);
        
        // Set the I/O handler in the interpreter
        $this->interpreter->setIO($this->ioHandler);
    }
    
    /**
     * Execute the program
     */
    public function execute(): int
    {
        try {
            // Parsovanie XML na AST
            $xml = $this->source->getDOMDocument();
            $program = $this->astBuilder->buildFromXML($xml);
            
            // Vykonanie programu
            $this->interpreter->execute($program);
            
            // Úspech
            return ReturnCode::OK;
        } catch (DoNotUnderstandException $e) {
            // Runtime error - message not understood
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_DNU_ERROR;
        } catch (TypeErrorException $e) {
            // Runtime error - type error
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_TYPE_ERROR;
        } catch (ValueErrorException $e) {
            // Runtime error - value error
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_VALUE_ERROR;
        } catch (\IPP\Core\Exception\XMLException $e) {
            // XML parsing error
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INVALID_XML_ERROR; // Použiť správnu konštantu z ReturnCode
        } catch (\IPP\Student\Exception\InvalidSourceStructureException $e) {
            // Unexpected XML structure
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR; // Použiť správnu konštantu z ReturnCode
        } catch (\Exception $e) {
            // Other errors
            $this->stderr->writeString("Unexpected error: " . $e->getMessage() . PHP_EOL);
            return ReturnCode::INTERNAL_ERROR;
        }
    }
}