<?php
/**
 * Contains the main Salesforce authentication logic.
 */
class SalesforceAuth {

	const AUTH_URL = 'https://login.salesforce.com/services/oauth2/authorize';
	const CALLBACK_URL = 'https://login.salesforce.com/services/oauth2/token';

	private $clientID;
	private $clientSecret;

	/**
	 * @param string $clientID The Salesforce application client ID.
	 * @param string $clientSecret The Salesforce application client secret.
	 */
	public function __construct($clientID, $clientSecret) {
		$this->clientID = $clientID;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @return string
	 */
	public function getClientID() {
		return $this->clientID;
	}

	/**
	 * @return string
	 */
	public function getClientSecret() {
		return $this->clientSecret;
	}

	/**
	 * Gets the URL to redirect the user to for an authentication operation.
	 *
	 * @return string
	 */
	public function getAuthURL() {
		return Controller::join_links(self::AUTH_URL, '?' . http_build_query(array(
			'response_type' => 'code',
			'client_id' => $this->getClientID(),
			'redirect_uri' => $this->getRedirectURL(),
			'scope' => 'id'
		)));
	}

	/**
	 * Gets the application URL the user is redirected to from Salesforce.
	 *
	 * @return string
	 */
	public function getRedirectURL() {
		return Controller::join_links(
			Director::absoluteBaseURL(), 'salesforce-auth/callback'
		);
	}

	/**
	 * Returns a response to start an authentication response.
	 *
	 * @return SS_HTTPResponse
	 */
	public function authenticate() {
		$response = new SS_HTTPResponse();
		$response->redirect($this->getAuthURL());

		return $response;
	}

	/**
	 * Handles performing a callback to the Salesforce auth server with the
	 * provided authorisation code.
	 *
	 * @param string $code
	 * @return SS_HTTPResponse
	 * @throws SalesforceAuthException On authentication failure.
	 */
	public function callback($code) {
		$callback = new RestfulService(self::CALLBACK_URL, -1);
		$callback = $callback->request('', 'POST', array(
			'code' => $code,
			'grant_type' => 'authorization_code',
			'client_id' => $this->getClientID(),
			'client_secret' => $this->getClientSecret(),
			'redirect_uri' => $this->getRedirectURL()
		));
		$callback = json_decode($callback->getBody());

		if(!$callback || !$callback->id) {
			throw new SalesforceAuthException(
				'An invalid authorisation response was returned'
			);
		}

		$id = new RestfulService($callback->id, -1);
		$id->setQueryString(array('oauth_token' => $callback->access_token));
		$id = json_decode($id->request()->getBody());

		if(!$id || !$id->email) {
			throw new SalesforceAuthException(
				'An invalid identity response was returned'
			);
		}

		$member = Member::get()->filter('Email', $id->email)->first();

		if(!$member) {
			throw new SalesforceAuthException(sprintf(
				'No member was found for the Salesforce email "%s"', $id->email
			));
		}

		$member->logIn();
		$member->extend('onSalesforceIdentify', $id);

		$response = new SS_HTTPResponse();
		$response->redirect('Security/login');

		return $response;
	}

}
