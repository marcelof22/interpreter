Implementační dokumentace k 2. úloze do IPP 2024/2025
Jméno a příjmení: 
Login: 

## Úvod

Tato dokumentace popisuje návrh a implementaci interpretu jazyka SOL25, který zpracovává XML reprezentaci abstraktního syntaktického stromu (AST) a interpretuje program v jazyce SOL25. Implementace je napsána v jazyce PHP 8.4 a používá objektově orientovaný přístup s využitím návrhových vzorů.

## Struktura implementace

Implementace je rozdělena do několika logických celků:

1. **Zpracování XML** - třída `ASTBuilder` pro načtení a zpracování XML reprezentace AST
2. **AST** - třídy reprezentující abstraktní syntaktický strom programu
3. **Runtime prostředí** - třídy pro běhové prostředí (environment, frame, class registry)
4. **SOL objekty** - třídy reprezentující objekty jazyka SOL25 za běhu
5. **Interpret** - třída pro interpretaci programu

### Zpracování XML

Třída `ASTBuilder` je zodpovědná za načtení XML reprezentace AST a vytvoření odpovídající struktury objektů AST. Používá nativní PHP DOM API pro zpracování XML.

### AST (Abstraktní syntaktický strom)

Abstraktní syntaktický strom je reprezentován pomocí hierarchie tříd, kde základní třídou je `Node`. Všechny uzly AST implementují vzor Visitor, který umožňuje snadné procházení a vyhodnocování stromu. Hlavní třídy AST jsou:

- `Program` - reprezentuje celý program
- `ClassNode` - reprezentuje definici třídy
- `Method` - reprezentuje definici metody
- `Block` - reprezentuje blok kódu
- `Parameter` - reprezentuje parametr bloku
- `Assignment` - reprezentuje příkaz přiřazení
- `Expression` - abstraktní třída pro výrazy
  - `Literal` - reprezentuje literály (čísla, řetězce, true, false, nil)
  - `Variable` - reprezentuje proměnné
  - `MessageSend` - reprezentuje zasílání zpráv

### Runtime prostředí

Runtime prostředí tvoří několik klíčových tříd:

- `Environment` - hlavní třída pro běhové prostředí, spravuje aktuální frame a registry
- `Frame` - reprezentuje rámec pro volání metody nebo bloku, obsahuje lokální proměnné
- `ClassRegistry` - registr všech tříd v programu
- `ObjectFactory` - továrna pro vytváření instancí objektů

### SOL objekty

Objekty jazyka SOL25 jsou reprezentovány hierarchií tříd:

- `SOLObject` - základní třída pro všechny objekty
- `SOLClass` - reprezentuje třídu
- `SOLInteger` - reprezentuje celé číslo
- `SOLString` - reprezentuje řetězec
- `SOLBoolean` - reprezentuje booleovskou hodnotu
- `SOLNil` - reprezentuje hodnotu nil
- `SOLBlock` - reprezentuje blok kódu

### Interpret

Hlavní třídy interpretu:

- `Interpreter` (v namespacu `IPP\Student`) - vstupní bod interpretu, propojuje jednotlivé části
- `RuntimeInterpreter` - implementuje zpracování jednotlivých uzlů AST

## Použité návrhové vzory

V implementaci jsem použil následující návrhové vzory:

1. **Interpreter Pattern** - pro interpretaci abstraktního syntaktického stromu. Tento vzor je přirozenou volbou pro implementaci interpretu jazyka, kde různé typy uzlů AST jsou reprezentovány různými třídami a interpretace spočívá v navštívení všech uzlů stromu a vykonání odpovídajících akcí.

2. **Visitor Pattern** - pro procházení AST. Všechny uzly AST implementují metodu `accept()`, která přijímá objekt typu `NodeVisitor`. Konkrétní implementace `NodeVisitor` pak obsahuje metody pro návštěvu jednotlivých typů uzlů. Tento vzor umožňuje oddělit algoritmus pro procházení stromu od operací prováděných na uzlech.

