---
title: Inheritance and Mapped Super Classes in Doctrine
---
<p><a href="http://www.doctrine-project.org/projects/mongodb_odm/1.0/docs/reference/inheritance-mapping/en#single-collection-inheritance" target="_blank">Single Collection Inheritance</a> in the <a href="http://www.doctrine-project.org/projects/mongodb_odm" target="_blank">Doctrine MongoDB ODM</a> allows you to map multiple classes in an inheritance hierarchy to a single collection in <a href="http://www.mongodb.org" target="_blank">MongoDB</a>. An example might be in a CMS where you have several different content types like the base Node, Page and BlogPost which all extends an abstract ContentType class.</p>

<p>First define the ContentType class that is a @MappedSuperclass:</p>

<pre><code>/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class ContentType
{
    /** @Id */
    protected $id;

    /** @Date */
    protected $createdAt;

    /** @Date */
    protected $updatedAt;

    public function getId()
    {
        return $this-&gt;id;
    }

    /** @PreUpdate */
    public function preUpdate()
    {
        $this-&gt;updatedAt = new DateTime();
    }

    /** @PrePersist */
    public function prePersist()
    {
        $this-&gt;createdAt = new DateTime();
        $this-&gt;updatedAt = new DateTime();
    }
}
</code></pre>

<p>Now we can define our base Node content type:</p>

<pre><code>/**
 * @Document(collection="pages")
 * @InheritanceType("SINGLE_COLLECTION")
 * @DiscriminatorField(fieldName="type")
 * @DiscriminatorMap({
 *   "node"="Node",
 *   "page"="Page",
 *   "blog_post"="BlogPost"
 * })
 */
class Node extends ContentType
{
    /** @String */
    protected $title;

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

<p>This gives us our base content type node functionality that we can extend to create the Page document which adds a body field for the page:</p>

<pre><code>/** @Document */
class Page extends Node
{
    /** @String */
    protected $body;

    public function getBody()
    {
        return $this-&gt;body;
    }

    public function setBody($body)
    {
        $this-&gt;body = $body;
    }
}
</code></pre>

<p>And the BlogPost document is a custom type of page that adds some additional fields related to blog posts like an excerpt and tags:</p>

<pre><code>/** @Document */
class BlogPost extends Page
{
    /** @Collection */
    private $tags = array();

    /** @String */
    private $excerpt;

    public function getExcerpt()
    {
        return $this-&gt;excerpt;
    }

    public function setExcerpt($excerpt)
    {
        $this-&gt;excerpt = $excerpt;
    }

    public function addTag($tag)
    {
        $this-&gt;tags[] = $tag;
    }

    public function removeTag($tag)
    {
        $key = array_search($tag, $this-&gt;tags);
        if ($key !== false) {
            unset($this-&gt;tags[$key]);
        }
    }

    public function getTags()
    {
        return $this-&gt;tags;
    }
}
</code></pre>

<p>You can easily add new content types by mapping a document class that extends the base Node. All your documents will be stored in a single collection and a discriminator field will be used to discriminate which class created each document.</p>

<p>Now we can use our document classes and create new instances and persist them. Here is an example where we create a new blog post:</p>

<pre><code>$post = new BlogPost();
$post-&gt;setTitle('Test');
$post-&gt;setExcerpt('Testing');
$post-&gt;setBody('w00t');
$post-&gt;addTag('test');
$dm-&gt;persist($post);
$dm-&gt;flush();
</code></pre>

<p>The above would result in a document like the following in MongoDB:</p>

<pre><code>Array
(
    [_id] =&gt; 4c4f38978ead0ef23f000000
    [createdAt] =&gt; MongoDate Object
        (
            [sec] =&gt; 1280260247
            [usec] =&gt; 0
        )

    [updatedAt] =&gt; MongoDate Object
        (
            [sec] =&gt; 1280260247
            [usec] =&gt; 0
        )

    [title] =&gt; Test
    [body] =&gt; w00t
    [tags] =&gt; Array
        (
            [0] =&gt; test
        )

    [excerpt] =&gt; Testing
    [type] =&gt; blog_post
)
</code></pre>