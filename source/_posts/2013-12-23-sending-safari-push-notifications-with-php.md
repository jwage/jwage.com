---
title: Sending Safari Push Notifications with PHP
categories: [articles]
---
<p>When OSX Mavericks was released, I was excited to see that push notifications were supported within Safari. So now, just like iOS, we can send push notifications to Safari regardless of whether the browser is open or not. This post gives overview of how to get them working from PHP using the <a href="http://github.com/jwage/php-apns" target="_blank">PHP APNS</a> library.</p>

<h3>Registering with Apple and Generating Certificates</h3>

<p>This post assumes you have read <a href="https://developer.apple.com/library/mac/documentation/NetworkingInternet/Conceptual/NotificationProgrammingGuideForWebsites/PushNotifications/PushNotifications.html#//apple_ref/doc/uid/TP40013225-CH3-SW1" target="_blank">this</a> article from Apple and have already generated your security certificates.</p>

<h3>PHP APNS Library</h3>

<p>The PHP APNS (Apple Push Notification Service) library makes it extremely easy to get Safari push notifications working on your website. Just install it with composer to get started:</p>

<h3>Installation</h3>

<pre><code>composer require jwage/php-apns
composer install
</code></pre>

<h3>Push Package</h3>

<p>Once you have the package installed you can start integrating it with your website. The first thing we need to do is create a base push package for your website. From Apple&rsquo;s website:</p>

<blockquote>
  <p>When a user is asked for permission to receive push notifications, Safari asks your web server for a package. The package contains data that is used by the notification UI, such as your website name and icon, as well as a cryptographic signature. The signature verifies that your notification hasn’t been intercepted by a man-in-the-middle attack and that it is indeed coming from a trusted source: you.</p>
</blockquote>

<p>You can find a sample base push package within the PHP APNS library <a href="https://github.com/jwage/php-apns/tree/master/data/safariPushPackage.base" target="_blank">here</a>. Copy this somewhere in your application and customize the icons in the icon.iconset folder and the website.json:</p>

<pre><code>{
    "websiteName": "WebsiteName",
    "websitePushID": "web.com.domain",
    "allowedDomains": ["http://{{ host }}", "https://{{ host }}"],
    "urlFormatString": "http://{{ host }}/%@",
    "authenticationToken": "{{ userId }}",
    "webServiceURL": "https://{{ host }}/safari_push_notifications/{{ userId }}"
}
</code></pre>

<h3>Web Service Endpoints</h3>

<p>Now it is time to setup some endpoints in your web application for Safari to communicate with. You need endpoints to do the following:</p>

<ul><li>Generate a push package for an individual user.</li>
<li>Register a users device token.</li>
<li>Deregister a users device token.</li>
<li>Record log data sent by Safari when errors occur.</li>
</ul><p>Here is a pseudo controller demonstrating what each endpoint needs to do:</p>

<pre><code>&lt;?php

namespace App\Controller;

use JWage\APNS\Certificate;
use JWage\APNS\Safari\PackageGenerator;

class SafariPushNotificationsController
{
    public function packageAction($userId)
    {
        // Send push notification package to browser when Safari asks user for permission to send you notifications.

        $certificate = new Certificate(file_get_contents('apns.p12'), 'certpassword');
        $packageGenerator = new PackageGenerator(
            $certificate, '/path/to/base/pushPackage/path', 'yourhost.com'
        );

        // returns JWage\APNS\Safari\Package instance
        $package = $packageGenerator-&gt;createPushPackageForUser('userid');

        // send $package-&gt;getZipPath() to the browser
    }

    public function registerAction($userId, $deviceToken)
    {
        // store $deviceToken on the $userId so you can use it later to send pushes
    }

    public function deregisterAction($userId, $deviceToken)
    {
        // remove $deviceToken from $userId
    }

    public function logAction($userId)
    {
        // log information sent for debugging purposes
    }
}
</code></pre>

<h3>Requesting Permission</h3>

<p>Requesting permission to send a user notifications in Safari can be done using this little snippet of javascript on your website:</p>

<pre><code>if ('safari' in window &amp;&amp; 'pushNotification' in window.safari) {
    var checkRemotePermission = function (permissionData) {
        if (permissionData.permission === 'default') {
            window.safari.pushNotification.requestPermission(
                'http://domain.com/safari_push_notifications/{{ userId }}',
                'web.com.domain',
                {
                    'userId': {{ userId }}
                },
                checkRemotePermission
            );
        } else if (permissionData.permission === 'denied') {
            // do something when permission is denied
        } else if (permissionData.permission === 'granted') {
            // do something when permission is granted
        }
    };

    // Ensure that the user can receive Safari Push Notifications.
    var permissionData = window.safari.pushNotification.permission('web.com.domain');
    checkRemotePermission(permissionData);
}
</code></pre>

<p>When a user visits your website with Safari in OSX Mavericks it will hit your web services endpoint, download the push package and ask the user if they want to receive notifications from your website.</p>

<h3>Sending Push Notifications</h3>

<p>Once you have done all of the above, you should be ready to send push notifications. The PHP APNS library makes this extremely easy. Here is an example:</p>

<pre><code>use JWage\APNS\Certificate;
use JWage\APNS\Client;
use JWage\APNS\Sender;
use JWage\APNS\SocketClient;

$certificate = new Certificate(file_get_contents('apns.pem'));
$socketClient = new SocketClient($certificate, 'gateway.push.apple.com', 2195);
$client = new Client($socketClient);
$sender = new Sender($client);

$sender-&gt;send('devicetoken', 'Title of push', 'Body of push', 'http://deeplink.com');
</code></pre>

<p>You can create an easy to use service in your application for sending push notifications to an instance of <code>User</code>, assuming it implements a <code>getSafariDeviceToken()</code> method:</p>

<pre><code>class SafariPushNotificationSender
{
    private $sender;

    public function __construct(Sender $sender)
    {
        $this-&gt;sender = $sender;
    }

    public function sendToUser(User $user, $title, $body, $link)
    {
        return $this-&gt;sender-&gt;send($user-&gt;getSafariDeviceToken(), $title, $body, $link);
    }
}
</code></pre>

<p>Now it is as simple as the following:</p>

<pre><code>$safariPushNotificationSender = new SafariPushNotificationSender($sender);
$safariPushNotificationSender-&gt;sendToUser($user, 'Title of push', 'Body of push', 'http://deeplink.com');
</code></pre>

<p>I hope this was helpful! If you have any questions please leave them in the comments. Enjoy!</p>
