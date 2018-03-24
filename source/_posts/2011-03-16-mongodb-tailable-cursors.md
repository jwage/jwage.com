---
title: MongoDB Tailable Cursors
---
<p>Tailable cursors are a cool feature of MongoDB. It allows you to setup scripts that run forever and are constantly processing new data that gets inserted to the collection. You need a capped collection in order to tail a cursor so just create a new collection and make sure it is capped to a size you can specify:</p>

<pre><code>&gt; db.createCollection("my_collection", {capped:true, size:100000})
</code></pre>

<p>Now you can tail a cursor in your favorite language. I have a few examples of the same script in PHP, Ruby, Python and Perl!</p>

<h2>PHP</h2>

<pre><code>$mongo = new Mongo();
$db = $mongo-&gt;selectDB('my_db')
$coll = $db-&gt;selectCollection('my_collection');
$cursor = $coll-&gt;find()-&gt;tailable(true);
while (true) {
    if ($cursor-&gt;hasNext()) {
        $doc = $cursor-&gt;getNext();
        print_r($doc);
    } else {
        sleep(1);
    }
}
</code></pre>

<h2>Ruby</h2>

<pre><code>db   = Mongo::Connection.new().db('my_db')
coll = db.collection('my_collection')
cursor = Mongo::Cursor.new(coll, :tailable =&gt; true)
loop do
  if doc = cursor.next_document
    puts doc
  else
    sleep 1
  end
end
</code></pre>

<h2>Python</h2>

<p>By <a href="http://wombatnation.com" target="_blank">Robert Stewart</a>:</p>

<pre><code>from pymongo import Connection
import time

db = Connection().my_db
coll = db.my_collection
cursor = coll.find(tailable=True)
while cursor.alive:
    try:
        doc = cursor.next()
        print doc
    except StopIteration:
        time.sleep(1)
</code></pre>

<h2>Perl</h2>

<p>By <a href="http://tong.ijenko.net/" target="_BLANK">Max</a></p>

<pre><code>use 5.010;

use strict;
use warnings;
use MongoDB;

my $db = MongoDB::Connection-&gt;new;
my $coll = $db-&gt;my_db-&gt;my_collection;
my $cursor = $coll-&gt;find-&gt;tailable(1);
for (;;)
{
    if (defined(my $doc = $cursor-&gt;next))
    {
        say $doc;
    }
    else
    {
        sleep 1;
    }
}
</code></pre>

<p>If you want to provide the same example in another language please add it in the comments and I&rsquo;d be glad to include it here!</p>