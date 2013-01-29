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
	protected static $session;
	protected static $instance;

	protected static $key = '';
	protected static $secret = '';
	protected static $url = 'http://ws.audioscrobbler.com/2.0/';

	/**
	 * Singleton pattern instance method
	 *
	 * @return LastFM
	 */
	public static function instance()
	{
		if (LastFM::$instance)
		{
			return LastFM::$instance;
		}

		return LastFM::$instance = new LastFM;
	}

	/**
	 * Singleton pattern constructor
	 *
	 * @return null
	 */
	protected function __construct()
	{
		if ( ! $this->has_valid_session())
		{
			LastFM::$session = Session::instance()->get('lastfm_session');
		}
	}

	/**
	 * Determines if we have a valid last.fm session for this user
	 *
	 * @return bool
	 */
	public function has_valid_session()
	{
		return isset(LastFM::$session) AND is_object(LastFM::$session);
	}

	/**
	 * Gets the last.fm session object. Returns an object containing:
	 * 	name - the last.fm username which is authenticated
	 * 	key  - the last.fm session key used for authenticated api requests
	 *
	 * @return object
	 */
	public function session()
	{
		return LastFM::$session;
	}

	/**
	 * Sends the request to last.fm to get a authorization token.
	 * 
	 * Will redirect back to $redirect_url with a 'token' $_GET parameter. Use
	 * this to obtain a session token using LastFM::fetch_service_session()
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

		HTTP::redirect($uri);
	}

	/**
	 * Gets a last.fm user session
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
		$session = $this->do_request($request)->session;
		Session::instance()->set('lastfm_session', $session);
	}

	/**
	 * Builds and sends a last.fm API request
	 *
	 * @return string the response converted from json_decode()
	 */
	public function api($method, array $params = array())
	{
		$request = array(
			'api_key' => LastFM::$key,
			'method' => $method,
			'sk' => $this->session()->key
		)+$params;
		$request['api_sig'] = $this->sign($request);

		if (isset(Kohana::config('lastfm')->post[$method]))
			return $this->do_request($request, TRUE);

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
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		}

		$response = curl_exec($ch);
		curl_close($ch);

		if ( ! $response)
		{
			throw new Kohana_Exception(
				'Could not connect to last.fm api!'
			);
		}

		$response = json_decode($response);

		if (isset($response->error))
		{
			throw new Kohana_Exception(
				$response->message,
				array(),
				$response->error
			);
		}

		return $response;
	}

	/**
	 * Performs request string signing per LastFM guidelines
	 * 
	 * @param array $request the request array to sign
	 * 
	 * @return string the md5 signed request hash
	 */
	protected function sign(array $request)
	{
		$string = '';
		foreach ($request as $key => $value)
			$string.=$key.$value;
		return md5($string.LastFM::$secret);
	}
}
