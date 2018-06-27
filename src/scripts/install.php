<?php
$corePath = dirname(dirname(__FILE__));
$modxCorePath = dirname(dirname(dirname($corePath)));
$target = $modxCorePath . 'elements/etc/msg/';
if (!file_exists($target)) {
    mkdir($target, 0744, true);
    $source = $corePath . 'etc/msg';
    $files = glob($source . '/*.ini');
    foreach ($files as $file)
        copy($file, $target . basename($file));
    echo 'To configure, change the files in the directory: ' . $target;
}

$name = dirname(dirname(dirname(__FILE__)));

// create Namespace
$vendorName  = dirname($name);
$path = '{core_path}vendor/' . $vendorName . '/' . $name . '/';
$assetsPath = '{assets_path}components/' . $name . '/';
$namespace = $modx->getObject('modNamespace', $name);
if (empty($namespace))
    $namespace = $modx->newObject('modNamespace', ['name' => $name]);
$namespace->set('path', $path);
$namespace->set('assets_path', $assetsPath);
$namespace->save();


// create Category
$category = $modx->getObject('modCategory', ['category' => $name]);
if (empty($category)) {
    $category = $modx->newObject('modNamespace');
    $category->set('name', $name);
    $category->save();

}
