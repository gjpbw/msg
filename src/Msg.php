<?php
class Msg
{
    const MODX_ELEMENTS_PATH = MODX_CORE_PATH . 'elements/';
    /** @var array */
    protected $providers;
    /** @var array */
    protected $events;
    /** @var array */
    protected $sendTo;

//**************************************************************************************************************************************************
    function __construct()
    {
        $this->providers = $this->parse_ini_file('providers.ini');
        $this->events = $this->parse_ini_file('events.ini');
        $this->sendTo = $this->parse_ini_file('sendTo.ini');
    }

//**************************************************************************************************************************************************
    private function parse_ini_file($name)
    {
        $a = array();

        $file = self::MODX_ELEMENTS_PATH . 'etc/msg/' . $name;
        if (file_exists($file))
            $a = parse_ini_file($file, true);
        else
            self::modx('ERROR: File is not found: ' . $file);

        return $a;
    }

//**************************************************************************************************************************************************
    public function msg($providerName, $msg, $arguments)
    {
        if (!isset($this->providers[$providerName]['obj'])) {
            if (isset($this->providers[$providerName]['provider'])) {
                $className = $this->providers[$providerName]['provider'];
            } else
                $className = $providerName;
            $className = ucfirst(strtolower($className));
            $file = dirname(__FILE__) . '/Msg/' . $className . '.php';
            if (file_exists($file)) {
                include_once $file;

                if (isset($this->providers[$providerName]))
                    $properties = $this->providers[$providerName];
                else
                    $properties = array();

                $fullClassName = 'Msg\\' . $className;
                $this->providers[$providerName]['obj'] = new $fullClassName ($properties);
            } else
                self::modx('ERROR: File is not found: ' . $file);
        }

        $output = $this->providers[$providerName]['obj']->msg($msg, $arguments);

        return $output;
    }

//**************************************************************************************************************************************************
    public function event($name, $input, array $properties)
    {
        global $modx;
        $output = '';
        $msgSuffix = '';

        $limit = 0;
        $modx->lexicon->load('gjpbw-msg:default');

        $a = $this->events[$name];
        if (is_array($a)) {
            if (is_string($input))
                $msg = $name . ": \n" . $input;
            else
                $msg = $input;
            foreach ($a as $provider => $v) {
                if ($provider == 'limit') {
                    $limit = (int)$v;
                } elseif ($provider == 'properties') {
                    $properties = array_merge($v, $properties);
                } else {
                    if (isset($this->providers[$provider]['provider']))
                        $providerName = strtolower($this->providers[$provider]['provider']);
                    else
                        $providerName = '';

                    if (empty($providerName))
                        $providerName = $provider;

                    $s = explode(',', $v);
                    foreach ($s as $sendTo) {
                        $sendTo = trim($sendTo);
                        if ($sendTo == 'sendTo') {
                            $sendTo = $properties['sendTo'];
                        } elseif ($sendTo == 'empty') {
                            // Ничего не делаем
                        } elseif ($sendTo !== 'self')
                            $sendTo = $this->sendTo[$providerName][$sendTo];

                        $properties['sendTo'] = $sendTo;

                        $msgLimit = false;
                        if (!empty($limit)) {
                            $hourTime = floor(time() / 3600);

                            if ($sendTo == 'self')
                                $keySuffix = $name . '.' . $modx->user->id;
                            else
                                $keySuffix = $name . '.' . $sendTo;

                            $lastEventTime = $modx->cacheManager->get('msg.lastEventTime.' . $keySuffix);
                            if ((!empty($lastEventTime)) && ($lastEventTime == $hourTime)) {
                                $lastEventCount = $modx->cacheManager->get('msg.lastEventCount.' . $keySuffix);
                                if (!isset($lastEventCount))
                                    $lastEventCount = 1;    //если lastEventCount нет, lastEventTime значит 1 раз уже событие было

                                $lastEventCount++;

                                $modx->cacheManager->set('msg.lastEventCount.' . $keySuffix, $lastEventCount);
                                if ($lastEventCount == $limit)
                                    $msgSuffix = "\n***********\n" . $modx->lexicon('msg_limit', array('event' => $name));
                                elseif ($lastEventCount > $limit)
                                    $msgLimit = true;

                            } else {
                                $modx->cacheManager->set('msg.lastEventTime.' . $keySuffix, $hourTime);
                                $modx->cacheManager->delete('msg.lastEventCount.' . $keySuffix);
                            }
                        }
                        if (!$msgLimit) {
                            if (empty($msgSuffix)) {
                                $output .= $this->msg($provider, $msg, $properties);
                            } elseif (is_string($msg)) {
                                $output .= $this->msg($provider, $msg . $msgSuffix, $properties);
                            } else {
                                $output .= $this->msg($provider, $msg, $properties);
                                $output .= $this->msg($provider, $msgSuffix, $properties);
                            }
                        }
                    }
                }
            }
        } else
            self::modx('Error: event "' . $name . '" not found. Fix file "\core\elements\etc\msg\events.ini"');
        return $output;
    }

//**************************************************************************************************************************************************
    public static function __callStatic($name, $arguments)
    {
        static $msg;
        if (!isset($msg))
            $msg = new Msg();

        $input = $arguments[0];
        $properties = array();
        if (count($arguments) > 1)
            $properties = $arguments[1];
        return $msg->event($name, $input, $properties);
    }

//**************************************************************************************************************************************************
    public static function error($msg)
    {
        global $modx;
        $s = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

        if (is_array($msg))
            $msg = json_encode($msg);
        elseif (!is_string($msg))
            $msg = (string)$msg;

        $msg = $msg . "\n" .
            "============================\n" .
            "[" . $s['class'] . $s['type'] . $s['function'] . "()]\n" .
            "user = " . $modx->user->username . "\n" .
            "url = " . MODX_URL_SCHEME . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n" .
            "(line) file = (" . $s['line'] . ") " . $s['file'] . "\n";
        self::modx($msg);
        return self::__callStatic('error', array($msg));
    }

//**************************************************************************************************************************************************
    public static function debug($msg)
    {
        global $modx;
        $output = '';
        if ($modx->user->isMember('Administrator')) {
            $s = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $msg = '[' . $s['class'] . $s['type'] . $s['function'] . '()] ' . $msg;
            self::modx($msg, 4);
            $output = self::__callStatic('debug', array($msg));
        }
        return $output;
    }

//**************************************************************************************************************************************************
//		MODX_LOG_LEVEL_FATAL = 0
//		MODX_LOG_LEVEL_ERROR = 1
//		MODX_LOG_LEVEL_WARN = 2
//		MODX_LOG_LEVEL_INFO = 3
//		MODX_LOG_LEVEL_DEBUG = 4
    public static function modx($msg, $logLevel = 1)
    {
        global $modx;


        $oldLevel = $modx->getLogLevel();
        if ($oldLevel < $logLevel) {
            $modx->setLogLevel($logLevel);
            $modx->log($logLevel, $msg);
            $modx->setLogLevel($oldLevel);
        }
        else
        $modx->log($logLevel, $msg);

    }

//**************************************************************************************************************************************************

