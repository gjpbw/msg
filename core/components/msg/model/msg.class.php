<?php

class Msg
{
    const MODX_ELEMENTS_PATH = MODX_CORE_PATH.'elements/';
    /** @var array */
    protected $providers;
    /** @var array */
    protected $events;
    /** @var array */
    protected $sendTo;

//**************************************************************************************************************************************************
    function __construct(array $context = array())
    {
        $this->providers = $this->parse_ini_file('providers.ini');
        $this->events = $this->parse_ini_file('events.ini');
        $this->sendTo = $this->parse_ini_file('sendTo.ini');
    }
//**************************************************************************************************************************************************
    private function parse_ini_file($name)
    {
        $a = array();

        $file = self::MODX_ELEMENTS_PATH.'etc/msg/' . $name;
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
            $className = 'Msg' . ucfirst($className);
            $file = dirname(__FILE__) . '/' . $className . '.class.php';
            if (file_exists($file)) {
                include_once $file;

                if (isset($this->providers[$providerName]))
                    $properties = $this->providers[$providerName];
                else
                    $properties = array();

                $this->providers[$providerName]['obj'] = new $className ($properties);
            } else
                self::modx('ERROR: File is not found: ' . $file);
        }

        $output = $this->providers[$providerName]['obj']->msg($msg, $arguments);

        return $output;
    }

//**************************************************************************************************************************************************
    public function event($name, $input, array $properties)
    {
        $output = '';

        $a = $this->events[$name];
        if (is_array($a)) {
			if (is_string($input))
				$msg = $name . ": \n" . $input;
			else
				$msg = $input;
            foreach ($a as $provider => $v) {
                if (isset($this->providers[$provider]['provider']))
                    $providerName = strtolower($this->providers[$provider]['provider']);
                else
                    $providerName = '';

				if (empty($providerName))
					$providerName = $provider;

				$s = explode(',', $v);
				foreach ($s as $sendTo) {
					$sendTo = trim($sendTo);
					if ($sendTo == 'sendTo')
						$sendTo = $properties['sendTo'];
					elseif ($sendTo !== 'self')
						$sendTo = $this->sendTo[$providerName][$sendTo];

					$properties['sendTo'] = $sendTo;

					$output .= $this->msg($provider, $msg, $properties);
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
		$properties=array();
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
			$msg = (string) $msg;

        $msg = $msg."\n".
			"============================\n".
			"[".$s['class'].$s['type'].$s['function']."()]\n".
            "user = ".$modx->user->username."\n".
            "url = ".MODX_URL_SCHEME.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\n".
            "(line) file = (".$s['line'].") ".$s['file']."\n";
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
            self::modx($msg,4);
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
        $modx->log($logLevel, $msg);
    }
//**************************************************************************************************************************************************

	public static function email($msg, $sendTo, $properties = array())
	{
        $output = '';
		$file = dirname(__FILE__) . 'msgemail.class.php';
		if (file_exists($file)) {
			include_once $file;
			$email = new MsgEmail();
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
    public static function curl($server, array $properties = array(), $proxy = '', $timeout = 3, $isGet = false)
    {
        foreach ($properties as $k => $v)
            $server = str_replace('[[+' . $k . ']]', $v, $server);

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
            Msg::error('Вызов cURL завершился с ошибкой: "' . curl_error($ch) . '". Ответ= ' . $output);
        }
        curl_close($ch); // завершаем сессию

        return $output;
    }
//**************************************************************************************************************************************************
    public static function test($properties = array())
    {
        return 'test'.json_encode($properties);
    }
}

