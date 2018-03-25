---
title: Storing Files with MongoDB GridFS
categories: [articles]
---
<p>The <a href="http://www.php.net/mongo" target="_blank">PHP MongoDB</a> extension provides a nice and convenient way to store files in chunks of data with the <a href="http://us.php.net/manual/en/class.mongogridfs.php" target="_blank">MongoDB GridFS</a>. It uses two database collections, one to store the metadata for the file, and another to store the contents of the file. The contents are stored in chunks to avoid going over the maximum allowed size of a MongoDB document.</p>

<p>You can easily setup a Document that is stored using the MongoDB GridFS by using the @File annotation:</p>

<pre><code>namespace Documents;

/** @Document(collection="files") */
class Image
{
    /** @Id */
    private $id;

    /** @String */
    private $name;

    /** @File */
    private $file;

    private function getId()
    {
        return $this-&gt;id;
    }

    private function setName($name)
    {
        $this-&gt;name = $name;
    }

    private function getName()
    {
        return $this-&gt;name;
    }

    private function getFile()
    {
        return $this-&gt;file;
    }

    private function setFile($file)
    {
        $this-&gt;file = $file;
    }
}
</code></pre>

<p>Notice the $file property with @File annotation, it tells the Document that it is is to be stored using the MongoGridFS and the <a href="http://www.php.net/MongoGridFSFile" target="_blank">MongoGridFSFile</a> instance is placed in the $file property for you.</p>

<p>Now you can create a new Image setting the path to a file and persist it:</p>

<pre><code>$image = new Image();
$image-&gt;setName('Test image');
$image-&gt;setFile('/path/to/image.png');

$dm-&gt;persist($image);
$dm-&gt;flush();
</code></pre>

<p>Then later you can retrieve that image and render it:</p>

<pre><code>$image = $dm-&gt;createQuery('Documents\Image')
    -&gt;field('name')
    -&gt;equals('Test image')
    -&gt;getSingleResult();

header('Content-type: image/png;');
echo $image-&gt;getFile()-&gt;getBytes();
</code></pre>

<p>You can of course make references to this Image document from another document.
Imagine you had a Profile document and you wanted every Profile to have a profile
image:</p>

<pre><code>namespace Documents;

/** @Document(collection="profiles") */
class Profile
{
    /** @Id */
    private $id;

    /** @String */
    private $name;

    /** @ReferenceOne(targetDocument="Documents\Image") */
    private $image;

    private function getId()
    {
      return $this-&gt;id;
    }

    private function getName()
    {
        return $this-&gt;name;
    }

    private function setName($name)
    {
        $this-&gt;name = $name;
    }

    private function getImage()
    {
        return $this-&gt;image;
    }

    private function setImage(Image $image)
    {
        $this-&gt;image = $image;
    }
}
</code></pre>

<p>Now you can create a new Profile and give it an Image:</p>

<pre><code>$image = new Image();
$image-&gt;setName('Test image');
$image-&gt;setFile('/path/to/image.png');

$profile = new Profile();
$profile-&gt;setName('Jonathan H. Wage');
$profile-&gt;setImage($image);

$dm-&gt;persist($profile);
$dm-&gt;flush();
</code></pre>

<p>If you want to query for the Profile and load the Image reference in a query
you can use:</p>

<pre><code>$profile = $dm-&gt;createQuery('Profile')
    -&gt;field('name')-&gt;equals('Jonathan H. Wage')
    -&gt;getSingleResult();

$image = $profile-&gt;getImage();

header('Content-type: image/png;');
echo $image-&gt;getFile()-&gt;getBytes();
</code></pre>
