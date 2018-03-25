---
title: Doctrine MongoDB Object Document Mapper in Symfony2
categories: [articles]
---
<h1>MongoDB</h1>

<p>The <a href="http://www.mongodb.org/" target="_blank">MongoDB</a> Object Document Mapper is much like the Doctrine2 ORM in the
way it works and architecture. You only deal with plain PHP objects and they are persisted
transparently without imposing on your domain model.</p>

<blockquote>
  <p><strong>TIP</strong>
  You can read more about the Doctrine MongoDB Object Document Mapper on the
  projects <a href="http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/en" target="_blank">documentation</a>.</p>
</blockquote>

<h2>Configuration</h2>

<p>To get started working with Doctrine and the MongoDB Object Document Mapper you just need
to enable it:</p>

<pre><code># config/config.yml
doctrine_odm.mongodb: ~
</code></pre>

<p>The above YAML is the most simple example and uses all of the default values provided, if
you need to customize more you can specify the complete configuration:</p>

<pre><code># config/config.yml
doctrine_odm.mongodb:
  server: mongodb://localhost:27017
  options:
    connect: true
  metadata_cache_driver: array # array, apc, xcache, memcache
</code></pre>

<p>If you wish to use memcache to cache your metadata and you need to configure the <code>Memcache</code> instance you can do the following:</p>

<pre><code># config/config.yml
doctrine_odm.mongodb:
  server: mongodb://localhost:27017
  options:
    connect: true
  metadata_cache_driver:
    type: memcache
    class: Doctrine\Common\Cache\MemcacheCache
    host: localhost
    port: 11211
    instance_class: Memcache
</code></pre>

<h3>Multiple Connections</h3>

<p>If you need multiple connections and document managers you can use the following syntax:</p>

<pre><code>doctrine_odm.mongodb:
  default_connection: conn2
  default_document_manager: dm2
  metadata_cache_driver: apc
  connections:
    conn1:
      server: mongodb://localhost:27017
      options:
        connect: true
    conn2:
      server: mongodb://localhost:27017
      options:
        connect: true
  document_managers:
    dm1:
      connection: conn1
      metadata_cache_driver: xcache
    dm2:
      connection: conn2
</code></pre>

<p>Now you can retrieve the configured services connection services:</p>

<pre><code>$conn1 = $container-&gt;getService('doctrine.odm.mongodb.conn1_connection');
$conn2 = $container-&gt;getService('doctrine.odm.mongodb.conn2_connection');
</code></pre>

<p>And you can also retrieve the configured document manager services which utilize the above
connection services:</p>

<pre><code>$dm1 = $container-&gt;getService('doctrine.odm.mongodb.dm1_document_manager');
$dm2 = $container-&gt;getService('doctrine.odm.mongodb.dm2_document_manager');
</code></pre>

<h3>XML</h3>

<p>You can specify the same configuration via XML if you prefer that. Here are the same
examples from above in XML.</p>

<p>Simple Single Connection:</p>

<pre><code>&lt;?xml version="1.0" ?&gt;

&lt;container xmlns="http://www.symfony-project.org/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
    xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services <a href="http://www.symfony-project.org/schema/dic/services/services-1.0.xsd" target="_blank">http://www.symfony-project.org/schema/dic/services/services-1.0.xsd</a>
                        <a href="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb" target="_blank">http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb</a> <a href="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd" target="_blank">http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd</a>"&gt;

    &lt;doctrine:mongodb server="mongodb://localhost:27017"&gt;
        &lt;metadata_cache_driver type="memcache"&gt;
            &lt;class&gt;Doctrine\Common\Cache\MemcacheCache&lt;/class&gt;
            &lt;host&gt;localhost&lt;/host&gt;
            &lt;port&gt;11211&lt;/port&gt;
            &lt;instance_class&gt;Memcache&lt;/instance_class&gt;
        &lt;/metadata_cache_driver&gt;
        &lt;options&gt;
            &lt;connect&gt;true&lt;/connect&gt;
        &lt;/options&gt;
    &lt;/doctrine:mongodb&gt;
