---
title: Writing a parser in PHP with the help of Doctrine
categories: [articles]
---
<p>In the <a href="http://doctrine-project.org" target="_blank">Doctrine</a> project we have a SQL-like language called <a href="http://doctrine-orm.readthedocs.org/en/2.0.x/reference/dql-doctrine-query-language.html" target="_blank">DQL</a> for the ORM. In Doctrine1 the DQL language was not implemented with a true parser but in Doctrine2 the language was completely re-written with a true lexer parser. This lexer parser not only powers DQL but it also powers the <a href="http://jwage.com/post/30490186668/doctrine-annotations-library" target="_blank">Annotations</a> library in the <a href="http://www.doctrine-project.org/projects/common.html" target="_blank">Common</a> library.</p>

<p>To write your own parser you just need to extend <a href="https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Lexer.php" target="_blank"><code>Doctrine\Common\Lexer</code></a> and implement the following three abstract methods. These methods define the <a href="http://en.wikipedia.org/wiki/Lexical_analysis" target="_blank">lexical</a> catchable and non-catchable patterns and a method for returning the type of a token and filtering the value if necessary.</p>

<pre><code>/**
 * Lexical catchable patterns.
 *
 * @return array
 */
abstract protected function getCatchablePatterns();

/**
 * Lexical non-catchable patterns.
 *
 * @return array
 */
abstract protected function getNonCatchablePatterns();

/**
 * Retrieve token type. Also processes the token value if necessary.
 *
 * @param string $value
 * @return integer
 */
abstract protected function getType(&amp;$value);
</code></pre>

<p>Here is an example. The <a href="https://github.com/doctrine/doctrine2/blob/master/lib/Doctrine/ORM/Query/Lexer.php" target="_blank"><code>Doctrine\ORM\Query\Lexer</code></a> implementation for DQL looks like the following:</p>

<pre><code>namespace Doctrine\ORM\Query;

class Lexer extends \Doctrine\Common\Lexer
{
    // All tokens that are not valid identifiers must be &lt; 100
    const T_NONE                = 1;
    const T_INTEGER             = 2;
    const T_STRING              = 3;
    const T_INPUT_PARAMETER     = 4;
    const T_FLOAT               = 5;
    const T_CLOSE_PARENTHESIS   = 6;
    const T_OPEN_PARENTHESIS    = 7;
    const T_COMMA               = 8;
    const T_DIVIDE              = 9;
    const T_DOT                 = 10;
    const T_EQUALS              = 11;
    const T_GREATER_THAN        = 12;
    const T_LOWER_THAN          = 13;
    const T_MINUS               = 14;
    const T_MULTIPLY            = 15;
    const T_NEGATE              = 16;
    const T_PLUS                = 17;
    const T_OPEN_CURLY_BRACE    = 18;
    const T_CLOSE_CURLY_BRACE   = 19;

    // All tokens that are also identifiers should be &gt;= 100
    const T_IDENTIFIER          = 100;
    const T_ALL                 = 101;
    const T_AND                 = 102;
    const T_ANY                 = 103;
    const T_AS                  = 104;
    const T_ASC                 = 105;
    const T_AVG                 = 106;
    const T_BETWEEN             = 107;
    const T_BOTH                = 108;
    const T_BY                  = 109;
    const T_CASE                = 110;
    const T_COALESCE            = 111;
    const T_COUNT               = 112;
    const T_DELETE              = 113;
    const T_DESC                = 114;
    const T_DISTINCT            = 115;
    const T_EMPTY               = 116;
    const T_ESCAPE              = 117;
    const T_EXISTS              = 118;
    const T_FALSE               = 119;
    const T_FROM                = 120;
    const T_GROUP               = 121;
    const T_HAVING              = 122;
    const T_IN                  = 123;
    const T_INDEX               = 124;
    const T_INNER               = 125;
    const T_INSTANCE            = 126;
    const T_IS                  = 127;
    const T_JOIN                = 128;
    const T_LEADING             = 129;
    const T_LEFT                = 130;
    const T_LIKE                = 131;
    const T_MAX                 = 132;
    const T_MEMBER              = 133;
    const T_MIN                 = 134;
    const T_NOT                 = 135;
    const T_NULL                = 136;
    const T_NULLIF              = 137;
    const T_OF                  = 138;
    const T_OR                  = 139;
    const T_ORDER               = 140;
    const T_OUTER               = 141;
    const T_SELECT              = 142;
    const T_SET                 = 143;
    const T_SIZE                = 144;
    const T_SOME                = 145;
    const T_SUM                 = 146;
    const T_TRAILING            = 147;
    const T_TRUE                = 148;
    const T_UPDATE              = 149;
    const T_WHEN                = 150;
    const T_WHERE               = 151;
    const T_WITH                = 153;
    const T_PARTIAL             = 154;
    const T_MOD                 = 155;

