---
title: Doctrine is not just an ORM for Relational Databases
categories: [articles]
---
<p>In April of 2010 the <a href="https://github.com/doctrine/mongodb-odm/commit/92582ffe5facffa4d2379f176dbee539918db962" target="_blank">first commit</a> for the Doctrine MongoDB ODM project was made. I was experimenting with MongoDB at the time and I wanted to see how difficult it would be to build a version of Doctrine for MongoDB.</p>

<p>Up until the MongoDB ODM, Doctrine was solely a project built around the DBAL/ORM and was advertised as such. In May of 2010 we decided to widen the scope of the project so that we could host libraries like the MongoDB ODM. This change led to a spur of new contributors and development and we now have several object mappers developed under Doctrine and things are more active than ever.</p>

<p>Below is an overview of all the libraries underneath the Doctrine project.</p>

<p><strong>Common Shared Libraries</strong></p>

<p><strong><a href="http://github.com/doctrine/common" target="_blank">doctrine/common</a></strong></p>

<p>Doctrine Common contains some base functionality and interfaces you need in order to create a Doctrine style object mapper. All of our mapper projects follow the same <code>Doctrine\Common\Persistence</code> interfaces. Here are the <code>ObjectManager</code> and <code>ObjectRepository</code> interfaces:</p>

<pre><code>&lt;?php

namespace Doctrine\Common\Persistence

interface ObjectManager
{
    public function find($className, $id);
    public function persist($object);
    public function remove($object);
    public function merge($object);
    public function clear($objectName = null);
    public function detach($object);
    public function refresh($object);
    public function flush();
    public function getRepository($className);
}

interface ObjectRepository
{
    public function find($id);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function findOneBy(array $criteria);
}
</code></pre>

<p><strong><a href="http://github.com/doctrine/collections" target="_blank">doctrine/collections</a></strong></p>

<p>Doctrine Collections is a library that contains classes for working with arrays of data. Here is an example using the simple <code>Doctrine\Common\Collections\ArrayCollection</code> class:</p>

<pre><code>&lt;?php

$data = new \Doctrine\Common\Collections\ArrayCollection(array(1, 2, 3));
$data = $data-&gt;filter(function($count) { return $count &gt; 1; });
</code></pre>

<p><strong><a href="http://github.com/doctrine/annotations" target="_blank">doctrine/annotations</a></strong></p>

<p>Doctrine Annotations is a library that allows you to parse structured information out of a doc block.</p>

<p>Imagine you have a class with a doc block like the following:</p>

<pre><code>&lt;?php

/** @Foo(bar="value") */
class User
{

}
</code></pre>

<p>You can parse the information out of the doc block for <code>User</code> easily. Define a new annotation object:</p>

<pre><code>&lt;?php

/**
 * @Annotation
 * @Target("CLASS")
 */
class Foo
{
    /** @var string */
    public $bar;
}
</code></pre>

<p>Now you can get instances of <code>Foo</code> defined on the <code>User</code>:</p>

<pre><code>&lt;?php

$reflClass = new ReflectionClass('User');
$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$classAnnotations = $reader-&gt;getClassAnnotations($reflClass);

foreach ($classAnnotations AS $annot) {
    if ($annot instanceof Foo) {
        echo $annot-&gt;bar; // prints "value";
    }
}
</code></pre>

<p><strong><a href="http://github.com/doctrine/inflector" target="_blank">doctrine/inflector</a></strong></p>

<p>Doctrine Inflector is a library that can perform string manipulations with regard to upper/lowercase and singular/plural forms of words.</p>

<pre><code>&lt;?php

$camelCase = 'camelCase';
$table = \Doctrine\Common\Inflector::tableize($camelCase);
echo $table; // camel_case
</code></pre>

<p><strong><a href="http://github.com/doctrine/lexer" target="_blank">doctrine/lexer</a></strong></p>

<p>Doctrine Lexer is a library that can be used in Top-Down, Recursive Descent Parsers. This lexer is used in Doctrine Annotations and in Doctrine ORM (DQL).</p>

<p>Here is what the <code>AbstractLexer</code> provided by Doctrine looks like:</p>

<pre><code>&lt;?php

