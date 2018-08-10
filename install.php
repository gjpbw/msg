<?php
$packageCorePath = dirname(dirname(dirname(__FILE__)));
$target = MODX_CORE_PATH . 'elements/etc/msg/';
if (!file_exists($target)) {
    mkdir($target, 0744, true);
    $source = $packageCorePath . 'etc/msg';
    $files = glob($source . '/*.ini');
    foreach ($files as $file)
        copy($file, $target . basename($file));
    echo 'To configure, change the files in the directory: ' . $target;
}

