<?php
/**
 * A member authenticator which uses the Salesforce OAuth and identity service.
 */
class SalesforceAuthenticator extends Authenticator {

	public static function get_name() {
		return _t('SalesforceAuth.SALESFORCE', 'Salesforce');
	}

	public static function authenticate($data, Form $form = null) {
		return Injector::inst()->get('SalesforceAuth')->authenticate(
			$data['BackURL'], !empty($data['Remember'])
		);
	}

	public static function get_login_form(Controller $controller) {
		return new SalesForceLoginForm($controller, 'LoginForm');
	}

}
