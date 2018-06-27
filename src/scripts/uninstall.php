<?php
$packageCorePath = dirname(dirname(dirname(__FILE__)));

// Подключаем MODX
define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname($packageCorePath)))).'/index.php';

// Включаем обработку ошибок
$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_FATAL);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
$modx->error->message = null; // Обнуляем переменную

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
