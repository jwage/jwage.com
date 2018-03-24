<?php

include 'vendor/autoload.php';

$consumerKey = 'J1cFSUvv7z4TfdttWbynD5rc9JJQU8crsqdEt6MnJgnAXjKzGg';
$consumerSecret = 'bFr3aqHbHk9BSmaXJ0JGi2GSQRaOPRzVH3zJiCsLIEJ50K3C99';

$client = new Tumblr\API\Client($consumerKey, $consumerSecret);
//$client->setToken($token, $tokenSecret);

$results = $client->getBlogPosts('jwage.com', [
    'limit' => 100,
]);

$template = <<<TEMPLATE
---
title: {{ title }}
---
{{ body }}
TEMPLATE;

foreach ($results->posts as $post) {
    $content = $template;

    $find = [
        '{{ title }}',
        '{{ body }}',
    ];

    $replace = [
        $post->title,
        $post->body
    ];

    $content = str_replace($find, $replace, $content);

    $date = date('Y-m-d', $post->timestamp);
    $fileName = sprintf('%s-%s', $date, $post->slug);

    $filePath = sprintf('source/_posts/%s', $fileName);

    file_put_contents($filePath, $content);
}
