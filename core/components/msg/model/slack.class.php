<?php
namespace msg;
class Slack
{
	private function run($msg, $chat_id)
	{
		$payload = '{"text": "'.$msg.'"}';
		$url = "https://hooks.slack.com/services/" . $chat_id;
		$ch = curl_init(); // инициализируем сессию curl
		curl_setopt($ch, CURLOPT_URL, $url); // указываем URL, куда отправлять POST-запрос
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
		curl_setopt($ch, CURLOPT_TIMEOUT, 3); // таймаут соединения
		curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'payload=' . $payload); // добавляем данные POST-запроса
		$output = curl_exec($ch); // выполняем запрос
		curl_close($ch); // завершаем сессию

		if (($output) !=='ok') {
			\Msg::error('неверный ответ на запрос. ответ= ' . $output);
			$output = '';
		}
		return $output;
	}
//**************************************************************************************************************************************************
	public function msg($msg = 'test', array $properties = array())
	{
		$output = '';
			$chat_id = $properties['sendTo'];
			if (empty($chat_id))
				\Msg::modx('empty sendTo (chat_id)');
			else {
				if (is_array($chat_id)){
					$chat_ids = $chat_id;
					foreach($chat_ids as $chat_id)
						$output =  $this->run($msg, $chat_id);
				}
				else
					$output =  $this->run($msg, $chat_id);
			}
		return $output;
	}
}