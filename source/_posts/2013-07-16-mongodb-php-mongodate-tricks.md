---
title: MongoDB PHP MongoDate Tricks
categories: [articles]
---
<p>Here are a few tricks I&rsquo;ve learned working with MongoDB and PHP.</p>

<p><strong>Create DateTime from MongoId</strong></p>

<p>Because MongoDB identifiers contain the date you can easily create a <code>DateTime</code> instance from them.</p>

<pre><code>public function getDateTimeFromMongoId(MongoId $mongoId)
{
    $dateTime = new DateTime('@'.$mongoId-&gt;getTimestamp());
    $dateTime-&gt;setTimezone(new DateTimeZone(date_default_timezone_get()));
    return $dateTime;
}
</code></pre>

<p>This is useful when you don&rsquo;t want to create an additional field to store the date a document was created. You can also use the _id index that already exists to paginate and filter/sort by date.</p>

<p><strong>Create MongoId with Date in the Past</strong></p>

<p>For the same reason as above, you can create a MongoId instance with a date in the past. This was copied from a <a href="http://stackoverflow.com/questions/14370143/create-mongodb-objectid-from-date-in-the-past-using-php-driver/14380093#14380093" target="_blank">stackoverflow answer</a> by <a href="http://derickrethans.nl/" target="_blank">Derek Rethans</a>.</p>

<pre><code>public createMongoIdFromTimestamp($timestamp)
{
    $ts = pack('N', $timestamp);
    $m = substr(md5(gethostname()), 0, 3);
    $pid = pack('n', posix_getpid());
    $trail = substr(pack('N', $this-&gt;inc++), 1, 3);

    $bin = sprintf('%s%s%s%s', $ts, $m, $pid, $trail);

    $id = '';
    for ($i = 0; $i &lt; 12; $i++ ) {
        $id .= sprintf('%02X', ord($bin[$i]));
    }

    return new \MongoID($id);
}
</code></pre>

<p>This was useful when migrating legacy data in to a collection where we utilize the _id for pagination and displaying a created date. If we simply created identifiers with todays date then it would show old records with all the same date and pagination/sorting would be broken.</p>
