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

        $sendTo = $properties['sendTo'];
        unset($properties['sendTo']);

        if ($sendTo == 'self') {
            if (!empty($modx->user->id))
                $sendTo = $modx->user->Profile->get('extended')['telegram'];
            else
                $sendTo = '';
        } elseif ($sendTo == 'empty') {
            // Ничего не делаем
        } elseif ((int)$sendTo == 0) {
            $user = $modx->getObject('modUser', array('username' => $sendTo));
            if (!empty($user)) {
                $user->getOne('Profile');
                $sendTo = trim($user->Profile->get('extended')['telegram']);
                if (empty($sendTo))
                    Msg::error('Ошибка! Не задан `id  telegram` в профиле пользователя ' . $properties['sendTo']);
            }
        }

        if (empty($sendTo))
            Msg::modx('empty sendTo (chat_id) ' . $sendTo);
        else {
            $properties['text'] = $msg;
            if (is_array($sendTo)) {
                $sendTos = $sendTo;
                foreach ($sendTos as $sendTo) {
                    $properties['chat_id'] = $sendTo;
                    $output .= Msg::curl($this->server . '/' . $this->method, $properties, $this->proxy, $this->timeout);
                }
            } else {
                $properties['chat_id'] = $sendTo;
                $output = Msg::curl($this->server . '/' . $this->method, $properties, $this->proxy, $this->timeout);
            }
        }
        return $output;
    }
}
