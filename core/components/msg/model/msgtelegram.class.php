<?php

class MsgTelegram
{
    /** @var string */
    protected $server;
    /** @var string */
    protected $proxy;
    /** @var int */
    protected $timeout;
    /** @var string */
    protected $method;

//**************************************************************************************************************************************************
    function __construct(array $properties = array())
    {
        if (!empty($properties['proxy']))
            $this->proxy = $properties['proxy'];

        $this->timeout = 3;
        if (!empty($properties['timeout']))
            $this->timeout = $properties['timeout'];

        $this->method = 'sendMessage';
        if (!empty($properties['method']))
            $this->method = $properties['method'];

        if (empty($properties['token']))
            Msg::modx('Empty token. Class ' . __CLASS__);
        else {
            $token = $properties['token'];

            $server = 'https://api.telegram.org';
            if (!empty($properties['server']))
                $server = $properties['server'];

            $this->server = $server . '/bot' . $token;
        }
    }

//**************************************************************************************************************************************************
    public function msg($msg = 'test', array $properties = array())
    {
        global $modx;
        $output = '';

        $chat_id = $properties['sendTo'];
        if ($chat_id == 'self') {
            if (!empty($modx->user->id))
                $chat_id = $modx->user->Profile->get('extended')['telegram'];
            else
                $chat_id = '';
        } elseif ($chat_id == 'empty') {
            // Ничего не делаем
        } elseif ((int)$chat_id == 0) {
            $user = $modx->getObject('modUser', array('username' => $chat_id));
            if (!empty($user)) {
                $user->getOne('Profile');
                $chat_id = trim($user->Profile->get('extended')['telegram']);
                if (empty($chat_id))
                    Msg::error('Ошибка! Не задан `id  telegram` в профиле пользователя ' . $properties['sendTo']);
            }
        }

        if (empty($chat_id))
            Msg::modx('empty sendTo (chat_id) ' . $properties['sendTo']);
        else {
            $properties = array('text' => $msg);
            if (is_array($chat_id)) {
                $chat_ids = $chat_id;
                foreach ($chat_ids as $chat_id) {
                    $properties['chat_id'] = $chat_id;
                    $output .= Msg::curl($this->server . '/' . $this->method, $properties, $this->proxy, $this->timeout);
                }
            } else {
                $properties['chat_id'] = $chat_id;
                $output = Msg::curl($this->server . '/' . $this->method, $properties, $this->proxy, $this->timeout);
            }
        }
        return $output;
    }
}