namespace Doctrine\Common\Lexer;

abstract class AbstractLexer
{
    public function setInput($input);
    public function reset();
    public function resetPeek();
    public function resetPosition($position = 0);
    public function isNextToken($token);
    public function isNextTokenAny(array $tokens);
    public function moveNext();
    public function skipUntil($type);
    public function isA($value, $token);
    public function peek();
    public function glimpse();
    public function getLiteral($token);

    abstract protected function getCatchablePatterns();
    abstract protected function getNonCatchablePatterns();
    abstract protected function getType(&amp;$value);
}
</code></pre>

<p>To implement a lexer just extend the <code>Doctrine\Common\Lexer\AbstractParser</code> class and implement the <code>getCatchablePatterns</code>, <code>getNonCatchablePatterns</code>, and <code>getType</code> methods. Here is a very simple example lexer implementation named <code>CharacterTypeLexer</code>. It tokenizes a string to <code>T_UPPER</code>, <code>T_LOWER</code> and <code>T_NUMER</code>:</p>

<pre><code>&lt;?php

use Doctrine\Common\Lexer\AbstractParser;

class CharacterTypeLexer extends AbstractLexer
{
    const T_UPPER =  1;
    const T_LOWER =  2;
    const T_NUMBER = 3;

    protected function getCatchablePatterns()
    {
        return array(
            '[a-bA-Z0-9]',
        );
    }

    protected function getNonCatchablePatterns()
    {
        return array();
    }

    protected function getType(&amp;$value)
    {
        if (is_numeric($value)) {
            return self::T_NUMBER;
        }

        if (strtoupper($value) === $value) {
            return self::T_UPPER;
        }

        if (strtolower($value) === $value) {
            return self::T_LOWER;
        }
    }
}
</code></pre>

<p>Use <code>CharacterTypeLexer</code> to extract an array of upper case characters:</p>

<pre><code>&lt;?php

class UpperCaseCharacterExtracter
{
    private $lexer;

    public function __construct(CharacterTypeLexer $lexer)
    {
        $this-&gt;lexer = $lexer;
    }

    public function getUpperCaseCharacters($string)
    {
        $this-&gt;lexer-&gt;setInput($string);
        $this-&gt;lexer-&gt;moveNext();

        $upperCaseChars = array();
        while (true) {
            if (!$this-&gt;lexer-&gt;lookahead) {
                break;
            }

            $this-&gt;lexer-&gt;moveNext();

            if ($this-&gt;lexer-&gt;token['type'] === CharacterTypeLexer::T_UPPER) {
                $upperCaseChars[] = $this-&gt;lexer-&gt;token['value'];
            }
        }

        return $upperCaseChars;
    }
}

$upperCaseCharacterExtractor = new UpperCaseCharacterExtracter(new CharacterTypeLexer());
$upperCaseCharacters = $upperCaseCharacterExtractor-&gt;getUpperCaseCharacters('1aBcdEfgHiJ12');

print_r($upperCaseCharacters);
</code></pre>

<p>The variable <code>$upperCaseCharacters</code> contains all of the upper case characters:</p>

<pre><code>Array
(
    [0] =&gt; B
    [1] =&gt; E
    [2] =&gt; H
    [3] =&gt; J
)
</code></pre>

<p><strong><a href="http://github.com/doctrine/cache" target="_blank">doctrine/cache</a></strong></p>

<p>Doctrine Cache is a library that provides an interface for caching data. It comes with implementations for some of the most popular caching data stores. Here is what the <code>Cache</code> interface looks like:</p>

<pre><code>&lt;?php

namespace Doctrine\Common\Cache;

interface Cache
{
    function fetch($id);
    function contains($id);
    function save($id, $data, $lifeTime = 0);
    function delete($id);
    function getStats();
}
</code></pre>

<p>Here is an example using memcache:</p>

<pre><code>&lt;?php

$memcache = new \Memcache();
$cache = new \Doctrine\Common\Cache\MemcacheCache();
$cache-&gt;setMemcache($memcache);

$cache-&gt;set('key', 'value');

echo $cache-&gt;get('key') // prints "value"
</code></pre>

<p>Other supported drivers are:</p>

