---
title: Building Activity Streams using Symfony2, Doctrine2 and MongoDB
categories: [articles]
---
<blockquote>
  <p><strong>NOTE</strong>
  The example code in this blog post has been simplified to make things more concise and easy to read.</p>
</blockquote>

<p>At <a href="http://www.opensky.com" target="_blank">OpenSky</a> we utilize <a href="http://symfony.com" target="_blank">Symfony2</a>, <a href="http://doctrine-project.org" target="_blank">Doctrine2</a> and <a href="http://www.mongodb.org/" target="_blank">MongoDB</a> for streams of activity across our site. We&rsquo;ve based our implementation on the <a href="http://activitystrea.ms/specs/json/1.0/" target="_blank">JSON Activity Streams 1.0</a> specification. This blog post demonstrates how you can easily setup your own activity streams.</p>

<p>An activity item consists of a published date, actor, object, target and verb. It describes an action performed by an entity. Here is an example JSON document of what an activity item might look like:</p>

<pre><code>{
  "published": "2011-02-10T15:04:55Z",
  "actor": {
    "url": "http://example.org/martin",
    "objectType" : "person",
    "id": "tag:example.org,2011:martin",
    "image": {
      "url": "http://example.org/martin/image",
      "width": 250,
      "height": 250
    },
    "displayName": "Martin Smith"
  },
  "verb": "post",
  "object" : {
    "url": "http://example.org/blog/2011/02/entry",
    "id": "tag:example.org,2011:abc123/xyz"
  },
  "target" : {
    "url": "http://example.org/blog/",
    "objectType": "blog",
    "id": "tag:example.org,2011:abc123",
    "displayName": "Martin's Blog"
  }
}
</code></pre>

<p>To get started implementing this let&rsquo;s define a base PHP class that we can use to represent an activity item.</p>

<pre><code>&lt;?php

namespace Project\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\MappedSuperclass(collection="activity_items")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="verb")
 * @ODM\DiscriminatorMap({
 *     "love" = "Project\Document\LoveActivityItem"
 * })
 */
abstract class AbstractActivityItem
{
    /** @ODM\Date */
    protected $published;

    protected $actor;
    protected $object;
    protected $target;

    // ...
}
</code></pre>

<p>For this blog post we&rsquo;re only going to implement one verb called <code>love</code>. On OpenSky you can love products and the action is pushed in to the feed of your followers. So let&rsquo;s get started implementing the <code>LoveActivityItem</code> class:</p>

<pre><code>&lt;?php

namespace Project\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Project\Document\Love;
use Project\Document\Product;
use Project\Document\User;

/** @ODM\Document */
class LoveActivityItem extends AbstractActivityItem
{
    /** @ODM\ReferenceOne(targetDocument="User") */
    protected $actor;

    /** @ODM\ReferenceOne(targetDocument="Love") */
    protected $object;

    /** @ODM\ReferenceOne(targetDocument="Product") */
    protected $target;

    public function __construct(
        User $user, Love $love, Product $product
    )
    {
        $this-&gt;actor = $user;
        $this-&gt;object = $love;
        $this-&gt;target = $product;
    }

    // ...
}
</code></pre>

<p>Now that we have our basic model defined, let&rsquo;s implement the code that will wire everything up. Assume we already have a Symfony event in our application being notified called <code>user.love</code>. So setup a listener that listens to that event and records the activity item:</p>

<pre><code>&lt;?php

namespace Project/Listener;

class ActivityListener
{
    /**
     * @var Project\Activity\ActivityManager
     */
    private $activityManager;

    /**
     * Listens to the `user.love` event.
     */
    public function onUserLove(UserLoveEvent $event)
    {
        $this-&gt;activityManager-&gt;recordUserLove(
            $event-&gt;getUser(),
            $event-&gt;getLove()
        );
    }
}
</code></pre>

<p>Now let&rsquo;s implement the <code>ActivityManager</code> class with the <code>recordUserLove</code> method:</p>

<pre><code>&lt;?php

namespace Project\Activity;

use Doctrine\ODM\MongoDB\DocumentManager;
use Project\Document\LoveActivityItem;
use Project\Document\Love;
use Project\Document\User;

class ActivityManager
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this-&gt;dm = $dm;
    }

    public function recordUserLove(User $user, Love $love)
    {
        $this-&gt;dm-&gt;persist(
            new LoveActivityItem($user, $love, $love-&gt;getProduct())
        );
    }
}
</code></pre>

<p>The new activity item will get flushed to the database at the end of the action with the love since we flush in the controller where the <code>user.love</code> event is notified. Now it is possible to generate a feed of activity items for the whole site, a specific user or even a set of users:</p>

<pre><code>&lt;?php

$user = $dm-&gt;getRepository('Project\Document\User')-&gt;findOneByEmail('jonwage@gmail.com');

$activityItems = $dm-&gt;getRepository('Project\Document\ActivityItem')
    -&gt;createQueryBuilder()
    -&gt;field('actor')-&gt;references($user)
    -&gt;getQuery()
    -&gt;execute();
</code></pre>

<p>That is it. You can easily add new verbs to the model and add code to the listener and manager to record new activities. Good luck! I hope this was helpful for someone!</p>
