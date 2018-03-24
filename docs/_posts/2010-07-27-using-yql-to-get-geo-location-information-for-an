---
title: Using YQL to get geo location information for an IP address
---
<p>The <a href="http://developer.yahoo.com/yql/" target="_blank">Yahoo! YQL API</a> has the ability to provide geo location information for IP addresses. Using this in PHP is dead simple. Here is a barebones example demonstrating how you can use PHP and YQL for this:</p>

<pre><code>$ipAddress = '76.22.200.69';
$query = sprintf("select * from ip.location where ip='%s'", $ipAddress);
$queryUrl = "http://query.yahooapis.com/v1/public/yql?q=" . urlencode($query)."&amp;format=json&amp;env=".urlencode("store://datatables.org/alltableswithkeys");
$json = file_get_contents($queryUrl);
$data = json_decode($json);
print_r($data);
</code></pre>

<p>The above code would output the following:</p>

<pre><code>stdClass Object
(
    [query] =&gt; stdClass Object
        (
            [count] =&gt; 1
            [created] =&gt; 2010-07-27T04:27:20Z
            [lang] =&gt; en-US
            [results] =&gt; stdClass Object
                (
                    [Response] =&gt; stdClass Object
                        (
                            [Ip] =&gt; 76.22.200.69
                            [Status] =&gt; OK
                            [CountryCode] =&gt; US
                            [CountryName] =&gt; United States
                            [RegionCode] =&gt; 47
                            [RegionName] =&gt; Tennessee
                            [City] =&gt; Nashville
                            [ZipPostalCode] =&gt; 37205
                            [Latitude] =&gt; 36.1121
                            [Longitude] =&gt; -86.863
                            [Timezone] =&gt; 0
                            [Gmtoffset] =&gt; 0
                            [Dstoffset] =&gt; 0
                        )

                )

        )

)
</code></pre>

<p>I took this a step further and built a little abstraction layer on top of this functionality. Now, retrieving geo location information for IP addresses using the YQL API is easier than ever using the PHP YQL Geo Locator library. You can get the code on <a href="http://github.com/jwage/php-yql-geo-locator" target="_blank">github</a>. Continue reading to learn how to get started!</p>

<p>First, clone the git repository:</p>

<pre><code>$ git clone git://github.com/jwage/php-yql-geo-locator.git
</code></pre>

<p>Now you need to setup your code to use the library:</p>

<pre><code>use GeoLocator\Locator;
use GeoLocator\Location;
use GeoLocator\GoogleMapImage;

require 'php-yql-geo-locator/lib/GeoLocator/Location.php';
require 'php-yql-geo-locator/lib/GeoLocator/Locator.php';
require 'php-yql-geo-locator/lib/GeoLocator/GoogleMapImage.php';
</code></pre>

<p>After setting everything up you are ready to start working with geo locations
using the locator API:</p>

<ul><li>getGeoLocation($ip)</li>
<li>getGoogleMapImageForIps(array $ips)</li>
<li>getGoogleMapImageForIp($ip)</li>
</ul><p>Here is an example using the getGeoLocation() method:</p>

<pre><code>$geoLocation = $locator-&gt;getGeoLocation('76.22.200.69');
</code></pre>

<p>It returns an instance of GeoLocator\Location and has a simple public API for retrieving the geo location information for the IP address:</p>

<pre><code>echo $geoLocation-&gt;getLatitude();
</code></pre>

<p>You can also export all the information to a PHP array using the toArray() method:</p>

<pre><code>print_r($geoLocation-&gt;toArray());
</code></pre>

<p>It would result in an array that looks like this:</p>

<pre><code>Array
(
    [ip] =&gt; 76.22.200.69
    [countryCode] =&gt; US
    [countryName] =&gt; United States
    [regionCode] =&gt; 47
    [regionName] =&gt; Tennessee
    [city] =&gt; Nashville
    [zipPostalCode] =&gt; 37205
    [latitude] =&gt; 36.1121
    [longitude] =&gt; -86.863
    [timezone] =&gt; 0
    [gmtOffset] =&gt; 0
    [dstOffset] =&gt; 0
)
</code></pre>

<p>Get a google map that plots multiple IP addresses:</p>

<pre><code>$image = $locator-&gt;getGoogleMapImageForIps(array(
    '76.22.200.69',
    '74.125.65.106'
));
</code></pre>

<p>The above method returns an instance of GoogleMapImage and has the following API:</p>

<ul><li>setWidth($width)</li>
<li>setHeight($height)</li>
<li>setMaptype($maptype)</li>
<li>setSensor($sensor)</li>
<li>setZoom($zoom)</li>
<li>addLocation(Location $location)</li>
<li>getUrl()</li>
<li>getHTMLImageTag()</li>
<li>__toString()</li>
</ul><p>Now you can just echo the $image to get the HTML image:</p>

<pre><code>echo $image;
</code></pre>

<p>The above would result in an image tag that looks like the following:</p>

<pre><code>&lt;img src="http://maps.google.com/maps/api/staticmap?zoom=&amp;size=550x550&amp;maptype=roadmap&amp;sensor=false&amp;markers=color:blue|label:76.22.200.69|36.1121,-86.863&amp;markers=color:blue|label:74.125.65.106|37.4192,-122.057" width="550" height="550" /&gt;
</code></pre>