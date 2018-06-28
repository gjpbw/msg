<?php
/**
 * Created by PhpStorm.
 * User: ld
 * Date: 28.06.2018
 * Time: 6:06
 */

namespace Msg;


class Installer
{

//**************************************************************************************************************************************************
    public static function install()
    {
        $packageCorePath = dirname(dirname(dirname(__FILE__)));

// Подключаем MODX
        define('MODX_API_MODE', true);
        require dirname(dirname(dirname(dirname($packageCorePath)))).'/index.php';

// Включаем обработку ошибок
        $modx->getService('error','error.modError');
        $modx->setLogLevel(modX::LOG_LEVEL_FATAL);
        $modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
        $modx->error->message = null; // Обнуляем переменную

        $target = MODX_CORE_PATH . 'elements/etc/msg/';
        if (!file_exists($target)) {
            mkdir($target, 0744, true);
            $source = $packageCorePath . 'etc/msg';
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

    }
//
}