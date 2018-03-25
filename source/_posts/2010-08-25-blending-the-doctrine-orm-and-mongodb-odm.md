---
title: Blending the Doctrine ORM and MongoDB ODM
categories: [articles]
---
<p>Since the start of the <a href="http://www.doctrine-project.org/projects/mongodb_odm" target="_blank">Doctrine MongoDB Object Document Mapper</a> project people have asked how it can be integrated with the <a href="http://www.doctrine-project.org/projects/orm" target="_blank">ORM</a>. This blog post demonstrates how you can integrate the two transparently, maintaining a clean domain model.</p>

<p>This example will have a <code>Product</code> that is stored in MongoDB and the <code>Order</code> stored in a MySQL database.</p>

<h2>Defining our Document and Entity</h2>

<p>First lets define our <code>Product</code> document:</p>

<pre><code>namespace Documents;

/** @Document */
class Product
{
    /** @Id */
    private $id;

    /** @String */
    private $title;

    public function getId()
    {
        return $this-&gt;id;
    }

    public function getTitle()
    {
        return $this-&gt;title;
    }

    public function setTitle($title)
    {
        $this-&gt;title = $title;
    }
}
</code></pre>

<p>Next create the <code>Order</code> entity that has a <code>$product</code> and <code>$productId</code> property linking it to the <code>Product</code> that is stored with MongoDB:</p>

<pre><code>namespace Entities;

use Documents\Product;

/**
 * @Entity
 * @Table(name="orders")
 * @HasLifecycleCallbacks
 */
class Order
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string")
     */
    private $productId;

    /**
     * @var Documents\Product
     */
    private $product;

    public function getId()
    {
        return $this-&gt;id;
    }

    public function getProductId()
    {
        return $this-&gt;productId;
    }

    public function setProduct(Product $product)
    {
        $this-&gt;productId = $product-&gt;getId();
        $this-&gt;product = $product;
    }

    public function getProduct()
    {
        return $this-&gt;product;
    }
}
</code></pre>

<h2>Event Subscriber</h2>

<p>Now we need to setup an event subscriber that will set the <code>$product</code> property of all <code>Order</code> instances to a reference to the document product so it can be lazily loaded when it is accessed the first time. So first register a new event subscriber:</p>

<pre><code>$eventManager = $em-&gt;getEventManager();
$eventManager-&gt;addEventListener(
    array(\Doctrine\ORM\Events::postLoad), new MyEventSubscriber($dm)
);
</code></pre>

<p>So now we need to define a class named <code>MyEventSubscriber</code> and pass a dependency to the <code>DocumentManager</code>. It will have a <code>postLoad()</code> method that sets the product document reference:</p>

<pre><code>use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class MyEventSubscriber
{
    public function __construct(DocumentManager $dm)
    {
        $this-&gt;dm = $dm;
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $order = $eventArgs-&gt;getEntity();
        $em = $eventArgs-&gt;getEntityManager();
        $productReflProp = $em-&gt;getClassMetadata('Entities\Order')
            -&gt;reflClass-&gt;getProperty('product');
        $productReflProp-&gt;setAccessible(true);
        $productReflProp-&gt;setValue(
            $order, $this-&gt;dm-&gt;getReference('Documents\Product', $order-&gt;getProductId())
        );
    }
}
</code></pre>

<p>The <code>postLoad</code> method will be invoked after an ORM entity is loaded from the database. This allows us to use the <code>DocumentManager</code> to set the <code>$product</code> property with a reference to the <code>Product</code> document with the product id we previously stored.</p>

<p>First create a new <code>Product</code>:</p>

<pre><code>$product = new \Documents\Product();
$product-&gt;setTitle('Test Product');
$dm-&gt;persist($product);
$dm-&gt;flush();
</code></pre>

<p>Now create a new <code>Order</code> and link it to a <code>Product</code> in MySQL:</p>

<pre><code>$order = new \Entities\Order();
$order-&gt;setProduct($product);
$em-&gt;persist($order);
$em-&gt;flush();
</code></pre>

<p>Later we can retrieve the entity and lazily load the reference to the document in MongoDB:</p>

<pre><code>$order = $em-&gt;find('Order', $order-&gt;getId());

// Instance of an uninitialized product proxy
$product = $order-&gt;getProduct();

// Initializes proxy and queries the database
echo "Order Title: " . $product-&gt;getTitle();
</code></pre>

<p>If you were to print the <code>$order</code> you would see that we got back regular PHP objects:</p>

<pre><code>print_r($order);
</code></pre>

<p>The above would output the following:</p>

<pre><code>Order Object
(
    [id:Entities\Order:private] =&gt; 53
    [productId:Entities\Order:private] =&gt; 4c74a1868ead0ed7a9000000
    [product:Entities\Order:private] =&gt; Proxies\DocumentsProductProxy Object
        (
            [__isInitialized__] =&gt; 1
            [id:Documents\Product:private] =&gt; 4c74a1868ead0ed7a9000000
            [title:Documents\Product:private] =&gt; Test Product
        )

)
</code></pre>

<p>That is it! It is not a very abstract example right now but it demonstrates how to utilize the events to do some very interesting things with the Doctrine persistence libraries! I hope that now someone will inspired to create an extension that offers an abstract solution for blending the ORM and ODM together!</p>
