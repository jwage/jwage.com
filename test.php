<?php

$paths = [
    '/post/31292541379/ruler-a-simple-stateless-production-rules-engine',
    '/post/30490196727/mongodb-tailable-cursors',
    '/post/31080076112/doctrine-dbal-php-database-abstraction-layer',
    '/post/70915418483/sending-safari-push-notifications-with-php',
    '/post/100323765531/forcing-https-for-all-traffic-with-f5-irules',
    '/post/30490209661/asynchronous-events-with-php-and-symfony2',
    '/post/103724682571/when-to-inject-the-container',
    '/post/30490176882/multiple-levels-of-embedded-documents-in-mongodb',
    '/post/76799775984/using-the-symfony-expression-language-for-a-reward',
    '/post/55348342841/quotes-from-the-road-to-serfdom-by-friedrich-hayek',
    '/post/31623163785/writing-a-parser-in-php-with-the-help-of-doctrine',
    '/post/30490180105/inheritance-and-mapped-super-classes-in-doctrine',
    '/post/55617183676/mongodb-php-mongodate-tricks',
    '/post/54480997504/building-activity-streams',
    '/post/73741567918/doctrine-is-not-just-an-orm-for-relational',
    '/post/54943645180/tracking-new-member-origination-with-symfony2',
    '/post/30490170860/doctrine-mongodb-object-document-mapper-in',
    '/post/30490181749/storing-files-with-mongodb-gridfs',
    '/post/30490183389/doctrine-mongodb-odm-schema-migrations',
    '/post/103775047571/fixing-nginx-too-many-open-files-errors',
    '/post/30490175122/using-yql-to-get-geo-location-information-for-an',
    '/post/31305747748/tumblr-code-syntax-highlighting',
    '/post/30490207842/logging-mongodb-explains-in-symfony2',
    '/post/30490211196/testing-query-counts-in-functional-web-tests-with',
    '/post/168014035296/gmail-translation',
    '/post/30490205475/a-cool-script-for-running-phpunit-tests-in',
    '/post/30490190002/blending-the-doctrine-orm-and-mongodb-odm',
    '/post/76487435536/tailing-log-files-across-multiple-servers',
    '/post/30490201906/something-to-always-think-about',
    '/post/30490186668/doctrine-annotations-library',
    '/post/30490173350/executing-sql-after-loading-your-data-fixtures-in',
    '/post/70915418483/sending-safari-push-notifications-with-php',
    '/post/30490193467/tips-on-being-successful-in-open-source',
    '/post/31049115791/deploying-opensky-with-fabric',
    '/post/32199136443/doctrine-common-library',
    '/post/30490178495/setting-entitydocument-references-without-hitting',
    '/post/42895003871/sunshinephp-miami',
    '/post/30490185066/array-dereferencing-in-php-trunk',
    '/post/30490188201/new-doctrine-mongodb-odm-documentation',
    '/post/92557031766/2014-vacation-books',
];

$redirects = [];

foreach ($paths as $path) {
    $e = explode('/', $path);

    $slug = end($e);

    $find = glob(sprintf('%s/source/_posts/*%s.md', __DIR__, $slug))[0];

    $redirect = str_replace(__DIR__.'/source/_posts/', '', $find);
    $redirect = str_replace('.md', '/', $redirect);

    $e = explode('-', $redirect);

    $date = $e;
    unset($e[0], $e[1], $e[2]);

    $redirect = sprintf('/posts/%s/%s/%s/%s',
        $date[0], $date[1], $date[2], implode('-', $e)
    );

    $redirects[$path.'/'] = $redirect;
}

echo json_encode($redirects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
