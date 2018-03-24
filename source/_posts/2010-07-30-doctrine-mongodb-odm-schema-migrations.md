---
title: Doctrine MongoDB ODM Schema Migrations
---
<p><a href="http://www.mongodb.org" target="_blank">MongoDB</a> is a schema-less database so as your domain model changes in Doctrine, you&rsquo;ll have newer documents with different fields than older documents. Since we don&rsquo;t have a way to rename a field internally in MongoDB, <a href="http://jira.mongodb.org/browse/SERVER-394" target="_blank">yet</a>, the only other option is to fetch all the documents and rename it in your application and update the document. This could take a really long time depending on how big your database is and will require downtime.</p>

<p>Doctrine provides ways for you to &ldquo;eventually&rdquo; migrate all your documents at application run-time. You have several options for working with database schema changes and this blog post will try and demonstrate them!</p>

<h2>Renaming a Field</h2>

<p>Imagine you have a document in your domain named Person and it looked like this:</p>

<pre><code>/** @Document */
class Person
{
    /** @Id */
    public $id;

    /** @String */
    public $name;
}
</code></pre>

<p>Then imagine later you decide you want to rename $name to $fullName like this:</p>

<pre><code>/** @Document */
class Person
{
    /** @Id */
    public $id;

    /** @String */
    public $fullName;
}
</code></pre>

<p>All documents from now on will be created with a fullName property but you&rsquo;ll still have old documents with the name field. You can use the @AlsoLoad annotation here to also load another fields value in that property if it exists:</p>

<pre><code>/** @Document */
class Person
{
    /** @Id */
    public $id;

    /**
     * @String
     * @AlsoLoad("name")
     */
    public $fullName;
}
</code></pre>

<h2>Transforming Data</h2>

<p>Another situation might be you want to load the name and fullName fields in to individual first and last name fields. We can handle this using the @AlsoLoad annotation on a method:</p>

<pre><code>/** @Document */
class Person
{
    /** @Id */
    public $id;

    /** @String */
    public $firstName;

    /** @String */
    public $lastName;

    /** @AlsoLoad({"name", "fullName"}) */
    public function populateFirstAndLastName($fullName)
    {
        $e = explode(' ', $fullName);
        $this-&gt;firstName = $e[0];
        $this-&gt;lastName = $e[1];
    }
}
</code></pre>

<p>So when a document has a field named name, or fullName it will execute the populateFirstAndLastName() method to handle the change when the document is loaded.</p>

<h2>Moving Fields</h2>

<p>You also have a few other options for dealing with changes in your model:</p>

<ul><li>@PostLoad - execute code after all fields have been loaded.</li>
<li>@PrePersist - execute code before your document gets saved.</li>
<li>@NotSaved - load values into fields without saving them again.</li>
</ul><p>Imagine you have some address fields on a Person document:</p>

<pre><code>/** @Document(collection="people") */
class Person
{
    /** @Id */
    public $id;

    /** @String */
    public $name;

    /** @String */
    public $street;

    /** @String */
    public $city;
}
</code></pre>

<p>Then later you want to store a persons address in another object as an embedded document:</p>

<pre><code>/** @EmbeddedDocument */
class Address
{
    /** @String */
    public $street;

    /** @String */
    public $city;

    public function __construct($street, $city)
    {
        $this-&gt;street = $street;
        $this-&gt;city = $city;
    }
}

/**
 * @Document(collection="people")
 * @HasLifecycleCallbacks
 */
class Person
{
    /** @Id */
    public $id;

    /** @String */
    public $name;

    /** @NotSaved */
    public $street;

    /** @NotSaved */
    public $city;

    /** @EmbedOne(targetDocument="Address") */
    public $address;

    /** @PostLoad */
    public function postLoad()
    {
        if ($this-&gt;street !== null || $this-&gt;city !== null)
        {
            $this-&gt;address = new Address($this-&gt;street, $this-&gt;city);
        }
    }
}
</code></pre>

<p>The above will change the document each time it is loaded. If you want to change it permanently in the database you can do it when the document is being updated:</p>

<pre><code>/**
  * @Document(collection="people")
  * @HasLifecycleCallbacks
  */
class Person
{
    // ...

    /** @PreUpdate */
    public function preUpdate()
    {
        if ($this-&gt;street !== null || $this-&gt;city !== null)
        {
            $this-&gt;address = new Address($this-&gt;street, $this-&gt;city);
        }
    }
}
</code></pre>