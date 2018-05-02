<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;

$success = false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $path = MODX_CORE_PATH.'elements/etc/msg/';
        if (!file_exists($path)) {
            mkdir($path, 0744, true);
            $source = MODX_CORE_PATH . 'components/msg/etc/msg';
            $files = glob($source . '/*.ini');
            foreach ($files as $file)
                copy($file, $path . basename($file));
            $modx->log(modX::LOG_LEVEL_INFO, 'To configure, change the files in the directory: ' . $path);
        }
        break;
    case xPDOTransport::ACTION_UPGRADE:
        $success = true;
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        break;
}

return $success;