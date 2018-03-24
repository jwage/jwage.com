---
title: Setting Entity/Document references without hitting the database
---
<p>In <a href="http://www.doctrine-project.org/projects/orm/1.2/docs/en" target="_blank">Doctrine 1</a> when you want to specify a reference you have two options, you can set the foreign key manually:</p>

<pre><code>$user-&gt;setProfileId($profileId);
$user-&gt;save();
</code></pre>

<p>The problem here is that the profile_id is set in the object but the reference to a Profile instance is not set. The next option is to actually set the object reference:</p>

<pre><code>$profile = Doctrine_Core::getTable('Profile')-&gt;find($profileId);
$user-&gt;setProfile($profile);
$user-&gt;save();
</code></pre>

<p>Here the reference is set properly but the downside to this approach is that it requires us to load the entire Profile object just to set the reference. It is silly! Thanks to the <a href="http://www.doctrine-project.org/projects/orm" target="_blank">Doctrine2 ORM</a> and <a href="http://www.doctrine-project.org/projects/mongodb_odm" target="_blank">MongoDB ODM</a> you have the ability to retrieve a reference to an object without having to hit the database. Here is an example:</p>

<pre><code>$profile = $em-&gt;getReference('Profile', $profileId);
$user-&gt;setProfile($profile);
$em-&gt;flush();
</code></pre>

<p>Or the same thing with the MongoDB ODM:</p>

<pre><code>$profile = $dm-&gt;getReference('Profile', $profileId);
$user-&gt;setProfile($profile);
$dm-&gt;flush();
</code></pre>