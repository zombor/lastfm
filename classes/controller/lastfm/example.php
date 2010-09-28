<?php
/**
 * @package    Last.fm
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 */
class Controller_LastFM_Example extends Controller
{
	/**
	 * 
	 *
	 * @return null
	 */
	public function action_index()
	{
		$lastfm = LastFM::instance();

		if ( ! $lastfm->has_valid_session())
			LastFM::authorize('http://lastfm/index.php/lastfm/example/process');

		var_dump($lastfm->session());
		var_dump(
			$lastfm->api(
				'user.getLovedTracks',
				array(
					'user' => $lastfm->session()->name,
				)
			)
		);
	}

	/**
	 * Does stuff
	 *
	 * @return null
	 */
	public function action_process()
	{
		$token = arr::get($_GET, 'token');
		LastFM::instance()->fetch_service_session($token);
		$this->request->redirect('lastfm_example/index');
	}
}