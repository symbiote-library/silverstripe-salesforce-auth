<?php
/**
 * Displays the Salesforce login form.
 */
class SalesforceLoginForm extends LoginForm {

	public function __construct($controller, $name) {
		$fields = new FieldList(array(
			new HiddenField('AuthenticationMethod', null, 'SalesforceAuthenticator')
		));

		$actions = new FieldList(array(
			new FormAction('dologin', _t('SalesforceAuth.LOGIN_WITH_SALESFORCE', 'Login with Salesforce'))
		));

		parent::__construct($controller, $name, $fields, $actions);
	}

	public function dologin($data, $form) {
		return SalesForceAuthenticator::authenticate($data, $form);
	}

	/**
	 * @return SalesForceAuthenticator
	 */
	public function getAuthenticator() {
		return new SalesForceAuthenticator();
	}

}
