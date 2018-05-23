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
        $this->proxy = $properties['proxy'];
        $this->timeout = $properties['timeout'];
        if (empty($this->timeout))
            $this->timeout = 3;

        $server = $properties['server'];
        if (empty($server))
            $server = "https://hooks.slack.com";

        $this->server = $server . '/services/';

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