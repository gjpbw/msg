<?php

class MsgApi
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
        $this->server = $properties['server'];

        $this->timeout = $properties['timeout'];
        if (empty($this->timeout))
            $this->timeout = 3;

        $this->properties = $properties['properties'];
    }

//**************************************************************************************************************************************************
    public function msg($msg = 'test', array $properties = array())
    {
        $output = '';

        $server = $this->server;
        $server = Msg::parseValue($server, ['msg' => $msg]);

        $properties = array_merge($this->properties, $properties);
        Msg::parseArray($properties, ['msg' => $msg]);

        if (empty($this->server))
            Msg::modx('empty server');
        else {
            $sendTo = $properties['sendTo'];
            unset($properties['sendTo']);
            if (empty($sendTo))
                $output = Msg::curl($server, $properties, $this->proxy, $this->timeout);
            else {
                if (is_array($sendTo)) {
                    $sendTos = $sendTo;
                    foreach ($sendTos as $sendTo) {
                        $server = Msg::parseValue($server, ['sendTo' => $sendTo]);
                        Msg::parseArray($properties, ['sendTo' => $sendTo]);
                        $output .= Msg::curl($server, $properties, $this->proxy, $this->timeout);
                    }
                } else {
                    $server = Msg::parseValue($server, ['sendTo' => $sendTo]);
                    Msg::parseArray($properties, ['sendTo' => $sendTo]);
                    $output = Msg::curl($server, $properties, $this->proxy, $this->timeout);
                }
            }
        }
        return $output;
    }
//**************************************************************************************************************************************************

}
