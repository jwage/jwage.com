---
title: Deploying OpenSky with Fabric
---
<p>At <a href="http://opensky.com" target="_blank">OpenSky</a> we use <a href="http://docs.fabfile.org/en/1.4.3/index.html" target="_blank">Fabric</a> to deploy new versions of software to our servers. We deploy dozens of times a day to our testing environments, and do daily deploys to production.</p>

<p>Our production web nodes are split in to two groups, <strong>group1</strong> and <strong>group2</strong>. It is setup that way so we can easily pull out a group of web nodes from the load balancer for maintenance without disrupting the site.</p>

<p>In this post I will take you through a hotfix scenario and the steps we take to deploy to production.</p>

<p><strong>The Scenario</strong></p>

<p>Imagine we just released <strong>v3.0.0</strong> to production and we discover a critical bug that must be hotfixed.</p>

<p>First thing we need to do is create a hotfix branch. We use <a href="https://github.com/nvie/gitflow" target="_blank">gitflow</a> to assist with streamlining this process. I won&rsquo;t talk too much about it here so I will assume you already know what it is.</p>

<p>Create the hotfix:</p>

<pre><code>git flow hotfix start 3.0.1
</code></pre>

<p>Modify the <strong>opensky/config/version.ini</strong> file and bump the version number:</p>

<pre><code>[parameters]
opensky.version = 3.0.1
</code></pre>

<p>Add the changed file, commit it and push up the hotfix:</p>

<pre><code>git add opensky/config/version.ini
git commit -m"Bump version to 3.0.1"
git push origin hotfix/3.0.1
</code></pre>

<p>Another developer who is responsible for fixing the bug will create a new branch based off of <strong>hotfix/3.0.1</strong> where the fix will be made:</p>

<pre><code>git fetch
git checkout -b fix-the-bug origin/hotfix/3.0.1
</code></pre>

<p>The developer makes some changes and pushes up the new branch:</p>

<pre><code>git add src/changed/file
git commit -m"Fixed nasty bug"
git push origin fix-the-bug
</code></pre>

<p>We use <a href="http://github.com" target="_blank">GitHub</a> pull requests for all of our code changes to be as transparent as possible and maintain a high level of peer code review. The developer would create a pull request for the <strong>fix-the-bug</strong> branch and ask for a team mate to review. We have a special bot named <strong>@pr-nightmare</strong> that runs our tests against the branch to ensure stability before it is merged. Once the branch gets a +1 from @pr-nightmare the team mate can merge the branch in to <strong>hotfix/3.0.1</strong>.</p>

<p>Once it is merged we are ready to finish the hotfix:</p>

<pre><code>git pull origin hotfix/3.0.1
git flow hotfix finish 3.0.1
</code></pre>

<p>The above command will merge <strong>hotfix/3.0.1</strong> in to <strong>production</strong> and <strong>develop</strong> and create a new tag named <strong>v3.0.1</strong> that can be deployed to production.</p>

<p>Now push the finished hotfix up to git:</p>

<pre><code>git push origin develop
git push origin production
git push --tags
</code></pre>

<p>We are all set and ready to go to production with the <strong>v3.0.1</strong> tag using fabric.</p>

<p>First thing we need to do is pull out a group of nodes from the load balancer so that we can deploy <strong>v3.0.1</strong> to it. We will pull out <strong>group1</strong> and leave <strong>group2</strong> live:</p>

<pre><code>fab prod proxy.group2
</code></pre>

<p>Now <strong>group2</strong> is live and <strong>group1</strong> is not receiving any traffic so we can deploy to it:</p>

<pre><code>fab prod:out deploy:stable
</code></pre>

<p>The above command automatically determines what the latest stable tag to deploy is. In this case it will deploy <strong>v3.0.1</strong>.</p>

<p>Once that is done we can flip <strong>group1</strong> live and pull out <strong>group2</strong>:</p>

<pre><code>fab prod proxy.flip
</code></pre>

<p>Now <strong>group1</strong> is live with the hotfix and <strong>group2</strong> is out of rotation. To finish we run the same command as before and deploy the hotfix to <strong>group2</strong> as well:</p>

<pre><code>fab prod:out deploy:stable
</code></pre>

<p>We can push both groups live again and we are done:</p>

<pre><code>fab prod proxy.all
</code></pre>

<p>The process could be even more streamlined and we&rsquo;re actively working to remove steps and make it even easier to deploy to production!</p>