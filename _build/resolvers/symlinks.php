<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/msg/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/msg')) {
            $cache->deleteTree(
                $dev . 'assets/components/msg/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/msg/', $dev . 'assets/components/msg');
        }
        if (!is_link($dev . 'core/components/msg')) {
            $cache->deleteTree(
                $dev . 'core/components/msg/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/msg/', $dev . 'core/components/msg');
        }
    }
}

return true;