    public static function email($msg, $sendTo, $properties = array())
    {
        $output = '';
        $file = dirname(__FILE__) . 'msgemail.class.php';
        if (file_exists($file)) {
            include_once $file;
            $email = new Msg\Email();
            $properties['sendTo'] = $sendTo;
            $output = $email->msg($msg, $properties);
        }
        return $output;
    }

//**************************************************************************************************************************************************
    public static function send($properties = array())
    {
        $event = $properties['event'];
        unset ($properties['event']);
        return self::__callStatic($event, [$properties['msg'], $properties]);
    }

//**************************************************************************************************************************************************
    public static function curlError($msg)
    {
        global $modx;
        $s = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

        if (is_array($msg))
            $msg = json_encode($msg);
        elseif (!is_string($msg))
            $msg = (string)$msg;

        $msg = $msg . "\n" .
            "============================\n" .
            "[" . $s['class'] . $s['type'] . $s['function'] . "()]\n" .
            "user = " . $modx->user->username . "\n" .
            "url = " . MODX_URL_SCHEME . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n" .
            "(line) file = (" . $s['line'] . ") " . $s['file'] . "\n";
        self::modx($msg);
        return self::__callStatic('curlError', array($msg));
    }

//**************************************************************************************************************************************************
    public static function curl($server, array $properties = array(), $proxy = '', $timeout = 3, $isGet = false)
    {
        global $modx;
        $modx->lexicon->load('gjpbw-msg:default');
//        foreach ($properties as $k => $v)
//            $properties[$k] = str_replace('"', '\"', $v);

        $ch = curl_init(); // инициализируем сессию curl
        curl_setopt($ch, CURLOPT_URL, $server); // указываем URL, куда отправлять запрос
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);// лимит перенаправлений
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // таймаут соединения

        if (!$isGet) {
            curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $properties); // добавляем данные POST-запроса
        }

        if (!empty($proxy))
            curl_setopt($ch, CURLOPT_PROXY, $proxy);

        $output = curl_exec($ch); // выполняем запрос

        if (curl_errno($ch)) {
            Msg::modx('server=' . $server . "\n". $modx->lexicon('msg_curlError', array('input' => json_encode($properties, JSON_UNESCAPED_UNICODE ), 'output' => $output, 'error' => curl_error($ch))));
            Msg::curlError('curlError ' . $_SERVER['SERVER_NAME']);
        }
        curl_close($ch); // завершаем сессию
        return $output;
    }

//**************************************************************************************************************************************************
    public static function parseValue($value, $properties)
    {
        foreach ($properties as $k => $v)
            $value = str_replace('[[+' . $k . ']]', $v, $value);

        return $value;
    }

//**************************************************************************************************************************************************
    public static function parseArray(& $a, $properties)
    {
        foreach ($a as $k => $v)
            $a[$k] = self::parseValue($v, $properties);
    }
//**************************************************************************************************************************************************
    public static function test($properties = array())
    {
        return 'test' . json_encode($properties);
    }
}

