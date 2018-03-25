---
title: Watch for changes in Sculpin Source
categories: [articles]
tags: [php, sculpin, symfony, twig]
---

I recently rebuilt jwage.com using <a href="https://sculpin.io/">Sculpin</a> and <a href="https://pages.github.com/">GitHub Pages</a>. It was previously hosted on Tumblr and over time I grew frustrated with the lack of control. I wanted a mobile friendly design and I wasn't looking forward to working with HTML & CSS in the Tumblr browser user interface so I decided to give Sculpin a try. I was immediately happy with the choice because it is built using familiar technologies like <a href="https://symfony.com/" target="_blank">Symfony</a> and <a href="https://twig.symfony.com/" target="_blank">Twig</a>.

Here are a few handy utilities I hacked together to help with my development.

## build.sh

For easily building the site in the way that GitHub pages expects:

```command
./build.sh dev
```

Or to build it for prod:

```command
./build.sh prod
```

Here is the source:

```bash
#!/bin/bash

vendor/bin/sculpin generate --env=$1
if [ $? -ne 0 ]; then echo "Could not generate the site"; exit 1; fi

mv docs/CNAME output_$1/CNAME
rm -rf docs
mv output_$1 docs
```

## publish.sh

To make it easy to publish and deploy a new version of the site:

```bash
#!/bin/bash

./build.sh prod

git add --all .
git commit -m "New version of jwage.com"
git push origin master

# Put the dev version back after deploying prod
./build.sh dev

```

## watch.php

For watching changes to your source and automatically triggering `build.sh`:

```php
<?php

$buildScriptPath = __DIR__.'/build.sh dev';

$startPaths = [
    __DIR__.'/app/config/*',
    __DIR__.'/source/*',
];

$lastTime = time();

while (true) {
    $files = recursiveGlob($startPaths);

    foreach ($files as $file) {
        $time = filemtime($file);

        if ($time > $lastTime) {
            $lastTime = time();

            echo sprintf("%s was changed. Building...\n", $file);

            echo shell_exec($buildScriptPath)."\n";

            file_put_contents(__DIR__.'/docs/changed', time());
        }
    }
}

function recursiveGlob(array $paths)
{
    $files = [];

    foreach ($paths as $path) {
        $files = array_merge($files, glob($path));

        foreach ($files as $file) {
            if (is_dir($file)) {
                $dirPath = $file.'/*';

                $dirFiles = recursiveGlob([$dirPath]);

                $files = array_merge($files, $dirFiles);
            }
        }
    }

    return $files;
}
```

Run this script in the root of your project:

```php
php watch.php
```

Then save a file in your project and it should rebuild automatically:

```shell
/data/jwage.com/source/_posts/2018-03-25-sculpin-watch-rebuild.md was changed. Building...
Detected new or updated files
Generating: 100% (249 sources / 0.01 seconds)
Converting: 100% (272 sources / 0.14 seconds)
Formatting: 100% (272 sources / 0.03 seconds)
Processing completed in 0.24 seconds
```

## Browser Refresh

Everytime `watch.php` detects a change, it writes an updated timestamp to a file named `changed` with the current timestamp. Now you can poll this file for changes in your site to automatically refresh the browser. Put this in your layout.

{% verbatim %}
```twig
{% if site.env == 'dev' %}
    <script type="text/javascript">
        var changedUrl = '{{ site.url }}/changed';
    </script>
    <script src="{{ site.url }}/js/watch.js"></script>
{% endif %}
```
{% endverbatim %}

Then in `source/js/watch.js` place the following code:

```javascript
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

var lastTimestamp = getCookie('lastTimestamp');

var watchCallback = function(timestamp) {
    if (lastTimestamp != timestamp) {
        lastTimestamp = timestamp;

        setCookie('lastTimestamp', lastTimestamp, 1);

        window.location.reload();
    }
};

setInterval(function() {
    $.get(changedUrl, watchCallback);
}, 500);
```

Maybe eventually I will put this together in to proper code and packaging so that other people can use it more easily. For now, feel free to copy and paste this in to your project.
