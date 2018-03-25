---
title: Gmail trying to translate our e-mails when it should not
---
<p>Recently at <a href="https://www.opensky.com" target="_blank">OpenSky</a> we migrated our e-mail service provider to Oracle Responsys and since then we&rsquo;ve had issues with Gmail trying to translate our e-mails from random languages. Over the last month it has tried to translate from the following languages:</p>

<ul><li>Finnish</li>
<li>Afrikaans</li>
<li>Catalan</li>
<li>German</li>
<li>Hungarian</li>
<li>Italian</li>
<li>Latvian</li>
<li>Norwegian</li>
<li>Polish</li>
<li>Slovak</li>
<li>Vietnamese</li>
</ul><p>We have tried to find someone at gmail support to help but we haven&rsquo;t had any success. If we did have something in our HTML or e-mail service provider that was making gmail think our emails are in another language, I would think it would be the same language consistently. I&rsquo;ve seen gmail try to translate the same e-mail to multiple different languages. This feels like a bug in gmail causing false positives.</p>

<p>Here is another Responsys customer that is experiencing the same thing <a href="https://litmus.com/community/discussions/7028-gmail-mis-translating-our-english-only-emails-into-other-languages" target="_blank">here</a>.</p>

<p>Here is a post on the gmail help forum about the <a href="https://productforums.google.com/forum/?utm_medium=email&amp;utm_source=footer#!msg/gmail/Tfxb9iu-xgk/NMzTCe_SCgAJ" target="_blank">issue</a></p>

<p>Here is a screenshot of an e-mail coming from one gmail user going to another gmail user. This rules out the problem being anything at all related to our e-mail service provider. I am confident that Gmail language detection is broken:</p>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a2afb0efa08eb4c991b4230.png?publicId=c27733e&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a2afb0efa08eb4c991b4230.png?publicId=c27733e&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<p>Here are some screenshots:</p>

<h2>Finnish</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a20875f6130b21c322880f6.png?publicId=8053e29&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a20875f6130b21c322880f6.png?publicId=8053e29&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Afrikaans</h2>

<p><a href="https://lh4.googleusercontent.com/qYJW4XT79EWda4V2wHbeSEnShEv26W6KxLuC_F7AdKjPzbfda0xmwjsxwe1HRcin6xmevliPKPRdKQe8k-74=w3350-h1574-rw" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed61f40f76c5d8d00dacd.png?publicId=3c10391&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Catalan</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed659c915e42b8c680062.png?publicId=bae13dd&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed659c915e42b8c680062.png?publicId=bae13dd&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>German</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed6966d88eb74d569b2a5.png?publicId=e6b7c09&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed6966d88eb74d569b2a5.png?publicId=e6b7c09&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Hungarian</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed6b7927b0c2f3f380797.png?publicId=56be9fe&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed6b7927b0c2f3f380797.png?publicId=56be9fe&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Italian</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed6e85ec748219267f42e.png?publicId=c245813&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed6e85ec748219267f42e.png?publicId=c245813&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Latvian</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed70940f76c5db61aa640.png?publicId=5c6e8b9&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed70940f76c5db61aa640.png?publicId=5c6e8b9&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Norwegian</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed72ec915e42b8958a52d.png?publicId=f64cf09&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed72ec915e42b8958a52d.png?publicId=f64cf09&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Polish</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed80d78a3c9554f17bba9.png?publicId=5f8d4ea&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed80d78a3c9554f17bba9.png?publicId=5f8d4ea&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Slovak</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed8373474d756662084b1.png?publicId=d301802&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed8373474d756662084b1.png?publicId=d301802&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>

<h2>Vietnamese</h2>

<p><a href="https://cdn1.ykso.co/image/cdn_image/5a1ed856782800481b035f65.png?publicId=9e10659&amp;filterName=original&amp;cacheBuster=1467148600" target="_blank"><img src="https://cdn1.ykso.co/image/cdn_image/5a1ed856782800481b035f65.png?publicId=9e10659&amp;filterName=original&amp;cacheBuster=1467148600" width="100%"/></a></p>
