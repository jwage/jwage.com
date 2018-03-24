---
title: Forcing HTTPS for all traffic with F5 iRules
---
<p>If you want to force all traffic to HTTPS you can do so pretty easily by adding a F5 iRule to the HTTP VIP.</p>

<pre><code>when HTTP_REQUEST {
    HTTP::redirect "https://[HTTP::host][HTTP::uri]"
}
</code></pre>

<p>You should have two virtual servers configured in the F5, one for HTTP and one for HTTPS. By adding the redirect iRule to the HTTP virtual server it will only be executed for traffic that comes in without HTTPS. This means the iRule code above does not need any kind of conditional to check what the protocol is and only redirect when it is not HTTPS.</p>

<p>If you wanted to wrap some other conditional around the redirect, say you don&rsquo;t want to redirect one particular section of your site, you can do this:</p>

<pre><code>when HTTP_REQUEST {
    if { not ([string tolower [HTTP::path]] starts_with "/non-secure-section") } {
        HTTP::redirect "https://[HTTP::host][HTTP::uri]"
    }
}
</code></pre>