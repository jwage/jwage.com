---
title: Fixing nginx "Too many open files" errors
---
<p>At <a href="https://www.opensky.com" target="_blank">OpenSky</a>, we recently migrated to the <a href="http://instartlogic.com/" target="_blank">InstartLogic CDN</a>. One of the features they provide is the ability to resize images by appending <code>?iresize=width:500,height:500</code> to your image URLs.</p>

<p>This feature allowed us to start serving static files directly through nginx instead of going through our application to generate the requested size. Now, InstartLogic does all the resizing work for us and our backend PHP servers are relieved of this responsibility.</p>

<p>This was a nice win for us but it introduced a slight new problem. Nginx started complaining about &ldquo;Too many open files&rdquo;. This was easily fixed for us by setting the <code>worker_rlimit_nofile</code> setting in the <code>nginx.conf</code> file to 30000.</p>

<pre><code>worker_rlimit_nofile 30000;
</code></pre>

<p>After this change our backend PHP servers have a lighter load and the first request for an image through the CDN is much faster.</p>