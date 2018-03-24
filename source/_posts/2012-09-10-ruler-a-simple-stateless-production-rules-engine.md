---
title: Ruler: A simple stateless production rules engine for PHP 5.3+
---
<p><strong>What is ruler?</strong></p>

<p><a href="https://github.com/bobthecow/ruler" target="_blank">Ruler</a> is a simple stateless production rules engine for PHP 5.3+ written by <a href="http://twitter.com/bobthecow" target="_blank">Justin Hileman (@bobthecow)</a>. Justin was previously employed at <a href="https://opensky.com" target="_blank">OpenSky</a> but these days you will find him hacking on a new startup named <a href="https://twitter.com/presentate" target="_blank">@presentate</a>.</p>

<p><strong>What is a rules engine?</strong></p>

<p>From <a href="http://martinfowler.com/bliki/RulesEngine.html" target="_blank">martinfowler.com</a>:</p>

<blockquote>A rules engine is all about providing an alternative computational model. Instead of the usual imperative model, commands in sequence with conditionals and loops, it provides a list of production rules. Each rule has a condition and an action - simplistically you can think of it as a bunch of if-then statements.</blockquote>

<p>From <a href="http://en.wikipedia.org/wiki/Business_rules_engine" target="_blank">wikipedia</a>:</p>

<blockquote>A business rules engine is a software system that executes one or more business rules in a runtime production environment. The rules might come from legal regulation (&ldquo;An employee can be fired for any reason or no reason but not for an illegal reason&rdquo;), company policy (&ldquo;All customers that spend more than $100 at one time will receive a 10% discount&rdquo;), or other sources. A business rule system enables these company policies and other operational decisions to be defined, tested, executed and maintained separately from application code.</blockquote>

<p><strong>What does Ruler usage look like?</strong></p>

<p>Ruler has a nice and convenient DSL that is provided by <code>RuleBuilder</code>:</p>

<pre><code>$rb = new RuleBuilder;
$rule = $rb-&gt;create(
    $rb-&gt;logicalAnd(
        $rb['minAge']-&gt;greaterThan($rb['age']),
        $rb['maxAge']-&gt;lessThan($rb['age'])
    ),
    function() {
        echo 'Congratulations! You are between the ages of 18 and 25!';
    }
);

$context = new Context(array(
    'minAge' =&gt; 18,
    'maxAge' =&gt; 25,
    'age' =&gt; function() {
        return 20;
    },
));

$rule-&gt;execute($context); // "Congratulations! You are between the ages of 18 and 25!"
</code></pre>

<p>The full API is quite simple:</p>

<pre><code>// These are Variables. They'll be replaced by terminal values during Rule evaluation.

$a = $rb['a'];
$b = $rb['b'];

// Here are bunch of Propositions. They're not too useful by themselves, but they
// are the building blocks of Rules, so you'll need 'em in a bit.

$a-&gt;greaterThan($b);          // true if $a &gt; $b
$a-&gt;greaterThanOrEqualTo($b); // true if $a &gt;= $b
$a-&gt;lessThan($b);             // true if $a &lt; $b
$a-&gt;lessThanOrEqualTo($b);    // true if $a &lt;= $b
$a-&gt;equalTo($b);              // true if $a == $b
$a-&gt;notEqualTo($b);           // true if $a != $b
</code></pre>

<p>You can combine things to create more complex rules:</p>

<pre><code>// Create a Rule with an $a == $b condition
$aEqualsB = $rb-&gt;create($a-&gt;equalTo($b));

// Create another Rule with an $a != $b condition
$aDoesNotEqualB = $rb-&gt;create($a-&gt;notEqualTo($b));

// Now combine them for a tautology!
// (Because Rules are also Propositions, they can be combined to make MEGARULES)
$eitherOne = $rb-&gt;create($rb-&gt;logicalOr($aEqualsB, $aDoesNotEqualB));

// Just to mix things up, we'll populate our evaluation context with completely
// random values...
$context = new Context(array(
    'a' =&gt; rand(),
    'b' =&gt; rand(),
));

// Hint: this is always true!
$eitherOne-&gt;evaluate($context);
</code></pre>

<p>More complex examples:</p>

<pre><code>$rb-&gt;logicalNot($aEqualsB);                  // The same as $aDoesNotEqualB :)
$rb-&gt;logicalAnd($aEqualsB, $aDoesNotEqualB); // True if both conditions are true
$rb-&gt;logicalOr($aEqualsB, $aDoesNotEqualB);  // True if either condition is true
$rb-&gt;logicalXor($aEqualsB, $aDoesNotEqualB); // True if only one condition is true
</code></pre>

<p><strong>Full Examples</strong></p>

<p>Check if user is logged in:</p>

<pre><code>$context = new Context(array('username', function() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}));

$userIsLoggedIn = $rb-&gt;create($rb['username']-&gt;notEqualTo(null));

if ($userIsLoggedIn-&gt;evaluate($context)) {
    // Do something special for logged in users!
}
</code></pre>

<p>If a Rule has an action, you can execute() it directly and save yourself a couple of lines of code.</p>

<pre><code>$hiJustin = $rb-&gt;create(
    $rb['userName']-&gt;equalTo('bobthecow'),
    function() {
        echo "Hi, Justin!";
    }
);

$hiJustin-&gt;execute($context);  // "Hi, Justin!"
</code></pre>

<p><strong>What does <a href="https://opensky.com" target="_blank">OpenSky</a> use Ruler for?</strong></p>

<p><a href="https://opensky.com" target="_blank">OpenSky</a> makes heavy use of Ruler. Below is a list of some of the conditions we have available in our application:</p>

<ul><li><p>Joins OpenSky</p>

<ul><li>Is Facebook Connected</li>
<li>Number of friends is &gt;= n</li>
<li>Number of friends is &lt;= n</li>
<li>With certain origination parameters existing in URL</li>
</ul></li>
<li><p>Makes a Purchase</p>

<ul><li>Within x days of joining</li>
<li>Is first purchase</li>
<li>Order amount is &gt;= n</li>
</ul></li>
<li><p>Loves an offer</p>

<ul><li>Is first love of the day</li>
</ul></li>
<li><p>Visits OpenSky</p>

<ul><li>Is Facebook Connected</li>
<li>Number of friends is &gt;= n</li>
<li>Number of friends is &lt;= n</li>
<li>Users points are &gt;= n</li>
</ul></li>
</ul><p>These are just some of the conditions we have available. Our application is setup in a way that we can easily create new rules via a backend GUI. We can mix and match conditions and rewards. Some of the rewards we have available are:</p>

<ul><li>Issue n points</li>
<li>New member level</li>
<li>Credit</li>
<li>Free shipping</li>
</ul><p>The benefit of this abstract setup is it allows us to combine different conditions, tweak the parameters of the conditions and issue rewards depending on the outcome of the condition all without requiring code changes and a deploy. You can imagine our business and marketing teams love this because they can change things all day long and without having to bother the tech team.</p>