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