    /**
     * Creates a new query scanner object.
     *
     * @param string $input a query string
     */
    public function __construct($input)
    {
        $this-&gt;setInput($input);
    }

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return array(
            '[a-z_\\\][a-z0-9_\:\\\]*[a-z0-9_]{1}',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            "'(?:[^']|'')*'",
            '\?[0-9]*|:[a-z]{1}[a-z0-9_]{0,}'
        );
    }

    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns()
    {
        return array('\s+', '(.)');
    }

    /**
     * @inheritdoc
     */
    protected function getType(&amp;$value)
    {
        $type = self::T_NONE;

        // Recognizing numeric values
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false || stripos($value, 'e') !== false)
                    ? self::T_FLOAT : self::T_INTEGER;
        }

        // Differentiate between quoted names, identifiers, input parameters and symbols
        if ($value[0] === "'") {
            $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
            return self::T_STRING;
        } else if (ctype_alpha($value[0]) || $value[0] === '_') {
            $name = 'Doctrine\ORM\Query\Lexer::T_' . strtoupper($value);

            if (defined($name)) {
                $type = constant($name);

                if ($type &gt; 100) {
                    return $type;
                }
            }

            return self::T_IDENTIFIER;
        } else if ($value[0] === '?' || $value[0] === ':') {
            return self::T_INPUT_PARAMETER;
        } else {
            switch ($value) {
                case '.': return self::T_DOT;
                case ',': return self::T_COMMA;
                case '(': return self::T_OPEN_PARENTHESIS;
                case ')': return self::T_CLOSE_PARENTHESIS;
                case '=': return self::T_EQUALS;
                case '&gt;': return self::T_GREATER_THAN;
                case '&lt;': return self::T_LOWER_THAN;
                case '+': return self::T_PLUS;
                case '-': return self::T_MINUS;
                case '*': return self::T_MULTIPLY;
                case '/': return self::T_DIVIDE;
                case '!': return self::T_NEGATE;
                case '{': return self::T_OPEN_CURLY_BRACE;
                case '}': return self::T_CLOSE_CURLY_BRACE;
                default:
                    // Do nothing
                    break;
            }
        }

        return $type;
    }
}
</code></pre>

<p>The <code>Lexer</code> parser is responsible for giving you an API to walk across a string and analyze the type, value and position of each token in the string. The low level API of the lexer is pretty simple:</p>

<ul><li><strong>setInput($input)</strong> - Sets the input data to be tokenized. The Lexer is immediately reset and the new input tokenized.</li>
<li><strong>reset()</strong> - Resets the lexer.</li>
<li><strong>resetPeek()</strong> - Resets the peek pointer to 0.</li>
<li><strong>resetPosition($position = 0)</strong> - Resets the lexer position on the input to the given position.</li>
<li><strong>isNextToken($token)</strong> - Checks whether a given token matches the current lookahead.</li>
<li><strong>isNextTokenAny(array $tokens)</strong> - Checks whether any of the given tokens matches the current lookahead.</li>
<li><strong>moveNext()</strong> - Moves to the next token in the input string.</li>
<li><strong>skipUntil($type)</strong> - Tells the lexer to skip input tokens until it sees a token with the given value.</li>
<li><strong>isA($value, $token)</strong> - Checks if given value is identical to the given token.</li>
<li><strong>peek()</strong> - Moves the lookahead token forward.</li>
<li><strong>glimpse()</strong> - Peeks at the next token, returns it and immediately resets the peek.</li>
</ul><p>Put it all together and this is what you get. This is what the Doctrine ORM DQL parser implementation looks like:</p>

<pre><code>class Parser
{
    private $lexer;

    public function __construct($dql)
    {
        $this-&gt;lexer = new Lexer();
        $this-&gt;lexer-&gt;setInput($dql);
    }

    // ...

    public function getAST()
    {
        // Parse &amp; build AST
        $AST = $this-&gt;QueryLanguage();

        // ...

        return $AST;
    }

    public function QueryLanguage()
    {
        $this-&gt;lexer-&gt;moveNext();

        switch ($this-&gt;lexer-&gt;lookahead['type']) {
            case Lexer::T_SELECT:
                $statement = $this-&gt;SelectStatement();
                break;
            case Lexer::T_UPDATE:
                $statement = $this-&gt;UpdateStatement();
                break;
            case Lexer::T_DELETE:
                $statement = $this-&gt;DeleteStatement();
                break;
            default:
                $this-&gt;syntaxError('SELECT, UPDATE or DELETE');
                break;
        }

        // Check for end of string
        if ($this-&gt;lexer-&gt;lookahead !== null) {
            $this-&gt;syntaxError('end of string');
        }

        return $statement;
    }

    // ...
}

$parser = new Parser('SELECT u FROM User u');
$AST = $parser-&gt;getAST(); // returns \Doctrine\ORM\Query\AST\SelectStatement
</code></pre>

<p>What is an AST? AST stands for <a href="http://en.wikipedia.org/wiki/Abstract_syntax_tree" target="_blank">Abstract syntax tree</a>:</p>

<blockquote>In computer science, an abstract syntax tree (AST), or just syntax tree, is a tree representation of the abstract syntactic structure of source code written in a programming language. Each node of the tree denotes a construct occurring in the source code.</blockquote>

<p>Now the AST is used to transform the DQL query in to portable SQL for whatever relational database you are using! Cool!</p>
