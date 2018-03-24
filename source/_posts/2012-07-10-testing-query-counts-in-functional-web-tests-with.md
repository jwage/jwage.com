---
title: Testing query counts in functional web tests with Symfony2 and PHPUnit
---
<p>At <a href="https://opensky.com" target="_blank">OpenSky</a> we were faced with a challenge of being able to evolve functionality fast without having the overhead of developers constantly watching for changes in performance, or the number of queries required for a request. To help solve part of this problem we integrated the Symfony2 profiler with our functional web tests to assert that a request required a certain number of database queries.</p>

<p>First in order to accomplish this we need to create a special test environment named <strong>test_logging</strong> that will be the same as the normal test environment except profiling and logging is enabled. We don&rsquo;t want this enabled for all of our tests as it does add some overhead to the request and will slow things down a little bit.</p>

<pre><code>imports:
    - { resource: config_test.yml }

doctrine:
    dbal:
        connections:
            default:
                logging: true

doctrine_mongodb:
    document_managers:
        default:
            logging: true
</code></pre>

<p>Now in your PHPUnit functional tests you can issue requests with the <strong>test_logging</strong> environment client and run assertions afterwards to make sure the request executed the queries you expected.</p>

<pre><code>namespace OpenSky\Bundle\MainBundle\Tests\Functional;

use OpenSky\Bundle\MainBundle\Tests\WebTestCase;

class TestSomeQueryCounts extends WebTestCase
{
    // ...

    public function testQueryCounts()
    {
        $client = static::createClient(array(
            'environment' =&gt; 'test_logging'
        ), array(
            'PHP_AUTH_USER' =&gt; 'foobar',
            'PHP_AUTH_PW'   =&gt; 'foobar',
        ));

        $client-&gt;request('GET', '/some_page');
        $response = $client-&gt;getResponse();
        $profile = $this-&gt;getContainer()-&gt;get('profiler')-&gt;loadProfileFromResponse($response);

        $numMysqlQueries = $profile-&gt;getCollector('db')-&gt;getQueryCount();
        $numMongoQueries = $profile-&gt;getCollector('mongodb')-&gt;getQueryCount();

        $this-&gt;assertEquals($numMysqlQueries, 1);
        $this-&gt;assertEquals($numMongoQueries, 1);
    }
}
</code></pre>

<p>You can abstract this a little bit and add some convenience methods in your base WebTestCase class that would clean this up and make it more reusable. Here is an example:</p>

<pre><code>// ...
class WebTestCase
{
    // ...
    protected function assertResponseQueryCounts(Response $response, $expectedMysql, $expectedMongo)  
    {
        $profile = $this-&gt;getContainer()-&gt;get('profiler')-&gt;loadProfileFromResponse($response);

        $numMysqlQueries = $profile-&gt;getCollector('db')-&gt;getQueryCount();
        $numMongoQueries = $profile-&gt;getCollector('mongodb')-&gt;getQueryCount();

        if ($expectedMysql !== $numMysqlQueries) {
            print_r($profile-&gt;getCollector('db')-&gt;getQueries());
        }
        $this-&gt;assertEquals($expectedMysql, $numMysqlQueries);
        if ($expectedMongo !== $numMongoQueries) {
            print_r($profile-&gt;getCollector('mongodb')-&gt;getQueries());
        }
        $this-&gt;assertEquals($expectedMongo, $numMongoQueries);
    }

    protected function assertRequestQueryCounts($client, $url, $method, $expectedMysql, $expectedMongo)
    {
        if ($client-&gt;getKernel()-&gt;getEnvironment() !== 'test_logging') {
            throw new \InvalidArgumentException(
                'You must pass a client created with createClient(array("environment" =&gt; "test_logging"))'
            );
        }
        $client-&gt;request($method, $url);
        $this-&gt;assertResponseQueryCounts($client-&gt;getResponse(), $expectedMysql, $expectedMongo);
    }
}
</code></pre>

<p>Now the example functional test we showed in the beginning can be cleaned up quite a bit to use the convenience methods we created above:</p>

<pre><code>// ...
class TestSomeQueryCounts extends WebTestCase
{
    // ...
    public function testQueryCounts()
    {
        $client = static::createClient(array(
            'environment' =&gt; 'test_logging'
        ), array(
            'PHP_AUTH_USER' =&gt; 'foobar',
            'PHP_AUTH_PW'   =&gt; 'foobar',
        ));

        $this-&gt;assertRequestQueryCounts($client, '/some_page', 'GET', 1, 1);
    }
}
</code></pre>

<p>I hope this is a helpful tip for someone else.</p>