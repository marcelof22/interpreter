<?php

namespace IPP\Student\XML;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use IPP\Core\Exception\XMLException;
use IPP\Student\AST\Assignment;
use IPP\Student\AST\Block;
use IPP\Student\AST\ClassNode;
use IPP\Student\AST\Expression;
use IPP\Student\AST\Literal;
use IPP\Student\AST\MessageSend;
use IPP\Student\AST\Method;
use IPP\Student\AST\Parameter;
use IPP\Student\AST\Program;
use IPP\Student\AST\Variable;
use IPP\Student\Exception\InvalidSourceStructureException;
use Exception;

/**
 * Builder for creating AST from XML
 */
class ASTBuilder
{
    /**
     * Build AST from XML document
     * 
     * @param DOMDocument $xml XML document
     * @return Program Program AST node
     * @throws XMLException If the XML structure is invalid
     * @throws InvalidSourceStructureException If the source structure is invalid
     */
    public function buildFromXML(DOMDocument $xml): Program
    {
        try {
            $programElement = $xml->documentElement;
            
            if (!$programElement || $programElement->nodeName !== 'program') {
                throw new XMLException('Invalid XML: root element must be <program>');
            }
            
            $program = new Program();
            
            // Process class elements
            /** @var DOMNodeList<DOMNode> $classElements */
            $classElements = $programElement->getElementsByTagName('class');
            foreach ($classElements as $classElement) {
                if ($classElement instanceof DOMElement) {
                    $class = $this->processClass($classElement);
                    $program->addClass($class);
                }
            }
            
            return $program;
        } catch (InvalidSourceStructureException $e) {
            // Forward InvalidSourceStructureException
            throw $e;
        } catch (Exception $e) {
            // Convert other exceptions to XMLException
            throw new XMLException('Error processing XML: ' . $e->getMessage(), $e);
        }
    }
    
    /**
     * Process a class element
     * 
     * @param DOMElement $element XML class element
     * @return ClassNode Class AST node
     * @throws InvalidSourceStructureException If the class element is invalid
     */
    private function processClass(DOMElement $element): ClassNode
    {
        $name = $element->getAttribute('name');
        $parent = $element->getAttribute('parent');
        
        if (!$name || !$parent) {
            throw new InvalidSourceStructureException('Invalid class: missing name or parent attribute');
        }
        
        $class = new ClassNode($name, $parent);
        
        // Process method elements
        /** @var DOMNodeList<DOMNode> $methodElements */
        $methodElements = $element->getElementsByTagName('method');
        foreach ($methodElements as $methodElement) {
            if ($methodElement instanceof DOMElement) {
                $method = $this->processMethod($methodElement);
                $class->addMethod($method);
            }
        }
        
        return $class;
    }
    
