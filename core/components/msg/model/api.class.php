<?php
namespace msg;
class Api
{
    /** @var string */
    protected $server;
    /** @var string */
    protected $proxy;

//**************************************************************************************************************************************************
    function __construct(array $properties = array())
    {
        $this->proxy = $properties['proxy'];
        $this->server = $properties['server'];
    }

//**************************************************************************************************************************************************
        public function msg($msg = 'test', array $properties = array())
    {
        $output = '';

        if (empty($this->server))
            \Msg::modx('empty server');
        else {
            $sendTo = $properties['sendTo'];
            if (empty($sendTo))
                \Msg::modx('empty sendTo ' . $properties['sendTo']);
            else {

                $properties['msg'] = $msg;
                if (is_array($sendTo)){
                    $sendTos = $sendTo;
                    foreach($sendTos as $sendTo) {
                        $properties['sendTo'] = $sendTo;
                        $output = \Msg::curl($this->server, $properties, $this->proxy);
                    }
                }
                else {
                    $properties['sendTo'] = $sendTo;
                    $output = \Msg::curl($this->server, $properties, $this->proxy);
                }
            }
        }
        return $output;
    }
//**************************************************************************************************************************************************

}
