---
title: Doctrine Annotations Library
categories: [articles]
---
<p>The Doctrine Annotations library was born from a need in the Doctrine2 ORM to allow the mapping information to be specified inside the doc-blocks of classes, properties and methods. The library is independent and can be used in your own libraries to implement doc block annotations.</p>

<h2>Setup and Configuration</h2>

<p>To use the annotations library is simple, you just need to create a new <code>AnnotationReader</code>
instance:</p>

<pre><code>$reader = new \Doctrine\Common\Annotations\AnnotationReader();
</code></pre>

<h2>Usage</h2>

<p>Using the library API is simple. Imagine you had some annotation classes that looked like the following:</p>

<pre><code>namespace MyCompany\Annotations;

class Foo extends \Doctrine\Common\Annotations\Annotation
{
    public $bar;
}

class Bar extends \Doctrine\Common\Annotations\Annotation
{
    public $foo;
}
</code></pre>

<p>Now to use the annotations you would just need to do the following:</p>

<pre><code>/**
 * @MyCompany\Annotations\Foo(bar="test")
 * @MyCompany\Annotations\Bar(foo="test")
 */
class User
{
}
</code></pre>

<p>Now we can write a script to get the annotations above:</p>

<pre><code>$reflClass = new ReflectionClass('User');
$classAnnotations = $reader-&gt;getClassAnnotations($reflClass);
echo $classAnnotations['MyCompany\Annotations\Foo']-&gt;bar; // prints foo
echo $classAnnotations['MyCompany\Annotations\Foo']-&gt;foo; // prints bar
</code></pre>

<p>You have a complete API for retrieving annotation class instances from a class, property
or method docblock:</p>

<ul><li>getClassAnnotations(ReflectionClass $class)</li>
<li>getClassAnnotation(ReflectionClass $class, $annotation)</li>
<li>getPropertyAnnotations(ReflectionProperty $property)</li>
<li>getPropertyAnnotation(ReflectionProperty $property, $annotation)</li>
<li>getMethodAnnotations(ReflectionMethod $method)</li>
<li>getMethodAnnotation(ReflectionMethod $method, $annotation)</li>
</ul><p>Read the full <a href="http://www.doctrine-project.org/projects/common/2.0/docs/reference/annotations/en" target="_blank">documentation</a> to learn more about how to use the Doctrine annotations library!</p>
