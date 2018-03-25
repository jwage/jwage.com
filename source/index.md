---
layout: default
title: Home
---

<div class="jumbotron jumbotron-fluid">
    <div class="container">
        <h1 class="display-4"><img src="{{ site.url }}/images/me.jpg" class="rounded-circle mr-3" style="width: 50px;"" /> {{ site.title }}</h1>
        <p class="lead">Personal website of Jonathan H. Wage. Lover of technology and tractors!</p>
    </div>
</div>

```php
<?php

class Me
{
    /** @var bool */
    private $happy = false;

    public function happy()
    {
        $this->happy = true;
    }
}

class Tractor
{
    /** @var bool */
    private $tilling = false;

    public function till() : void
    {
        $this->tilling = true;
    }

    public function isTilling() : bool
    {
        return $this->tilling;
    }
}

$me = new Me();

$tractor = new Tractor();
$tractor->till();

if ($tractor->isTilling()) {
    $me->happy();
}
```
