<?php
class MsgTelegram
{
    /** @var string */
    protected $server;
    /** @var string */
    protected $proxy;
    /** @var int */
    protected $timeout;

//**************************************************************************************************************************************************
    function __construct(array $properties = array())
    {
        $this->proxy = $properties['proxy'];
        $this->timeout = $properties['timeout'];
        if (empty($this->timeout))
            $this->timeout = 3;

        $token = $properties['token'];
        if (empty($token))
            Msg::modx('Empty token. Class ' . __CLASS__);
        else
            $this->server ='https://api.telegram.org/bot' . $token;
    }

//**************************************************************************************************************************************************
    public function msg($msg = 'test', array $properties = array())
    {
        global $modx;
        $output = '';

        if (empty($this->server))
            Msg::modx('empty server');
        else {
            $chat_id = $properties['sendTo'];
            if ($chat_id == 'self'){
                if (!empty($modx->user->id))
                    $chat_id = $modx->user->Profile->get('extended')['telegram'];
                else
                    $chat_id = '';
            } elseif ((int) $chat_id == 0){
                $user = $modx->getObject('modUser', array('username' => $chat_id));
                if (!empty($user)) {
                    $user->getOne('Profile');
                    $chat_id = trim($user->Profile->get('extended')['telegram']);
                    if (empty($chat_id))
                        Msg::error('Ошибка! Не задан "id пользователя telegram" (поле fax в профиле) у пользователя ' . $properties['sendTo']);
                }
            }

            if (empty($chat_id))
                Msg::modx('empty sendTo (chat_id) ' . $properties['sendTo']);
            else {
                $properties = array('text' => $msg);
                if (is_array($chat_id)){
                    $chat_ids = $chat_id;
                    foreach($chat_ids as $chat_id) {
                        $properties['chat_id'] = $chat_id;
                        $output = Msg::curl($this->server . '/sendMessage', $properties, $this->proxy, $this->timeout);
                    }
                }
                else{
                    $properties['chat_id'] = $chat_id;
                    $output = Msg::curl($this->server . '/sendMessage', $properties, $this->proxy, $this->timeout);
                }
            }
        }
        return $output;
    }
//**************************************************************************************************************************************************

}