&lt;/container&gt;
</code></pre>

<p>Multiple Connections:</p>

<pre><code>&lt;?xml version="1.0" ?&gt;

&lt;container xmlns="http://www.symfony-project.org/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:doctrine="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb"
    xsi:schemaLocation="http://www.symfony-project.org/schema/dic/services <a href="http://www.symfony-project.org/schema/dic/services/services-1.0.xsd" target="_blank">http://www.symfony-project.org/schema/dic/services/services-1.0.xsd</a>
                        <a href="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb" target="_blank">http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb</a> <a href="http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd" target="_blank">http://www.symfony-project.org/schema/dic/doctrine/odm/mongodb/mongodb-1.0.xsd</a>"&gt;

    &lt;doctrine:mongodb
            metadata_cache_driver="apc"
            default_document_manager="dm2"
            default_connection="dm2"
            proxy_namespace="Proxies"
            auto_generate_proxy_classes="true"
        &gt;
        &lt;doctrine:connections&gt;
            &lt;doctrine:connection id="conn1" server="mongodb://localhost:27017"&gt;
                &lt;options&gt;
                    &lt;connect&gt;true&lt;/connect&gt;
                &lt;/options&gt;
            &lt;/doctrine:connection&gt;
            &lt;doctrine:connection id="conn2" server="mongodb://localhost:27017"&gt;
                &lt;options&gt;
                    &lt;connect&gt;true&lt;/connect&gt;
                &lt;/options&gt;
            &lt;/doctrine:connection&gt;
        &lt;/doctrine:connections&gt;
        &lt;doctrine:document_managers&gt;
            &lt;doctrine:document_manager id="dm1" server="mongodb://localhost:27017" metadata_cache_driver="xcache" connection="conn1" /&gt;
            &lt;doctrine:document_manager id="dm2" server="mongodb://localhost:27017" connection="conn2" /&gt;
        &lt;/doctrine:document_managers&gt;
    &lt;/doctrine:mongodb&gt;
&lt;/container&gt;
</code></pre>

<h2>Writing Document Classes</h2>

<p>You can start writing document classes just how you normally would write some PHP classes.
The only difference is that you must map the classes to the MongoDB ODM. You can provide
the mapping information via xml, yaml or annotations. In this example, for simplicity and
ease of reading we will use annotations.</p>

<p>First, lets write a simple User class:</p>

<pre><code>// src/Application/HelloBundle/Document/User.php

namespace Application\HelloBundle\Document;

class User
{
    protected $id;
    protected $name;

    public function getId()
    {
        return $this-&gt;id;
    }

    public function setName($name)
    {
        $this-&gt;name = $name;
    }

    public function getName()
    {
        return $this-&gt;name;
    }
}
</code></pre>

<p>This class can be used independent from any persistence layer as it is a regular PHP
class and does not have any dependencies. Now we need to annotate the class so Doctrine
can read the annotated mapping information from the doc blocks:</p>

<pre><code>// ...

/** @Document(collection="users") */
class User
{
    /** @Id */
    protected $id;

    /** @String */
    protected $name;

    // ...
}
</code></pre>

<h2>Using Documents</h2>

<p>Now that you have a PHP class that has been mapped properly you can begin working with
instances of that document persisting to and retrieving from MongoDB.</p>

<p>From your controllers you can access the <code>DocumentManager</code> instances from the container:</p>

<pre><code>class UserController extends Controller
{
    public function createAction()
    {
        $user = new User();
        $user-&gt;setName('Jonathan H. Wage');

        $dm = $this-&gt;container-&gt;getService('doctrine.odm.mongodb.document_manager');
        $dm-&gt;persist($user);
        $dm-&gt;flush();

        // ...
    }
}
</code></pre>

<p>Later you can retrieve the persisted document by its id:</p>

<pre><code>class UserController extends Controller
{
    public function editAction($id)
    {
        $dm = $this-&gt;container-&gt;getService('doctrine.odm.mongodb.document_manager');
        $user = $dm-&gt;find('HelloBundle:User', $id);

        // ...
    }
}
</code></pre>
