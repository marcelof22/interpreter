<?php

namespace IPP\Student\Runtime\BuiltIn;

use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Exception\ValueErrorException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\Interpreter;
use IPP\Student\Runtime\ObjectFactory;
use IPP\Student\Runtime\SOL\SOLBlock;
use IPP\Student\Runtime\SOL\SOLBoolean;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Runtime\SOL\SOLInteger;
use IPP\Student\Runtime\SOL\SOLNil;
use IPP\Student\Runtime\SOL\SOLObject;
use IPP\Student\Runtime\SOL\SOLString;

/**
 * Initializer for built-in classes
 */
class BuiltInClassInitializer
{
    /**
     * Initialize all built-in classes
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @return void
     */
    public function initializeBuiltInClasses($registry, $factory): void
    {
        // Initialize built-in classes in the correct order (Object first)
        $objectClass = $this->initializeObjectClass($registry, $factory);
        
        // Set ClassRegistry for Object (important for lookups)
        $objectClass->setClassRegistry($registry);
        
        $nilClass = $this->initializeNilClass($registry, $factory, $objectClass);
        $booleanClasses = $this->initializeBooleanClasses($registry, $factory, $objectClass);
        $integerClass = $this->initializeIntegerClass($registry, $factory, $objectClass);
        $stringClass = $this->initializeStringClass($registry, $factory, $objectClass);
        $blockClass = $this->initializeBlockClass($registry, $factory, $objectClass);
    }
    
    /**
     * Initialize the Object class
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry The class registry
     * @param ObjectFactory $factory Object factory
     * @return SOLClass The Object class
     */
    private function initializeObjectClass($registry, $factory): SOLClass
    {
        // Create Object class
        $objectClass = new SOLClass('Object');
        $registry->registerClass($objectClass);
        
        // Register built-in methods
        $this->registerObjectMethods($objectClass, $factory);
        
        return $objectClass;
    }
    
    /**
     * Initialize the Nil class
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @param SOLClass $objectClass The Object class
     * @return SOLClass The Nil class
     */
    private function initializeNilClass($registry, $factory, $objectClass): SOLClass
    {
        // Create Nil class
        $nilClass = new SOLClass('Nil');
        $nilClass->setParent($objectClass);
        $registry->registerClass($nilClass);
        
        // Register built-in methods
        $this->registerNilMethods($nilClass, $factory);
        
        return $nilClass;
    }
    
    /**
     * Initialize the Boolean classes (True and False)
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @param SOLClass $objectClass The Object class
     * @return array<string, SOLClass> Array of Boolean classes ["True" => $trueClass, "False" => $falseClass]
     */
    private function initializeBooleanClasses($registry, $factory, $objectClass): array
    {
        // Create True class
        $trueClass = new SOLClass('True');
        $trueClass->setParent($objectClass);
        $registry->registerClass($trueClass);
        
        // Create False class
        $falseClass = new SOLClass('False');
        $falseClass->setParent($objectClass);
        $registry->registerClass($falseClass);
        
        // Register built-in methods
        $this->registerBooleanMethods($trueClass, $falseClass, $factory);
        
        return ["True" => $trueClass, "False" => $falseClass];
    }
    
    /**
     * Initialize the Integer class
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @param SOLClass $objectClass The Object class
     * @return SOLClass The Integer class
     */
    private function initializeIntegerClass($registry, $factory, $objectClass): SOLClass
    {
        // Create Integer class
        $integerClass = new SOLClass('Integer');
        $integerClass->setParent($objectClass);
        $registry->registerClass($integerClass);
        
        // Register built-in methods
        $this->registerIntegerMethods($integerClass, $factory);
        
        return $integerClass;
    }
    
    /**
     * Initialize the String class
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @param SOLClass $objectClass The Object class
     * @return SOLClass The String class
     */
    private function initializeStringClass($registry, $factory, $objectClass): SOLClass
    {
        // Create String class
        $stringClass = new SOLClass('String');
        $stringClass->setParent($objectClass);
        $registry->registerClass($stringClass);
        
        // Register built-in methods
        $this->registerStringMethods($stringClass, $factory);
        
        return $stringClass;
    }
    