<ul><li>APC</li>
<li>Couchbase</li>
<li>Filesystem</li>
<li>Memcached</li>
<li>MongoDB</li>
<li>PhpFile</li>
<li>Redis</li>
<li>Riak</li>
<li>WinCache</li>
<li>Xcache</li>
<li>ZendData</li>
</ul><p><strong>Database Abstraction Layers</strong></p>

<p><strong><a href="http://github.com/doctrine/dbal" target="_blank">doctrine/dbal</a></strong></p>

<p>Doctrine DBAL is a library that provides an abstraction layer for relational databases in PHP. Read <a href="http://jwage.com/post/31080076112/doctrine-dbal-php-database-abstraction-layer" target="_blank">Doctrine DBAL: PHP Database Abstraction Layer</a> blog post for more information on the DBAL.</p>

<p><strong><a href="http://github.com/doctrine/mongodb" target="_blank">doctrine/mongodb</a></strong></p>

<p>Doctrine MongoDB is a library that provides an abstraction layer on top of the PHP MongoDB PECL extension. It provides some additional functionality and abstractions to make working with MongoDB easier.</p>

<p><strong><a href="http://github.com/doctrine/couchdb-client" target="_blank">doctrine/couchdb-client</a></strong></p>

<p>Doctrine CouchDB Client is a library that provides a connection abstraction to CouchDB by wrapping around the CouchDB HTTP API.</p>

<pre><code>&lt;?php

$client = \Doctrine\CouchDB\CouchDBClient::create();

array($id, $rev) = $client-&gt;postDocument(array('foo' =&gt; 'bar'));
$client-&gt;putDocument(array('foo' =&gt; 'baz'), $id, $rev);

$doc = $client-&gt;findDocument($id);
</code></pre>

<p><strong>Object Mappers</strong></p>

<p>The object mappers are where all the pieces come together. The object mappers provide transparent persistence for PHP objects. As mentioned above, they all implement the common interfaces from <code>Doctrine\Common</code> so working with each of them is generally the same. You have an <code>ObjectManager</code> to manage the persistent state of your domain objects:</p>

<pre><code>&lt;?php

$user = new User();
$user-&gt;setId(1);
$user-&gt;setUsername('jwage');

$om = $this-&gt;getYourObjectManager();
$om-&gt;persist($user);
$om-&gt;flush(); // insert the new document
</code></pre>

<p>Then you can find that object later and modify it:</p>

<pre><code>&lt;?php

$user = $om-&gt;find('User', 1);
echo $user-&gt;getUsername(); // prints "jwage"

$user-&gt;setUsername('jonwage'); // change the obj in memory

$om-&gt;flush(); // updates the object in the database
</code></pre>

<p>You can find more information about the supported object mappers below:</p>

<p><strong><a href="http://github.com/doctrine/doctrine2" target="_blank">doctrine/orm</a></strong></p>

<p>Doctrine ORM provides persistence for PHP objects to relational database.</p>

<p><strong><a href="http://github.com/doctrine/couchdb-odm" target="_blank">doctrine/couchdb-odm</a></strong></p>

<p>Doctrine CouchDB ODM provides persistence for PHP objects to CouchDB.</p>

<p><strong><a href="http://github.com/doctrine/phpcr-odm" target="_blank">doctrine/phpcr-odm</a></strong></p>

<p>Doctrine PHPCR (Content Repository) ODM provides persistence to a backend like Jackalope or Midgard2. This is a specialized object mapper for dealing with data designed for building content websites. Think of this like a backend for a CMS (Content Management System).</p>

<p><strong><a href="http://github.com/doctrine/mongodb-odm" target="_blank">doctrine/mongodb-odm</a></strong></p>

<p>Doctrine MongoDB ODM provides persistence for PHP objects to MongoDB. You can read more about the MongoDB ODM <a href="http://jwage.com/post/30490170860/doctrine-mongodb-object-document-mapper-in-symfony2" target="_blank">here</a>.</p>

<p><strong><a href="http://github.com/doctrine/orientdb-odm" target="_blank">doctrine/orientdb-odm</a></strong></p>

<p>Doctrine OrientDB ODM provides persistence for PHP objects to OrientDB.</p>
