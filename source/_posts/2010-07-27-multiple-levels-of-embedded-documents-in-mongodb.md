---
title: Multiple levels of Embedded Documents in MongoDB
categories: [articles]
---
<p>One of the greatest things about <a href="http://www.mongodb.org" target="_blank">MongoDB</a> is the fact that it is schema-less. It makes for a very flexible domain model persistence layer. For example it is possible to have multiple levels of embedded documents. A useful example might be where you have many profiles and each profile has many addresses. In the <a href="http://www.doctrine-project.org" target="_blank">Doctrine</a> MongoDB <a href="http://www.doctrine-project.org/projects/odm" target="_blank">ODM</a> mapping this is trivial.</p>

<p>First create your top level <code>User</code> document:</p>

<pre><code>/** @Document(collection="users") */
class User
{
    /** @Id */
    private $id;

    /** @String */
    private $username;

    /** @EmbedMany(targetDocument="Profile") */
    private $profiles = array();

    public function setUsername($username)
    {
        $this-&gt;username = $username;
    }

    public function addProfile(Profile $profile)
    {
        $this-&gt;profiles[] = $profile;
    }
}
</code></pre>

<p>As you can see we embed another document class named Profile so lets define that as an embedded document:</p>

<pre><code>/** @EmbeddedDocument */
class Profile
{
    /** @String */
    private $name;

    /** @EmbedMany(targetDocument="Address") */
    private $addresses = array();

    public function setName($name)
    {
        $this-&gt;name = $name;
    }

    public function addAddress(Address $address)
    {
        $this-&gt;addresses[] = $address;
    }
}
</code></pre>

<p>Finally, we&rsquo;ve embedded a document in Profile named Address so lets define it:</p>

<pre><code>/** @EmbeddedDocument */
class Address
{
    /** @String */
    private $number;

    /** @String */
    private $street;

    /** @String */
    private $city;

    /** @String */
    private $state;

    /** @String */
    private $zipcode;

    public function setNumber($number)
    {
        $this-&gt;number = $number;
    }

    public function setStreet($street)
    {
        $this-&gt;street = $street;
    }

    public function setCity($city)
    {
        $this-&gt;city = $city;
    }

    public function setState($state)
    {
        $this-&gt;state = $state;
    }

    public function setZipcode($zipcode)
    {
        $this-&gt;zipcode = $zipcode;
    }
}
</code></pre>

<p>Now you can start working with the PHP objects just like you would if no persistence layer was present at all and persist the objects transparently when you are ready to have the state of the objects managed by Doctrine:</p>

<pre><code>$user = new User();
$user-&gt;setUsername('jwage');

$profile = new Profile();
$profile-&gt;setName('Profile #1');

$user-&gt;addProfile($profile);

$address = new Address();
$address-&gt;setNumber('6512');
$address-&gt;setStreet('Mercomatic');
$address-&gt;setCity('Nashville');
$address-&gt;setState('Tennessee');
$address-&gt;setZipcode('37209');

$profile-&gt;addAddress($address);

$profile = new Profile();
$profile-&gt;setName('Profile #2');

$user-&gt;addProfile($profile);

$address = new Address();
$address-&gt;setNumber('475');
$address-&gt;setStreet('Buckhead Ave');
$address-&gt;setCity('Atlanta');
$address-&gt;setState('Georgia');
$address-&gt;setZipcode('30303');

$profile-&gt;addAddress($address);

$dm-&gt;persist($user);
$dm-&gt;flush();
</code></pre>

<p>The above would result in an array being stored in MongoDB like the following:</p>

<pre><code>Array
(
    [_id] =&gt; MongoId Object
        (
        )
    [username] =&gt; jwage
    [profiles] =&gt; Array
        (
            [0] =&gt; Array
                (
                    [name] =&gt; Profile #1
                    [addresses] =&gt; Array
                        (
                            [0] =&gt; Array
                                (
                                    [number] =&gt; 6512
                                    [street] =&gt; Mercomatic
                                    [city] =&gt; Nashville
                                    [state] =&gt; Tennessee
                                    [zipcode] =&gt; 37209
                                )
                        )
                )
            [1] =&gt; Array
                (
                    [name] =&gt; Profile #2
                    [addresses] =&gt; Array
                        (
                            [0] =&gt; Array
                                (
                                    [number] =&gt; 475
                                    [street] =&gt; Buckhead Ave
                                    [city] =&gt; Atlanta
                                    [state] =&gt; Georgia
                                    [zipcode] =&gt; 30303
                                )
                        )
                )
        )
)
</code></pre>

<p>We can then later retrieve the documents from MongoDB and our object domain model will be reconstructed as you have mapped it:</p>

<pre><code>$user = $dm-&gt;findOne('User', array('username' =&gt; 'jwage'));
</code></pre>

<p>You can see the complete working script for this blog post as a gist on <a href="http://gist.github.com/492509" target="_blank">github</a>.</p>
