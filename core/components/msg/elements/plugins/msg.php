<?php
/** @var modX $modx */
//if ($contextKey <> 'mgr'){ //использовать не получается, т.к. при инициализации контекст еще неизвестен
global $msg;
if (!isset($msg)){
    include_once(MODX_CORE_PATH.'components/msg/model/msg.class.php');
    $msg =  new Msg();
}
$msg->routeEvent($modx->event->name);
//}