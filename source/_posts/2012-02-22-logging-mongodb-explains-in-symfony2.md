---
title: Logging MongoDB Explains in Symfony2
---
<p>At <a href="http://opensky.com" target="_blank">OpenSky.com</a> we use <a href="http://mongodb.org" target="_blank">MongoDB</a> as one of our primary data stores. We use the slow query log in the <a href="http://www.mongodb.org/display/DOCS/Database+Profiler" target="_blank">profiling tools</a> to identify to slow queries but sometimes it is hard to tell exactly where in our application it originated from. Thanks to the flexibility of <a href="http://doctrine-project.org" target="_blank">Doctrine</a> and <a href="http://symfony.com" target="_blank">Symfony2</a> we can easily listen to a few events and log the information without modifying any application code.</p>

<p>First lets write a simple listener class:</p>

<pre><code>namespace Application\Bundle\MainBundle\ODM\MongoDB\Explainer;

use Doctrine\MongoDB\Event\EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExplainerListener
{
    private $container;
    private $lastQuery;
    private $explains = array();

    public function __construct(ContainerInterface $container)
    {
        $this-&gt;container = $container;
    }

    public function collectionPreFind(EventArgs $args)
    {
        $this-&gt;lastQuery = $args-&gt;getData();
    }

    public function collectionPostFind(EventArgs $args)
    {
        $e = new \Exception();
        $collection = $args-&gt;getInvoker();
        $cursor = $args-&gt;getData();
        $explain = $cursor-&gt;explain();
        $this-&gt;explains[] = array(
            'explain' =&gt; $explain,
            'query' =&gt; $this-&gt;lastQuery,
            'database' =&gt; $collection-&gt;getDatabase()-&gt;getName(),
            'collection' =&gt; $collection-&gt;getName(),
            'traceAsString' =&gt; $e-&gt;getTraceAsString()
        );
    }

    private function getCollection()
    {
        $databaseName = $this-&gt;container-&gt;getParameter('doctrine.odm.mongodb.default_configuration.default_database');
        return $this-&gt;container-&gt;get('doctrine.odm.mongodb.document_manager')
            -&gt;getConnection()
            -&gt;selectCollection($databaseName, 'query_explains');
    }

    public function __destruct()
    {
        $this-&gt;getCollection()-&gt;batchInsert($this-&gt;explains);
    }
}
</code></pre>

<p>This class will listen on the <code>Doctrine\MongoDB\Collection#find()</code> <code>pre</code> and <code>post</code> event and capture the explain of the query.</p>

<p>Next just configure the listener we wrote above in the DIC:</p>

<pre><code>&lt;?xml version="1.0" encoding="utf-8" ?&gt;
&lt;container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services <a href="http://symfony.com/schema/dic/services/services-1.0.xsd" target="_blank">http://symfony.com/schema/dic/services/services-1.0.xsd</a>"&gt;

    &lt;services&gt;
        &lt;service id="odm.explainer" class="Application\Bundle\MainBundle\ODM\MongoDB\Explainer\ExplainerListener"&gt;
            &lt;tag name="doctrine.odm.mongodb.default_event_listener" event="collectionPreFind" method="collectionPreFind" /&gt;
            &lt;tag name="doctrine.odm.mongodb.default_event_listener" event="collectionPostFind" method="collectionPostFind" /&gt;
            &lt;argument type="service" id="service_container" /&gt;
        &lt;/service&gt;
    &lt;/services&gt;
&lt;/container&gt;
</code></pre>

<p>Now all your queries will be logged in to a mongodb collection named query_explains. If you take a look in the collection after triggering a few queries in your application you will see documents that look like the following:</p>

<pre><code>{
    "_id" : ObjectId("4f4480d4acee41cd6800001b"),
    "explain" : {
        "cursor" : "ForwardCappedCursor",
        "nscanned" : 0,
        "nscannedObjects" : 0,
        "n" : 0,
        "millis" : 0,
        "nYields" : 0,
        "nChunkSkips" : 0,
        "isMultiKey" : false,
        "indexOnly" : false,
        "indexBounds" : [ ],
        "allPlans" : [
            {
                "cursor" : "ForwardCappedCursor",
                "indexBounds" : [ ]
            }
        ]
    },
    "query" : [ ],
    "database" : "database_name",
    "collection" : "collection_name",
    "traceAsString" : "..."
}
</code></pre>

<p>The traceAsString field is a string representation of the trace that led up to the query so you can easily identify what triggered the query in your application.</p>