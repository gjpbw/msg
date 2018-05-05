<?php
class MsgXpdo
{
    /** @var string */
    protected $xpdoName;
    /** @var string */
    protected $className;
    /** @var string */
    protected $method;

//**************************************************************************************************************************************************
    function __construct(array $properties = array())
    {
        $this->xpdoName = $properties['xpdoName'];
        $this->className = $properties['className'];
        $this->method = strtolower($properties['method']);
        if (empty($this->method))
            $this->method = 'insert';
    }
//**************************************************************************************************************************************************
    public static function getXpdo($xpdoName)
    {
        global $modx;
        $xpdo =& $modx;
        if (!empty($xpdoName)) {
            if (!isset($modx->$xpdoName)) {
                $className = ucfirst($xpdoName);
                $modx->$xpdoName = new $className;
            }
            $xpdo = & $modx->$xpdoName;
        }
        return $xpdo;
    }
//**************************************************************************************************************************************************
        public function msg($msg = 'test', array $properties = array())
    {
        $output = '';

        if (empty($this->xpdoName))
            Msg::modx('empty xpdoName');
        else{
            if (empty($this->className))
                Msg::modx('empty class');
            else {
                $xpdo =& self::getXpdo($this->xpdoName);
                if ($this->method == 'insert') {
                    $obj = $xpdo->newObject($this->className, $properties);
                    $obj->save();
                    $output = 'ok';
                }
                elseif ($this->method == 'delete'){
                    $obj = $xpdo->getObject($this->className, $properties);
                    if (!empty($obj)){
                        $obj->remove();
                        $output = 'ok';
                    } else
                        Msg::modx(__CLASS__ . ': Object not found');
                }elseif ($this->method == 'update'){
                    $obj = $xpdo->getObject($this->className, $properties['where']);
                    if (!empty($obj)){
                        foreach ($properties['set'] as $k => $v) {
                            $obj->set($k, $v);
                        }
                        $obj->save();
                        $output = 'ok';
                    } else
                        Msg::modx(__CLASS__ . ': Object not found');
                }
                Msg::modx(__CLASS__ . $msg, 3);
            }
        }
        return $output;
    }
//**************************************************************************************************************************************************

}
