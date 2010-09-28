<?php
/**
 * Last.fm API class
 *
 * @package    Last.fm
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 */
class Kohana_LastFM
{
	public static $session = NULL;

	protected static $key = '';
	protected static $secret = '';
	protected static $url = 'http://ws.audioscrobbler.com/2.0/';

	/**
	 * Dynamically calls a API method using api()
	 *
	 * @return mixed
	 */
	public function __call($method, array $arguments)
	{
		return $this->api($method, $arguments);
	}

	/**
	 * Determines if we have a valid last.fm session for this user
	 *
	 * @return bool
	 */
	public function has_valid_session()
	{
		return isset(self::$session);
	}

	/**
	 * Sends the request to last.fm to get a authorization token
	 * 
	 * @param string $redirect_url optional parameter to send the request back to
	 *
	 * @return null
	 */
	public static function authorize($redirect_url = NULL)
	{
		$uri = 'http://www.last.fm/api/auth/?api_key='.LastFM::$key;
		if ($redirect_url)
			$uri.='&cb='.$redirect_url;

		Request::instance()->redirect($uri);
	}

	/**
	 * Gets a last.fm user session and assigns it to the class
	 *
	 * @return null
	 */
	public function fetch_service_session($token)
	{
		$request = array(
			'api_key' => LastFM::$key,
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
	public function api($method, array $params = array())
	{
		$request = array(
			'api_key' => LastFM::$key,
			'method' => $method,
			'session' => LastFM::$session
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
		$request = http_build_query($request+array('format' => 'json'));
		$ch = curl_init($post ? self::$url : self::$url.'?'.$request);

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
		return md5($string.LastFM::$secret);
	}
}