---
title: Asynchronous Events with PHP and Symfony2
categories: [articles]
---
<p><a href="http://symfony.com" target="_blank">Symfony2</a> is a great framework. I use it at <a href="https://opensky.com" target="_blank">OpenSky</a> daily and have contributed a little bit of code to it related to the <a href="http://doctrine-project.org" target="_blank">Doctrine</a> integration.</p>

<h2>Symfony2 EventDispatcher</h2>

<p>One of the core components is the <a href="https://github.com/symfony/EventDispatcher" target="_blank">EventDispatcher</a> and it implements a lightweight version of the <a href="http://en.wikipedia.org/wiki/Observer_pattern" target="_blank">Observer design pattern</a>.</p>

<p>At <a href="http://opensky.com" target="_blank">OpenSky</a> we make heavy use of events. All of our core functionality notifies events that we can then listen to and execute other functionality. Here is an example where we notify the <strong>user.created</strong> event when a new user registers on the site:</p>

<pre><code>$eventDispatcher-&gt;notify(new Event($user, 'user.created'));
</code></pre>

<p>Now we can setup a listener for that and execute some more PHP code in the same process:</p>

<pre><code>&lt;service id="user.created.listener" class="UserCreatedListener"&gt;
    &lt;tag name="kernel.event_listener" event="user.created" method="onUserCreated" /&gt;
&lt;/service&gt;
</code></pre>

<p>The listener class might look something like this:</p>

<pre><code>class UserCreatedListener
{
    public function onUserCreated(EventInterface $event)
    {
        $user = $event-&gt;getSubject(); // $user instanceof User
        $params = $event-&gt;all();
        // do something
    }
}
</code></pre>

<p>The above gets executed in the same process that the <strong>user.created</strong> event was notified in.</p>

<h2>Notifying Asynchronous Events</h2>

<p>What if we want to do something else, like notify a third party API of the new user. We shouldn&rsquo;t do that in the main request as it would slow it down, and it doesn&rsquo;t need to be real time, so an asynchronous event is perfect.</p>

<p>At OpenSky we make use of <a href="https://www.jboss.org/hornetq" target="_blank">HornetQ</a>, a message queue, and a Java application written using <a href="http://en.wikipedia.org/wiki/Mule_(software)" target="_blank">Mule</a> to consume messages our PHP application sends. We&rsquo;ve added a way for Symfony2 events to be forwarded to HornetQ which are then received by our Java app and POSTed back to our PHP application in another request.</p>

<p>Sending an asynchronous event from our PHP app looks like this:</p>

<pre><code>$eventDispatcher-&gt;notifyAsync(new Event($user, 'user.created'));
</code></pre>

<p>The above would not execute any <strong>user.created</strong> listeners in this process, instead the <strong>Event</strong> instance is sent through HornetQ, received by our Java app and POSTed back to our PHP application in another request. The Java app posts to a controller that reconstructs the <strong>Event</strong> object and notifies it on the event dispatcher.</p>

<p>So this ends up happening but in another request/process:</p>

<pre><code>class EventController
{
    public function handle()
    {
        $event = $this-&gt;getEventFactory()-&gt;getReconstructedEvent($request);
        $this-&gt;getEventDispatcher()-&gt;notify($event);
    }
}
</code></pre>

<p>Now any code that listens on <strong>user.created</strong> will be executed in an asynchronous process:</p>

<pre><code>class UserCreatedListener
{
    // ...

    public function onUserCreated(EventInterface $event)
    {
        $user = $event-&gt;getSubject(); // $user instanceof User
        $this-&gt;someApi-&gt;notifyNewUser($user);
    }
}
</code></pre>

<h2>I don&rsquo;t have a message queue</h2>

<p>In order for you to implement the above example you will need some kind of message queue and middle ware. If you don&rsquo;t have that you could very easily stash the calls to notifyAsync() and issue the events as async ajax requests when the response renders in the browser or implement some other kind of event persistence and a console command that runs as a daemon constantly processing the events. It is possible to build out a smaller scale version of the example above that is easy to upgrade later.</p>
