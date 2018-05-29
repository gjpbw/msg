<?php

class MsgSlack
{
    /** @var string */
    protected $server;
    /** @var string */
    protected $proxy;
    /** @var int */
    protected $timeout;
    /** @var array */
    protected $properties;

//**************************************************************************************************************************************************
    function __construct(array $properties = array())
    {
        if (!empty($properties['proxy']))
            $this->proxy = $properties['proxy'];

        $server = 'https://hooks.slack.com';
        if (!empty($properties['server']))
            $server = $properties['server'];
        $this->server = $server . '/services/';

        $this->timeout = 3;
        if (!empty($properties['timeout']))
            $this->timeout = $properties['timeout'];

        if (!empty($properties['properties']))
            $this->properties = $properties['properties'];
    }

//**************************************************************************************************************************************************
    public function msg($msg = 'test', array $properties = array())
    {
        $output = '';

        $properties = array_merge($this->properties, $properties);

        if (empty($properties['payload']))
            $properties['payload'] = '{"text": "' . $msg . '"}';

        $chat_id = $properties['sendTo'];
        if (empty($chat_id))
            Msg::modx('empty sendTo (chat_id)');
        else {
            if (is_array($chat_id)) {
                $chat_ids = $chat_id;
                foreach ($chat_ids as $chat_id)
                    $output .= Msg::curl($this->server . $chat_id, $properties, $this->proxy, $this->timeout);
            } else {
                $output = Msg::curl($this->server . $chat_id, $properties, $this->proxy, $this->timeout);
            }
        }
        return $output;
    }
}