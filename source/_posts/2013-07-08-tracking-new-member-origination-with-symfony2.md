---
title: Tracking New Member Origination with Symfony2
categories: [articles]
---
<blockquote>
  <p><strong>NOTE</strong>
  The example code in this blog post has been simplified to make things more concise and easy to read.</p>
</blockquote>

<p>At <a href="http://www.opensky.com" target="_blank">OpenSky</a> it is important for us to track how a member joined so that we can determine causation between joining and any subsequent actions taken on the site, like an order.</p>

<p>To get started create a new class called <code>OriginationManager</code> with a method named <code>updateHistory</code>. It will accept a <code>Request</code> object and record the query parameters from the current URL in the session.</p>

<pre><code>&lt;?php class OriginationManager
{
    public function updateHistory(Request $request)
    {
        $session = $request-&gt;getSession();

        $history = $session-&gt;get('user_origination_history', array());
        $history[] = $request-&gt;query-&gt;all();

        $session-&gt;set('user_origination_history', $history);
    }
}
</code></pre>

<p>Now create an <code>OriginationListener</code> class that will listen to the <code>kernel.request</code> event and make use of the <code>OriginationManager::updateHistory()</code> API we just created.</p>

<pre><code>&lt;?php class OriginationListener
{
    // ...

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event-&gt;getRequest();

        // Only process non-logged-in users
        if ($token = $this-&gt;securityContext-&gt;getToken()) {
            $user = $token-&gt;getUser();
            if ($user instanceof User) {
                return;
            }
        }

        $this-&gt;originationManager-&gt;updateHistory($request);
    }
}
</code></pre>

<p>Now we are tracking the query parameters of logged out users on every request. We can make use of this information later when a user joins. First, lets define a new model that can be used to store the origination information along with the user (assume the model is persisted with <a href="http://www.doctrine-project.org" target="_blank">Doctrine</a>). Each new <code>User</code> gets an <code>Originator</code> record when they join. It allows us to essentially see how a new user entered the site and what clicks they made before joining.</p>

<pre><code>&lt;?php class Originator
{
    /**
     * The raw array of request query data.
     *
     * @var array
     */
    protected $history = array();

    /**
     * The user that originated this member.
     *
     * @var User
     */
    protected $user;

    /**
     * The value of the first osky_origin parameter we encounter.
     *
     * @var string
     */
    protected $origin;

    /**
     * The value of the first osky_source parameter we encounter.
     *
     * @var string
     */
    protected $source;

    // ...
}
</code></pre>

<p>Assume your application already notifies an event named <code>user.created</code>. In <code>OriginationListener</code> create an <code>onUserCreated</code> method that will listen to the <code>user.created</code> event.</p>

<pre><code>&lt;?php

class OriginationListener
{
    // ...

    public function onUserCreated(UserCreatedEvent $event)
    {
        $user = $event-&gt;getUser();
        $request = $event-&gt;getRequest();

        $this-&gt;originationManager-&gt;assignUserOrigination($user, $request);
    }
}
</code></pre>

<p>Next, create the <code>OriginationManager::assignUserOrigination()</code> method. It will utilize the request query parameters we saved on the session earlier to create a new <code>Originator</code> record.</p>

<pre><code>&lt;?php class OriginationManager
{
    public function assignUserOrigination(User $user, Request $request)
    {
        $session = $request-&gt;getSession();

        $history = $session-&gt;get('user_origination_history', array());

        $originator = new Originator();
        $originator-&gt;setHistory($history);

        foreach ($history as $query) {
            if (!$originator-&gt;getOrigin() &amp;&amp; isset($query['osky_origin'])) {
                $originator-&gt;setOrigin($query['osky_origin']);

                if ($user = $this-&gt;userRepository-&gt;find($query['osky_origin'])) {
                    $originator-&gt;setUser($user);
                }
            }

            if (!$originator-&gt;getSource() &amp;&amp; isset($query['osky_source'])) {
                $originator-&gt;setSource($query['osky_source']);
            }
        }

        $user-&gt;setOriginator($originator);

        $session-&gt;remove('user_origination_history');
    }
}
</code></pre>

<p>Now if a logged out user were to visit OpenSky with a parameter named <code>osky_origin</code> in the URL with another users id as the value, the <code>Originator</code> record that gets created for the new member will have a reference to that <code>User</code>. We can then utilize that information do whatever we want. In our case we give the user that got the new <code>User</code> to join a credit and a thank you. The <code>osky_source</code> parameter can be used as an arbitrary reporting variable to help with identifying marketing campaigns.</p>

<p>Keep in mind that this is an example implementation and it omits many details specific to OpenSky for the sake of simplicity. The real implementation we use has dozens of other parameters that can be used to associate records together and assist with reporting. You can add to this implementation and check for custom parameters and standard ones like <code>utm_source</code>, <code>utm_campaign</code>, etc.</p>