3. **Factory Method Pattern** - implementovaný v třídě `ObjectFactory`, která je zodpovědná za vytváření instancí SOL objektů. Tento vzor umožňuje centralizovat logiku vytváření různých typů objektů a zajistit správnou inicializaci objektů.

4. **Singleton Pattern** - použitý pro třídu `RuntimeInterpreter`, aby byl v celém programu pouze jeden instance interpretu. Tento vzor jsem zvolil, protože interpret musí udržovat globální stav programu (např. registry tříd, aktuální frame) a je žádoucí, aby všechny části programu pracovaly se stejným stavem.

## Postup interpretace

Interpretace programu probíhá v následujících krocích:

1. Načtení XML reprezentace AST pomocí třídy `ASTBuilder`
2. Inicializace runtime prostředí a registrace vestavěných tříd
3. Registrace uživatelsky definovaných tříd z AST
4. Vyhledání třídy `Main` a metody `run`
5. Vytvoření instance třídy `Main`
6. Zavolání metody `run` na této instanci
7. Interpretace příkazů v metodě `run`

## Implementace vestavěných tříd

V jazyce SOL25 je několik vestavěných tříd:

- **Object** - základní třída, od které dědí všechny ostatní třídy
- **Integer** - třída pro celá čísla
- **String** - třída pro řetězce
- **Block** - třída pro bloky kódu
- **True** a **False** - třídy pro booleovské hodnoty
- **Nil** - třída pro hodnotu nil

Každá vestavěná třída implementuje své specifické metody podle specifikace jazyka SOL25. Například třída `Integer` implementuje metody pro aritmetické operace (`plus:`, `minus:`, `multiplyBy:`, `divBy:`) a porovnávání (`greaterThan:`).

## Zasílání zpráv

Zasílání zpráv je klíčovou součástí jazyka SOL25. V mé implementaci je realizováno metodou `sendMessage()` třídy `SOLObject`. Pro zpracování zprávy je potřeba:

1. Vyhodnotit příjemce zprávy (receiver)
2. Vyhodnotit argumenty zprávy (pokud existují)
3. Vyhledat odpovídající metodu v třídě příjemce nebo v jeho nadřazených třídách
4. Vytvořit nový frame pro vykonání metody
5. Navázat argumenty na parametry metody
6. Vykonat tělo metody

## Specifické implementační detaily

1. **Práce s bloky** - Bloky jsou v SOL25 objekty první třídy a mohou být předávány jako argumenty nebo uloženy v proměnných. Pro vyhodnocení bloku se používá metoda `execute()` třídy `SOLBlock`.

2. **Detekce chyb** - Implementace obsahuje robustní detekci chyb s odpovídajícími chybovými kódy podle zadání:
   - `51` - příjemce nerozumí zaslané zprávě
   - `52` - jiné běhové chyby
   - `53` - chyba hodnoty argumentu (např. dělení nulou)

3. **Vstup a výstup** - Vstup a výstup je realizován prostřednictvím třídy `InputOutputHandler`, která zapouzdřuje práci s rozhraními `InputReader` a `OutputWriter` z rámce ipp-core.

## UML diagram

Následující UML diagram znázorňuje hlavní třídy interpretu a jejich vztahy:

