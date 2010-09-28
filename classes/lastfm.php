<?php
/**
 * Last.fm API class
 *
 * @package    Last.fm
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 */
class LastFM
{
	protected static $key = '';
	protected static $secret = '';
	protected static $url = 'http://ws.audioscrobbler.com/2.0/';

	protected static $session = NULL;

	/**
	 * Determines if we have a valid last.fm session for this user
	 *
	 * @return bool
	 */
	public function has_valid_session()
	{
		return self::$session == NULL;
	}

	/**
	 * Gets a last.fm user session and assigns it to the class
	 *
	 * @return null
	 */
	public function fetch_service_session($token)
	{
		$request = array(
			'api_key' => self::$key,
			'method' => 'auth.getSession',
			'token' => $token,
		);
		$request['api_sig'] = $this->sign($request);
		return $this->do_request($request);
	}

	/**
	 * Builds and sends a last.fm API request
	 *
	 * @return string the json response
	 */
	public function api($method, $session, array $params = array())
	{
		$request = array(
			'api_key' => self::$key,
			'method' => $method,
			'session' => $session
		)+$params;
		$request['api_sig'] = $this->sign($request);
		return $this->do_request($request);
	}

	/**
	 * Sends a request to last.fm.
	 * 
	 * @throws Kohana_Exception if connection fails
	 * 
	 * @return string the json response
	 * 
	 */
	protected function do_request(array $request, $post = FALSE)
	{
		$request = http_build_query($request+array('format' => 'json');
		$ch = curl_init($post ? self::$url : self::$url.'?'.$request));

		curl_setopt_array(
			$ch,
			array(
				CURLOPT_HEADER         => FALSE,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_SSL_VERIFYPEER => TRUE,
			)
		);

		if ($post)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	protected function sign(array $request)
	{
		$string = '';
		foreach ($request as $key => $value)
			$string.=$key.$value;
		return md5($string.self::$secret);
	}
}