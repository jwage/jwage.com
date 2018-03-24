---
title: Doctrine Common Library
---
<p><a href="http://doctrine-project.org" target="_blank">Doctrine</a> started as a library where all the internal components were coupled together. But as things have evolved the components have been decoupled and shared between the projects. This change also makes it possible for other people to use these pieces of Doctrine even if they don&rsquo;t use the ORM or any other project.</p>

<p>The <a href="https://github.com/doctrine/common/tree/master/lib/Doctrine/Common" target="_blank">Doctrine\Common</a> namespace contains a few things like:</p>

<ul><li><a href="https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Annotations" target="_blank">DocBlock Annotations Library</a> - annotations library used by <a href="http://symfony.com" target="_blank">Symfony</a>, <a href="http://drupal.org" target="_blank">Drupal</a> and other popular PHP projects.</li>
<li><a href="https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Cache" target="_blank">Cache Drivers</a> - APC, Memcache, etc. cache drivers.</li>
<li><a href="https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Persistence" target="_blank">Persistence</a> - Shared base classes and interfaces across the object mappers.</li>
<li><a href="https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Lexer.php" target="_blank">Lexer Parser</a> - Base class for writing simple lexers, i.e. for creating small DSLs. I recently wrote a blog post about it <a href="http://jwage.com/post/31623163785/writing-a-parser-in-php-with-the-help-of-doctrine" target="_blank">here</a>.</li>
</ul><p><strong>DocBlock AnnotationsLibrary</strong></p>

<p>With the annotations library you can parse information out of your DocBlocks in to PHP objects. The object mapper projects use this feature for specifying entity mapping information in the DocBlocks of your classes, properties and methods. Here is an example of what an entity looks like in the ORM:</p>

<pre><code>namespace MyProject\Entities;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validation\Constraints AS Assert;

/**
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Id @ORM\Column @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotEmpty
     * @Assert\Email
     */
    private $email;
}
</code></pre>

<p><strong>Cache Drivers</strong></p>

<p>The cache drivers provide a common interface to cache backends in PHP. Here are the supported drivers:</p>

<ul><li>ApcCache</li>
<li>ArrayCache</li>
<li>FileCache</li>
<li>FilesystemCache</li>
<li>MemcacheCache</li>
<li>MemcachedCache</li>
<li>PhpFileCache</li>
<li>RedisCache</li>
<li>WinCacheCache</li>
<li>XcacheCache</li>
<li>ZendDataCache</li>
</ul><p>The interface is very simple:</p>

<pre><code>function fetch($id);
function contains($id);
function save($id, $data, $lifeTime = 0);
function delete($id);
function getStats();
</code></pre>

<p><strong>Persistence Library</strong></p>

<p>The persistence interfaces are implemented by the object mapper libraries. They provide the common base classes and interfaces that a Doctrine object persistence library should implement, such as:</p>

<p><strong>ObjectManager</strong></p>

<pre><code>function find($className, $id);
function persist($object);
function remove($object);
function merge($object);
function clear($objectName = null);
function detach($object);
function refresh($object);
function flush();
function getRepository($className);
function getClassMetadata($className);
function getMetadataFactory();
function initializeObject($obj);
function contains($object);
</code></pre>

<p><strong>ObjectRepository</strong></p>

<pre><code>function find($id);
function findAll();
function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
function findOneBy(array $criteria);
function getClassName();
</code></pre>

<p><strong>ClassMetadataFactory</strong></p>

<pre><code>function getAllMetadata();
function getMetadataFor($className);
function hasMetadataFor($className);
function setMetadataFor($className, $class);
function isTransient($className);
</code></pre>

<p><strong>ClassMetadata</strong></p>

<pre><code>function getName();
function getIdentifier();
function getReflectionClass();
function isIdentifier($fieldName);
function hasField($fieldName);
function hasAssociation($fieldName);
function isSingleValuedAssociation($fieldName);
function isCollectionValuedAssociation($fieldName);
function getFieldNames();
function getIdentifierFieldNames();
function getAssociationNames();
function getTypeOfField($fieldName);
function getAssociationTargetClass($assocName);
function isAssociationInverseSide($assocName);
function getAssociationMappedByTargetField($assocName);
function getIdentifierValues($object);
</code></pre>

<p>The <code>Doctrine\Common</code> namespace contains lots more than I have mentioned here. So if you want to learn more check it out on <a href="https://github.com/doctrine/common/tree/master/lib/Doctrine/Common" target="_blank"><code>GitHub</code></a> or read the <a href="http://docs.doctrine-project.org/projects/doctrine-common/en/latest/index.html" target="_blank">documentation</a>. It is not that complete yet but it has some useful information.</p>