```
+-----------------------------+                +------------------------+
|   IPP::Student::Interpreter |--------------->|XML::ASTBuilder        |
+-----------------------------+                +------------------------+
| -astBuilder: ASTBuilder     |                | +buildFromXML()       |
| -interpreter: Interpreter   |                +------------------------+
| -ioHandler: IOHandler       |                         |
+-----------------------------+                         |
| +execute(): int             |                         V
+-----------------------------+                +------------------------+
            |                                  |AST::Program           |
            |                                  +------------------------+
            |                                  | -classes: ClassNode[] |
            V                                  +------------------------+
+-----------------------------+                         |
|Runtime::Interpreter         |                         |
+-----------------------------+                         V
| -environment: Environment   |                +------------------------+
| -io: InputOutputHandler     |                |AST::ClassNode         |
+-----------------------------+                +------------------------+
| +execute(Program): SOLObject|                | -name: string         |
| +visitXXX(): mixed          |                | -parent: string       |
+-----------------------------+                | -methods: Method[]    |
            |                                  +------------------------+
            |                                           |
            V                                           V
+-----------------------------+                +------------------------+
|Runtime::Environment         |                |AST::Method            |
+-----------------------------+                +------------------------+
| -currentFrame: Frame        |                | -selector: string     |
| -classRegistry: ClassRegistry|               | -body: Block          |
| -objectFactory: ObjectFactory|               +------------------------+
+-----------------------------+                         |
| +defineVariable()           |                         |
| +lookupVariable()           |                         V
| +enterFrame()               |                +------------------------+
| +exitFrame()                |                |AST::Block             |
+-----------------------------+                +------------------------+
     |          |                              | -arity: int           |
     |          |                              | -parameters: Parameter[]|
     |          |                              | -statements: Assignment[]|
     |          |                              +------------------------+
     |          |                                       |
     |          V                                       V
     |  +------------------------+            +------------------------+
     |  |Runtime::Frame          |            |AST::Assignment        |
     |  +------------------------+            +------------------------+
     |  | -variables: array      |            | -variableName: string |
     |  | -self: SOLObject       |            | -expression: Expression|
     |  | -parent: Frame         |            +------------------------+
     |  | -isSuper: bool         |                      |
     |  +------------------------+                      |
     |                                                  V
     V                                        +------------------------+
+-----------------------------+               |AST::Expression        |
|Runtime::ClassRegistry       |               +------------------------+
+-----------------------------+               | +accept()             |
| -classes: array             |               +------------------------+
+-----------------------------+                     |        |        |
| +registerClass()            |                     |        |        |
| +lookupClass()              |            +--------+  +-----+  +-----+
| +hasClass()                 |            |           |        |
+-----------------------------+   +----------------+   |   +---------------+
     |                            |AST::Literal    |   |   |AST::MessageSend|
     V                            +----------------+   |   +---------------+
+-----------------------------+   | -class: string |   |   | -selector: string|
|Runtime::ObjectFactory       |   | -value: string |   |   | -receiver: Expr|
+-----------------------------+   +----------------+   |   | -arguments: Expr[]|
| +createObject()             |                        |   +---------------+
| +createFromLiteral()        |                        |
| +createInteger()            |                        V
| +createString()             |                    +----------------+
| +createBlock()              |                    |AST::Variable   |
+-----------------------------+                    +----------------+
     |                                             | -name: string  |
     V                                             +----------------+
+-----------------------------+
|Runtime::SOL::SOLObject      |
+-----------------------------+
| -class: SOLClass            |
| -attributes: array          |
+-----------------------------+
| +sendMessage()              |
| +getAttribute()             |
| +setAttribute()             |
+-----------------------------+
    |          |         |
    |          |         |
    V          V         V
+-------+ +--------+ +--------+
|SOLClass| |SOLInteger| |SOLString|
+-------+ +--------+ +--------+
```

Diagram znázorňuje hlavní třídy interpretu a jejich vztahy. Třídy z rámce ipp-core nejsou v diagramu zahrnuty (kromě základní třídy `IPP::Student::Interpreter`), aby byl diagram přehlednější.

## Závěr

Implementace interpretu je založena na objektově orientovaném návrhu s využitím návrhových vzorů, což zajišťuje modulárnost, rozšiřitelnost a udržovatelnost kódu. Interpret je schopen zpracovat XML reprezentaci AST a interpretovat program v jazyce SOL25 podle specifikace. 

Využití návrhových vzorů, zejména Visitor a Interpreter, umožňuje oddělení struktury AST od operací prováděných na jednotlivých uzlech, což usnadňuje implementaci nových funkcí a rozšíření jazyka v budoucnu.

Implementace také obsahuje robustní detekci chyb a ošetření výjimek, což zajišťuje spolehlivý běh programu i při neočekávaných situacích.