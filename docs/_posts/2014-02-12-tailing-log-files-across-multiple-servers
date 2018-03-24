---
title: Tailing Log Files Across Multiple Servers
---
<p>Install the utility named <code>dsh</code>:</p>

<pre><code>apt-get install dsh
brew install dsh
</code></pre>

<p>Add the list of servers you want to work with to <code>.dsh/machines.list</code> in your home directory. It might look something like this:</p>

<pre><code>web1.prod.domain.com
web2.prod.domain.com
web3.prod.domain.com
web4.prod.domain.com
web5.prod.domain.com
web6.prod.domain.com
</code></pre>

<p>Add this to your <code>.ssh/config</code>:</p>

<pre><code>Host web*.prod.domain.com
  User your_username
</code></pre>

<p>Now you can do things like this:</p>

<pre><code>dsh -Mac -- "tail -f /var/log/some_log_file.log"
</code></pre>

<p>Combine that with grep to look for certain things that you are logging:</p>

<pre><code>dsh -Mac -- "tail -f /var/log/some_log_file.log" | grep "look for something"
</code></pre>