    /**
     * Process a method element
     * 
     * @param DOMElement $element XML method element
     * @return Method Method AST node
     * @throws InvalidSourceStructureException If the method element is invalid
     */
    private function processMethod(DOMElement $element): Method
    {
        $selector = $element->getAttribute('selector');
        
        if (!$selector) {
            throw new InvalidSourceStructureException('Invalid method: missing selector attribute');
        }
        
        // Najdi přímý blok potomek
        $blockElement = null;
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === 'block') {
                if ($blockElement !== null) {
                    throw new InvalidSourceStructureException('Invalid method: must contain exactly one block');
                }
                $blockElement = $child;
            }
        }
        
        if ($blockElement === null) {
            throw new InvalidSourceStructureException('Invalid method: must contain exactly one block');
        }
        
        $block = $this->processBlock($blockElement);
        
        return new Method($selector, $block);
    }
    
    /**
     * Process a block element
     * 
     * @param DOMElement $element XML block element
     * @return Block Block AST node
     * @throws InvalidSourceStructureException If the block element is invalid
     */
    private function processBlock(DOMElement $element): Block
    {
        $arity = (int)$element->getAttribute('arity');
        $block = new Block($arity);
        
        // Process parameter elements
        /** @var DOMNodeList<DOMNode> $paramElements */
        $paramElements = $element->getElementsByTagName('parameter');
        $parameters = [];
        
        foreach ($paramElements as $paramElement) {
            if ($paramElement instanceof DOMElement) {
                $param = $this->processParameter($paramElement);
                $order = $param->getOrder();
                $parameters[$order] = $param;
            }
        }
        
        // Sort and add parameters by order
        ksort($parameters);
        foreach ($parameters as $param) {
            $block->addParameter($param);
        }
        
        // Process assignment elements
        /** @var DOMNodeList<DOMNode> $assignElements */
        $assignElements = $element->getElementsByTagName('assign');
        $statements = [];
        
        foreach ($assignElements as $assignElement) {
            if ($assignElement instanceof DOMElement) {
                $assign = $this->processAssignment($assignElement);
                $order = (int)$assignElement->getAttribute('order');
                $statements[$order] = $assign;
            }
        }
        
        // Sort and add statements by order
        ksort($statements);
        foreach ($statements as $statement) {
            $block->addStatement($statement);
        }
        
        return $block;
    }
    
    /**
     * Process a parameter element
     * 
     * @param DOMElement $element XML parameter element
     * @return Parameter Parameter AST node
     * @throws InvalidSourceStructureException If the parameter element is invalid
     */
    private function processParameter(DOMElement $element): Parameter
    {
        $name = $element->getAttribute('name');
        $order = (int)$element->getAttribute('order');
        
        if (!$name || $order < 1) {
            throw new InvalidSourceStructureException('Invalid parameter: missing name or invalid order');
        }
        
        return new Parameter($name, $order);
    }
    
    /**
     * Process an assignment element
     * 
     * @param DOMElement $element XML assignment element
     * @return Assignment Assignment AST node
     * @throws InvalidSourceStructureException If the assignment element is invalid
     */
    private function processAssignment(DOMElement $element): Assignment
    {
        // Get var element - looking only for direct children
        $varElement = null;
        $exprElement = null;
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                if ($childNode->nodeName === 'var') {
                    $varElement = $childNode;
                } else if ($childNode->nodeName === 'expr') {
                    $exprElement = $childNode;
                }
            }
        }
        
        if (!$varElement) {
            throw new InvalidSourceStructureException('Invalid assignment: must contain var element');
        }
        
        $varName = $varElement->getAttribute('name');
        
        if ($varName === '') {
            throw new InvalidSourceStructureException('Invalid assignment: variable has no name');
        }
        
        if (!$exprElement) {
            throw new InvalidSourceStructureException('Invalid assignment: must contain expr element');
        }
        
        $expression = $this->processExpression($exprElement);
        
        return new Assignment($varName, $expression);
    }
    
    /**
     * Process an expression element
     * 
     * @param DOMElement $element XML expression element
     * @return Expression Expression AST node
     * @throws InvalidSourceStructureException If the expression element is invalid
     */
    private function processExpression(DOMElement $element): Expression
    {
        // Check expression type based on child element
        /** @var DOMNodeList<DOMNode> $childNodes */
        $childNodes = $element->childNodes;
        
        foreach ($childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $nodeName = $childNode->nodeName;
                
                switch ($nodeName) {
                    case 'literal':
                        return $this->processLiteral($childNode);
                    case 'var':
                        return $this->processVariable($childNode);
                    case 'send':
                        return $this->processMessageSend($childNode);
                    case 'block':
                        return $this->processBlockExpression($childNode);
                    default:
                        throw new InvalidSourceStructureException("Invalid expression: unknown type '$nodeName'");
                }
            }
        }
        
        throw new InvalidSourceStructureException('Invalid expression: missing expression type');
    }
    
    /**
     * Process a literal element
     * 
     * @param DOMElement $element XML literal element
     * @return Literal Literal AST node
     * @throws InvalidSourceStructureException If the literal element is invalid
     */
    private function processLiteral(DOMElement $element): Literal
    {
        $class = $element->getAttribute('class');
        $value = $element->getAttribute('value');
        
        if ($class === '') {
            throw new InvalidSourceStructureException('Invalid literal: missing class attribute');
        }
        
        return new Literal($class, $value);
    }
    
    /**
     * Process a variable element
     * 
     * @param DOMElement $element XML variable element
     * @return Variable Variable AST node
     * @throws InvalidSourceStructureException If the variable element is invalid
     */
    private function processVariable(DOMElement $element): Variable
    {
        $name = $element->getAttribute('name');
        
        if ($name === '') {
            throw new InvalidSourceStructureException('Invalid variable: missing name attribute');
        }
        
        return new Variable($name);
    }
    
    /**
     * Process a message send element
     * 
     * @param DOMElement $element XML message send element
     * @return MessageSend MessageSend AST node
     * @throws InvalidSourceStructureException If the message send element is invalid
     */
    private function processMessageSend(DOMElement $element): MessageSend
    {
        $selector = $element->getAttribute('selector');
        
        if ($selector === '') {
            throw new InvalidSourceStructureException('Invalid message send: missing selector attribute');
        }
        
        // Get receiver expression - looking only for direct children
        $receiverExpr = null;
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement && $childNode->nodeName === 'expr' && !$receiverExpr) {
                $receiverExpr = $this->processExpression($childNode);
                break;
            }
        }
        
        if ($receiverExpr === null) {
            throw new InvalidSourceStructureException('Invalid message send: missing receiver expression');
        }
        
        $messageSend = new MessageSend($selector, $receiverExpr);
        
        // Process arguments - looking only for direct children
        $args = [];
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement && $childNode->nodeName === 'arg') {
                $order = (int)$childNode->getAttribute('order');
                
                // Find expr element in arg element
                $argExpr = null;
                foreach ($childNode->childNodes as $argChild) {
                    if ($argChild instanceof DOMElement && $argChild->nodeName === 'expr') {
                        $argExpr = $this->processExpression($argChild);
                        break;
                    }
                }
                
                if ($argExpr === null) {
                    throw new InvalidSourceStructureException('Invalid message send: argument must contain expr');
                }
                
                $args[$order] = $argExpr;
            }
        }
        
        // Sort and add arguments by order
        ksort($args);
        foreach ($args as $argExpr) {
            $messageSend->addArgument($argExpr);
        }
        
        return $messageSend;
    }
    
    /**
     * Process a block expression element
     * 
     * @param DOMElement $element XML block element
     * @return Block Block AST node
     * @throws InvalidSourceStructureException If the block element is invalid
     */
    private function processBlockExpression(DOMElement $element): Block
    {
        return $this->processBlock($element);
    }
}