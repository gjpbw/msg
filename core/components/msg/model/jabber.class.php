<?php
class Jabber
{
	/** @var bool */
	protected $jabberInit;
	/** @var string */
	protected $jabber_server;
	/** @var string */
	protected $jabber_user;
	/** @var string */
	protected $jabber_password;
	/** @var string */
	protected $jabber_SendTo;

//**************************************************************************************************************************************************
	function __construct(array $properties = array())
	{
		$this->jabber_server = $properties['server'];
		$this->jabber_user = $properties['user'];
		$this->jabber_password = $properties['password'];

		if (!empty($this->jabber_server) && !empty($this->jabber_user) && !empty($this->jabber_password))
			include_once MODX_ASSETS_PATH . 'components/XMPPHP/XMPP.php';
		else
			Msg::modx('Неверный вызов конструктора. Недостаточно параметров');
	}

//**************************************************************************************************************************************************
	public function msg($msg = 'test', array $properties = array())
	{
		$output = true;
		if (empty($this->jabber_server))
			Msg::modx('empty jabber_server');
		elseif (empty($this->jabber_user))
				Msg::modx('empty jabber_user');
		elseif (empty($this->jabber_password))
			Msg::modx('empty jabber_password');
		else {
			$sendTo = $properties['sendTo'];
			if (empty($sendTo))
				Msg::modx('empty sendTo');
			else {
				#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
				#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
				$conn = new XMPPHP_XMPP($this->jabber_server, 5222, $this->jabber_user, $this->jabber_password, 'xmpphp', $this->jabber_server, $printlog = false, $loglevel = XMPPHP_Log::LEVEL_INFO);

				if (is_array($sendTo)){
					$sendTos = $sendTo;
					foreach($sendTos as $sendTo) {
						try {
							$conn->connect();
							$conn->processUntil('session_start');
							$conn->presence();
							$conn->message($sendTo, $msg);
							$conn->disconnect();
						} catch (XMPPHP_Exception $e) {
                            $output = false;
                            Msg::modx($e->getMessage());
						}
					}
				}
				else{
					try {
						$conn->connect();
						$conn->processUntil('session_start');
						$conn->presence();
						$conn->message($sendTo, $msg);
						$conn->disconnect();
					} catch (XMPPHP_Exception $e) {
                        $output = false;
                        Msg::modx($e->getMessage());
					}
				}
			}
		}
		return $output;
	}
}
