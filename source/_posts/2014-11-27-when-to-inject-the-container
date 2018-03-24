---
title: When to Inject the Container
---
<p>Deciding when to inject the container in Symfony is a frequent topic of discussion. Many would have you believe that you should NEVER inject the container because it breaks the &ldquo;rules&rdquo; and is an anti-pattern. This is not always true and, just like most things, it should not be applied blindly to everything you do. This post aims to demonstrate cases where injecting the container makes sense.</p>

<p><strong>The problem with unused dependencies</strong></p>

<p>Imagine you have a listener that records some query string parameters and you&rsquo;d like this to run on each request:</p>

<pre><code>&lt;?php

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestParameterLoggerListener
{
    private $requestParameterLogger;

    public function __construct(RequestParameterLogger $requestParameterLogger)
    {
        $this-&gt;requestParameterLogger = $requestParameterLogger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event-&gt;getRequest();

        if (!$request-&gt;query-&gt;has('utm_origin')) {
            return;
        }

        $this-&gt;requestParameterLogger-&gt;logUtmOrigin($request);
    }
}

class RequestParameterLogger
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this-&gt;connection = $connection;
    }

    public function logUtmOrigin(Request $request)
    {
        // record utm origin to the database using the connection
    }
}
</code></pre>

<p>As you can see, <code>logUtmOrigin</code> is only called when the Request&rsquo;s query string contains the parameter named <code>utm_origin</code>. This means that even on requests where <code>utm_origin</code> does not exist, we are constructing the <code>RequestParameterLogger</code> and injecting it to <code>RequestParameterLoggerListener</code>.</p>

<p>This is a simple example; however, in a large application with many such listeners, you can imagine the application constructing potentially dozens of services like RequestParameterLogger, which would be injected but never used.</p>

<p><strong>Injecting the Container</strong></p>

<p>This problem can be easily fixed by injecting the container and lazily requesting the service from the container when it is needed. Here is the same listener above, but rewritten with container injection.</p>

<pre><code>&lt;?php

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestParameterLoggerListener extends ContainerAware
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event-&gt;getRequest();

        if (!$request-&gt;query-&gt;has('utm_origin')) {
            return;
        }

        $this-&gt;container-&gt;get('request.parameter_logger')-&gt;logUtmOrigin($request);
    }
}
</code></pre>

<p>Now, when the query parameter <code>utm_origin</code> does not exist, the <code>RequestParameterLogger</code> will not be constructed.</p>

<p><strong>In the Wild</strong></p>

<p>At <a href="http://www.opensky.com" target="_blank">OpenSky</a> we have always injected services to listeners, security voters, security providers, etc. As a result, every request would eagerly construct many services that would never be used. By rewriting these services to use the strategy demonstrated above, I was able to shave 10-20 milliseconds off of every request and significantly reduce the number of services constructed to handle a request that serves a blank controller and template.</p>

<p>To make it easy to rewrite all of our prior art, I wrote a simple class named <code>LazyService</code>.</p>

<pre><code>&lt;?php

use Symfony\Component\DependencyInjection\ContainerAware;

abstract class LazyService extends ContainerAware
{
    protected $propertyMap = array();
    protected $values = array();

    public function __get($key)
    {
        if (!isset($this-&gt;propertyMap[$key])) {
            throw new \InvalidArgumentException(sprintf('Could not find service for key %s', $key));
        }

        if (!isset($this-&gt;values[$key])) {
            if ($this-&gt;propertyMap[$key][0] === '%') {
                $this-&gt;values[$key] = $this-&gt;container-&gt;getParameter(trim($this-&gt;propertyMap[$key], '%'));
            } else {
                $this-&gt;values[$key] = $this-&gt;container-&gt;get($this-&gt;propertyMap[$key]);
            }
        }

        return $this-&gt;values[$key];
    }
}
</code></pre>

<p>Here is our original example, but rewritten to use this LazyService class:</p>

<pre><code>class RequestParameterLoggerListener extends LazyService
{
    protected $propertyMap = array(
        'requestParameterLogger' =&gt; 'request.parameter_logger',
    );

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event-&gt;getRequest();

        if (!$request-&gt;query-&gt;has('utm_origin')) {
            return;
        }

        $this-&gt;requestParameterLogger-&gt;logUtmOrigin($request);
    }
}
</code></pre>

<p>This made it easy for me to make dozens of services extra lazy without having to rewrite too much of the service themselves or the associated tests.</p>

<p><strong>Why not use lazy services provided by Symfony?</strong></p>

<p>I chose not to use lazy services provided by Symfony because I didn&rsquo;t want to add yet more complexity and weight to our application. Even if you mark a service as lazy, a proxy still has to be instantiated and injected. I wanted to completely eliminate the construction of these classes.</p>

<p>That is it! I hope this post was helpful in realizing when to inject the container and not blindly follow design theory. Happy Thanksgiving!</p>