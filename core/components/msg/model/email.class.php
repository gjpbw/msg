<?php
namespace msg;
class Email
{
//**************************************************************************************************************************************************
	private function run($msg, $sendTo)
	{
		global $modx;
		$output = '';

		if ($sendTo == 'self'){
			if (!empty($modx->user->id))
				$sendTo = $modx->user->Profile->email;
			else
				$sendTo = '';
		}

		if (!empty($sendTo)) {

			$modx->getService('mail', 'mail.modPHPMailer');
			$modx->mail->set(\modMail::MAIL_FROM, $modx->getOption('emailsender'));
			$modx->mail->set(\modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
			$modx->mail->set(\modMail::MAIL_SENDER, $modx->getOption('emailsender'));

			if (is_array($msg)) {
				$modx->mail->set(\modMail::MAIL_SUBJECT, $msg['subject']);
				$modx->mail->set(\modMail::MAIL_BODY, $msg['body']);
			} else {
				$modx->mail->set(\modMail::MAIL_SUBJECT, $msg);
				$modx->mail->set(\modMail::MAIL_BODY, $msg);
			}

			$modx->mail->address('to', $sendTo, $sendTo);
			$modx->mail->address('reply-to', $modx->getOption('emailsender'));
			$output = $modx->mail->send();
			$modx->mail->reset();
		}
		return $output;
	}
//**************************************************************************************************************************************************
	public function msg($msg = 'test', array $properties = array())
	{
		$output = '';
		$sendTo = $properties['sendTo'];
		if (empty($sendTo))
            \Msg::modx('empty sendTo');
		else {
			if (is_array($sendTo)){
				$sendTos = $sendTo;
				foreach($sendTos as $sendTo)
					$output .= $this->run($msg, $sendTo);
			}
			else
				$output = $this->run($msg, $sendTo);
		}
		return $output;
	}
}