    /**
     * Initialize the Block class
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     * @param ObjectFactory $factory Object factory
     * @param SOLClass $objectClass The Object class
     * @return SOLClass The Block class
     */
    private function initializeBlockClass($registry, $factory, $objectClass): SOLClass
    {
        // Create Block class
        $blockClass = new SOLClass('Block');
        $blockClass->setParent($objectClass);
        $registry->registerClass($blockClass);
        
        // Register built-in methods
        $this->registerBlockMethods($blockClass, $factory);
        
        return $blockClass;
    }
    
    /**
     * Register methods for the Object class
     * 
     * @param SOLClass $objectClass The Object class
     * @param ObjectFactory $factory Object factory
     */
    private function registerObjectMethods(SOLClass $objectClass, ObjectFactory $factory): void
    {
        $registry = $objectClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
        
            // Přidejme novou metodu
        $registry->registerClassMethod('Object', 'new', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Class method new expected 0 arguments');
                }
                
                if (!($receiver instanceof SOLClass)) {
                    throw new TypeErrorException('Receiver of new must be a Class');
                }
                
                return $receiver->newInstance();
            })
        );

        // identicalTo:
        $registry->registerMethod('Object', 'identicalTo:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method identicalTo: expected 1 argument');
                }
                
                $other = $arguments[0];
                $result = $receiver === $other;
                
                return $result ? $factory->getTrue() : $factory->getFalse();
            })
        );
        
        // equalTo:
        $registry->registerMethod('Object', 'equalTo:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method equalTo: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                // Default implementation: use identicalTo:
                $result = $receiver === $other;
                
                return $result ? $factory->getTrue() : $factory->getFalse();
            })
        );
        
        // asString
        $registry->registerMethod('Object', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                // Default implementation: return empty string
                return $factory->createString('');
            })
        );
        
        // Type checking methods
        $registry->registerMethod('Object', 'isNumber', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getFalse();
            })
        );
        
        $registry->registerMethod('Object', 'isString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getFalse();
            })
        );
        
        $registry->registerMethod('Object', 'isBlock', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getFalse();
            })
        );
        
        $registry->registerMethod('Object', 'isNil', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getFalse();
            })
        );
        
        // Class methods (constructors)
        
        // new
        $registry->registerClassMethod('Object', 'new', 
            new SimpleBuiltInMethod(function(Environment $env, SOLClass $receiver, array $arguments) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Class method new expected 0 arguments');
                }
                
                return $receiver->newInstance();
            })
        );
        
        // from:
        $registry->registerClassMethod('Object', 'from:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLClass $receiver, array $arguments) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Class method from: expected 1 argument');
                }
                
                $obj = $arguments[0];
                $objClass = $obj->getClass();
                
                // Check if obj's class is compatible with receiver
                if (!($objClass->isSubclassOf($receiver) || $receiver->isSubclassOf($objClass))) {
                    throw new DoNotUnderstandException("Incompatible class for from: method");
                }
                
                // Create a new instance and copy attributes
                $newInstance = $receiver->newInstance();
                
                return $newInstance;
            })
        );
    }
    
    /**
     * Register methods for the Nil class
     * 
     * @param SOLClass $nilClass The Nil class
     * @param ObjectFactory $factory Object factory
     */
    private function registerNilMethods(SOLClass $nilClass, ObjectFactory $factory): void
    {
        $registry = $nilClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
        
        // asString
        $registry->registerMethod('Nil', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                return $factory->createString('nil');
            })
        );
        
        // isNil (redefinováno pro Nil)
        $registry->registerMethod('Nil', 'isNil', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getTrue();
            })
        );
    }
    
    /**
     * Register methods for the Boolean classes
     * 
     * @param SOLClass $trueClass The True class
     * @param SOLClass $falseClass The False class
     * @param ObjectFactory $factory Object factory
     */
    private function registerBooleanMethods(SOLClass $trueClass, SOLClass $falseClass, ObjectFactory $factory): void
    {
        $registry = $trueClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
        
        // Metody pro True
        
        // not
        $registry->registerMethod('True', 'not', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method not expected 0 arguments');
                }
                
                return $factory->getFalse();
            })
        );
        
        // and:
        $registry->registerMethod('True', 'and:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method and: expected 1 argument');
                }
                
                $block = $arguments[0];
                
                // Vyhodnotí argument (blok) zasláním zprávy value
                $result = $block->sendMessage('value', []);
                return $result;
            })
        );
        
        // or:
        $registry->registerMethod('True', 'or:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method or: expected 1 argument');
                }
                
                // Je-li příjemce true, vrací true bez vyhodnocení argumentu
                return $factory->getTrue();
            })
        );
        
        // ifTrue:ifFalse:
        $registry->registerMethod('False', 'ifTrue:ifFalse:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 2) {
                    throw new DoNotUnderstandException('Method ifTrue:ifFalse: expected 2 arguments');
                }
                
                $falseBlock = $arguments[1];
                
                // Vyhodnotí druhý argument (false blok)
                $result = $falseBlock->sendMessage('value', []);
                return $result;
            })
        );
        
        // asString
        $registry->registerMethod('True', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                return $factory->createString('true');
            })
        );
        
        // Metody pro False
        
        // not
        $registry->registerMethod('False', 'not', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method not expected 0 arguments');
                }
                
                return $factory->getTrue();
            })
        );
        
        // and:
        $registry->registerMethod('False', 'and:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method and: expected 1 argument');
                }
                
                // Je-li příjemce false, vrací false bez vyhodnocení argumentu
                return $factory->getFalse();
            })
        );
        
        // or:
        $registry->registerMethod('False', 'or:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method or: expected 1 argument');
                }
                
                $block = $arguments[0];
                
                // Vyhodnotí argument (blok) zasláním zprávy value
                $result = $block->sendMessage('value', []);
                return $result;
            })
        );
        
        // ifTrue:ifFalse:
            $registry->registerMethod('False', 'ifTrue:ifFalse:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 2) {
                    throw new DoNotUnderstandException('Method ifTrue:ifFalse: expected 2 arguments');
                }
                
                $falseBlock = $arguments[1];
                
                // Vyhodnotí druhý argument (false blok)
                $result = $falseBlock->sendMessage('value', []);
                return $result;
            })
        );
        
        // asString
        $registry->registerMethod('False', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                return $factory->createString('false');
            })
        );
    }
    
    /**
     * Register methods for the Integer class
     * 
     * @param SOLClass $integerClass The Integer class
     * @param ObjectFactory $factory Object factory
     */
    private function registerIntegerMethods(SOLClass $integerClass, ObjectFactory $factory): void
    {
        $registry = $integerClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
        
        // equalTo:
        $registry->registerMethod('Integer', 'equalTo:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method equalTo: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if ($other instanceof SOLInteger && $receiver instanceof SOLInteger) {
                    $result = $receiver->getValue() === $other->getValue();
                    return $result ? $factory->getTrue() : $factory->getFalse();
                }
                
                return $factory->getFalse();
            })
        );
        
        // greaterThan:
        $registry->registerMethod('Integer', 'greaterThan:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method greaterThan: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of greaterThan: must be an Integer');
                }
                
                if (!($other instanceof SOLInteger)) {
                    throw new TypeErrorException('Argument of greaterThan: must be an Integer');
                }
                
                $result = $receiver->getValue() > $other->getValue();
                return $result ? $factory->getTrue() : $factory->getFalse();
            })
        );
        
        // plus:
        $registry->registerMethod('Integer', 'plus:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method plus: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of plus: must be an Integer');
                }
                
                if (!($other instanceof SOLInteger)) {
                    throw new TypeErrorException('Argument of plus: must be an Integer');
                }
                
                $result = $receiver->getValue() + $other->getValue();
                return $factory->createInteger($result);
            })
        );
        
        // minus:
        $registry->registerMethod('Integer', 'minus:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method minus: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of minus: must be an Integer');
                }
                
                if (!($other instanceof SOLInteger)) {
                    throw new TypeErrorException('Argument of minus: must be an Integer');
                }
                
                $result = $receiver->getValue() - $other->getValue();
                return $factory->createInteger($result);
            })
        );
        
        // multiplyBy:
        $registry->registerMethod('Integer', 'multiplyBy:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method multiplyBy: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of multiplyBy: must be an Integer');
                }
                
                if (!($other instanceof SOLInteger)) {
                    throw new TypeErrorException('Argument of multiplyBy: must be an Integer');
                }
                
                $result = $receiver->getValue() * $other->getValue();
                return $factory->createInteger($result);
            })
        );
        
        // divBy:
        $registry->registerMethod('Integer', 'divBy:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method divBy: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of divBy: must be an Integer');
                }
                
                if (!($other instanceof SOLInteger)) {
                    throw new TypeErrorException('Argument of divBy: must be an Integer');
                }
                
                if ($other->getValue() === 0) {
                    throw new ValueErrorException('Division by zero');
                }
                
                $result = intdiv($receiver->getValue(), $other->getValue());
                return $factory->createInteger($result);
            })
        );
        
        // asString
        $registry->registerMethod('Integer', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of asString must be an Integer');
                }
                
                return $factory->createString((string)$receiver->getValue());
            })
        );
        
        // asInteger
        $registry->registerMethod('Integer', 'asInteger', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asInteger expected 0 arguments');
                }
                
                return $receiver; // Vrátí sebe sama
            })
        );
        
        // timesRepeat:
        $registry->registerMethod('Integer', 'timesRepeat:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method timesRepeat: expected 1 argument');
                }
                
                $block = $arguments[0];
                
                if (!($receiver instanceof SOLInteger)) {
                    throw new TypeErrorException('Receiver of timesRepeat: must be an Integer');
                }
                
                $times = $receiver->getValue();
                
                // Pokud číslo není kladné, neprovádět blok
                if ($times <= 0) {
                    return $factory->getNil();
                }
                
                // Provedení bloku opakovaně
                $lastResult = $factory->getNil();
                for ($i = 1; $i <= $times; $i++) {
                    $iterArg = $factory->createInteger($i);
                    $lastResult = $block->sendMessage('value:', [$iterArg]);
                }
                
                return $lastResult;
            })
        );
        
        // isNumber (redefinováno pro Integers)
        $registry->registerMethod('Integer', 'isNumber', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                return $env->getObjectFactory()->getTrue();
            })
        );
    }
    
    /**
     * Register methods for the String class
     * 
     * @param SOLClass $stringClass The String class
     * @param ObjectFactory $factory Object factory
     */
    private function registerStringMethods(SOLClass $stringClass, ObjectFactory $factory): void
    {
        $registry = $stringClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
        
        // Redefinovat asString
        $registry->registerMethod('String', 'asString', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asString expected 0 arguments');
                }
                
                return $receiver; // Vrátí sebe sama
            })
        );
        
        // print
        $registry->registerMethod('String', 'print', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method print expected 0 arguments');
                }
                
                // Vytisknout hodnotu řetězce na výstup
                $io = Interpreter::getInstance()->getIO();
                $io->write(($receiver instanceof SOLString) ? $receiver->getValue() : (string)$receiver);
                
                return $receiver; // Vrátí sebe sama
            })
        );
        
        // equalTo:
        $registry->registerMethod('String', 'equalTo:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method equalTo: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if ($other instanceof SOLString) {
                    $result = ($receiver instanceof SOLString) && 
                              ($receiver->getValue() === $other->getValue());
                    return $result ? $factory->getTrue() : $factory->getFalse();
                }
                
                return $factory->getFalse();
            })
        );
        
        // asInteger
        $registry->registerMethod('String', 'asInteger', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 0) {
                    throw new DoNotUnderstandException('Method asInteger expected 0 arguments');
                }
                
                if (!($receiver instanceof SOLString)) {
                    throw new TypeErrorException('Receiver of asInteger must be a String');
                }
                
                $value = $receiver->getValue();
                
                // Zkusit převést na celé číslo
                if (preg_match('/^[+-]?\d+$/', $value)) {
                    return $factory->createInteger((int)$value);
                }
                
                // Nelze převést
                return $factory->getNil();
            })
        );
        
        // concatenateWith:
        $registry->registerMethod('String', 'concatenateWith:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 1) {
                    throw new DoNotUnderstandException('Method concatenateWith: expected 1 argument');
                }
                
                $other = $arguments[0];
                
                if (!($receiver instanceof SOLString)) {
                    throw new TypeErrorException('Receiver of concatenateWith: must be a String');
                }
                
                if (!($other instanceof SOLString)) {
                    return $factory->getNil(); // Vrátit nil, pokud argument není String
                }
                
                $concatenated = $receiver->getValue() . $other->getValue();
                return $factory->createString($concatenated);
            })
        );
        
        // startsWith:endsBefore:
        $registry->registerMethod('String', 'startsWith:endsBefore:', 
            new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
                if (count($arguments) !== 2) {
                    throw new DoNotUnderstandException('Method startsWith:endsBefore: expected 2 arguments');
                }
                
                $start = $arguments[0];
                $end = $arguments[1];
                
                if (!($receiver instanceof SOLString)) {
                    throw new TypeErrorException('Receiver of startsWith:endsBefore: must be a String');
                }
                
                if (!($start instanceof SOLInteger) || !($end instanceof SOLInteger)) {
                    return $factory->getNil(); // Vrátit nil, pokud argumenty nejsou Integer
                }
                
                $startIndex = $start->getValue();
                $endIndex = $end->getValue();
                
                // Kontrola, zda jsou indexy kladné
                if ($startIndex <= 0 || $endIndex <= 0) {
                    return $factory->getNil();
                }
                
                // Výpočet substring (v SOL25 se indexuje od 1)
                if ($endIndex <= $startIndex) {
                    return $factory->createString(''); // Vrátit prázdný řetězec, pokud je rozdíl argumentů <= 0
                }
                
                $string = $receiver->getValue();
                $length = $endIndex - $startIndex;
                
                // Ošetřit indexy, které jsou mimo rozsah
                if ($startIndex > mb_strlen($string)) {
                    return $factory->createString('');
                }
                
                // Získat podřetězec (indexování v PHP je od 0, v SOL25 od 1)
                $substring = mb_substr($string, $startIndex - 1, $length);
                return $factory->createString($substring);
            })
        );

       // isString (redefinováno pro Strings)
       $registry->registerMethod('String', 'isString', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               return $env->getObjectFactory()->getTrue();
           })
       );
       
       // Třídní metoda read
       $registry->registerClassMethod('String', 'read', 
           new SimpleBuiltInMethod(function(Environment $env, SOLClass $receiver, array $arguments) {
               if (count($arguments) !== 0) {
                   throw new DoNotUnderstandException('Class method read expected 0 arguments');
               }
               
               // Přečíst řádek ze vstupu
               $line = Interpreter::getInstance()->getIO()->readLine();
               
               if ($line === null) {
                   return $env->getObjectFactory()->createString(''); // Prázdný řetězec při EOF
               }
               
               return $env->getObjectFactory()->createString($line);
           })
       );
   }
   
   /**
    * Register methods for the Block class
    * 
    * @param SOLClass $blockClass The Block class
    * @param ObjectFactory $factory Object factory
    */
   private function registerBlockMethods(SOLClass $blockClass, ObjectFactory $factory): void
   {
       $registry = $blockClass->getClass()->getClassRegistry()->getBuiltInMethodRegistry();
       
       // value (pro bezparametrický blok)
       $registry->registerMethod('Block', 'value', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               if (count($arguments) !== 0) {
                   throw new DoNotUnderstandException('Method value expected 0 arguments');
               }
               
               if (!($receiver instanceof SOLBlock)) {
                   throw new TypeErrorException('Receiver of value must be a Block');
               }
               
               $block = $receiver->getBlock();
               if ($block->getArity() !== 0) {
                   throw new DoNotUnderstandException('Block arity mismatch: expected 0 parameters');
               }
               
               $interpreter = Interpreter::getInstance();
               $currentFrame = $env->getCurrentFrame();
               if ($currentFrame === null) {
                   throw new TypeErrorException('No current frame for block execution');
               }
               return $interpreter->executeBlock($block, $currentFrame->getSelf());
           })
       );
       
       // value: (pro blok s jedním parametrem)
       $registry->registerMethod('Block', 'value:', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               if (count($arguments) !== 1) {
                   throw new DoNotUnderstandException('Method value: expected 1 argument');
               }
               
               if (!($receiver instanceof SOLBlock)) {
                   throw new TypeErrorException('Receiver of value: must be a Block');
               }
               
               $block = $receiver->getBlock();
               if ($block->getArity() !== 1) {
                   throw new DoNotUnderstandException('Block arity mismatch: expected 1 parameter');
               }
               
               $interpreter = Interpreter::getInstance();
               $currentFrame = $env->getCurrentFrame();
               if ($currentFrame === null) {
                   throw new TypeErrorException('No current frame for block execution');
               }
               return $interpreter->executeBlock($block, $currentFrame->getSelf(), $arguments);
           })
       );
       
       // value:value: (pro blok se dvěma parametry)
       $registry->registerMethod('Block', 'value:value:', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               if (count($arguments) !== 2) {
                   throw new DoNotUnderstandException('Method value:value: expected 2 arguments');
               }
               
               if (!($receiver instanceof SOLBlock)) {
                   throw new TypeErrorException('Receiver of value:value: must be a Block');
               }
               
               $block = $receiver->getBlock();
               if ($block->getArity() !== 2) {
                   throw new DoNotUnderstandException('Block arity mismatch: expected 2 parameters');
               }
               
               $interpreter = Interpreter::getInstance();
               $currentFrame = $env->getCurrentFrame();
               if ($currentFrame === null) {
                   throw new TypeErrorException('No current frame for block execution');
               }
               return $interpreter->executeBlock($block, $currentFrame->getSelf(), $arguments);
           })
       );
       
       // value:value:value: (pro blok se třemi parametry)
       $registry->registerMethod('Block', 'value:value:value:', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               if (count($arguments) !== 3) {
                   throw new DoNotUnderstandException('Method value:value:value: expected 3 arguments');
               }
               
               if (!($receiver instanceof SOLBlock)) {
                   throw new TypeErrorException('Receiver of value:value:value: must be a Block');
               }
               
               $block = $receiver->getBlock();
               if ($block->getArity() !== 3) {
                   throw new DoNotUnderstandException('Block arity mismatch: expected 3 parameters');
               }
               
               $interpreter = Interpreter::getInstance();
               $currentFrame = $env->getCurrentFrame();
               if ($currentFrame === null) {
                   throw new TypeErrorException('No current frame for block execution');
               }
               return $interpreter->executeBlock($block, $currentFrame->getSelf(), $arguments);
           })
       );
       
       // whileTrue:
       $registry->registerMethod('Block', 'whileTrue:', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) use ($factory) {
               if (count($arguments) !== 1) {
                   throw new DoNotUnderstandException('Method whileTrue: expected 1 argument');
               }
               
               if (!($receiver instanceof SOLBlock)) {
                   throw new TypeErrorException('Receiver of whileTrue: must be a Block');
               }
               
               $conditionBlock = $receiver;
               $bodyBlock = $arguments[0];
               
               // Vykonávat blok, dokud je podmínka true
               $lastResult = $factory->getNil();
               
               while (true) {
                   // Vyhodnotit podmínku
                   $condition = $conditionBlock->sendMessage('value', []);
                   
                   // Pokud není true, končíme
                   if ($condition !== $factory->getTrue()) {
                       break;
                   }
                   
                   // Vykonat tělo cyklu
                   $lastResult = $bodyBlock->sendMessage('value', []);
               }
               
               return $lastResult;
           })
       );
       
       // isBlock (redefinováno pro Block)
       $registry->registerMethod('Block', 'isBlock', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               return $env->getObjectFactory()->getTrue();
           })
       );
       
       // asString
       $registry->registerMethod('Block', 'asString', 
           new SimpleBuiltInMethod(function(Environment $env, SOLObject $receiver, array $arguments) {
               if (count($arguments) !== 0) {
                   throw new DoNotUnderstandException('Method asString expected 0 arguments');
               }
               
               return $env->getObjectFactory()->createString('a Block');
           })
       );
   }
}