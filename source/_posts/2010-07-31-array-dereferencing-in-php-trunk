---
title: Array dereferencing in PHP trunk
---
<p>Today I was reading an <a href="http://schlueters.de/blog/archives/138-Features-in-PHP-trunk-Array-dereferencing.html" target="_blank">article</a> about array dereferencing in PHP trunk. It is an awesome new feature added to PHP! Imagine you had some code like this:</p>

<pre><code>class Foo
{
    public function bar()
    {
    }
}

function func()
{
    return new Foo();
}
</code></pre>

<p>Previous to this addition in PHP you had to do something like this:</p>

<pre><code>$foo = func();
$foo-&gt;bar();
</code></pre>

<p>Now it is possible to just call bar() directly without having to set the return of func() to a variable temporarily:</p>

<pre><code>func()-&gt;bar();
</code></pre>

<p>You can also now access arrays when they are the return of a method:</p>

<pre><code>function foo()
{
    return array(1, 2, 3);
}
echo foo()[2]; // prints 3
</code></pre>

<p>This greatly improves the syntax of PHP and I am very happy to see this committed!</p>