<?php
/**
 * Handles post-authentication redirects from Salesforce.
 */
class SalesforceAuthController extends Controller {

	private static $allowed_actions = array(
		'callback'
	);

	private static $dependencies = array(
		'salesforce' => '%$SalesforceAuth',
	);

	/**
	 * @var SalesforceAuth
	 */
	public $salesforce;

	public function callback() {
		$code = $this->request->getVar('code');
		$state = $this->request->getVar('state');

		try {
			$this->salesforce->callback($code, $state);
		} catch(SalesforceAuthException $e) {
			Session::set('FormInfo.SalesforceLoginForm_LoginForm.formError', array(
				'message' => $e->getMessage(),
				'type' => 'error'
			));

			return $this->redirect('Security/login#SalesforceLoginForm_LoginForm_tab');
		}

		return $this->redirect('/admin');
	}

}
