<?php
$name = dirname(dirname(dirname(__FILE__)));

// delete Namespace
$namespace = $modx->getObject('modNamespace', $name);
if (!empty($namespace)) {
    $namespace->remove();
}

// delete Category
$category = $modx->getObject('modCategory', ['category' => $name]);
if (!empty($category)) {
    $category->remove();
}
