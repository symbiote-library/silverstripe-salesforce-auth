<?php
/**
 * Saves Salesforce identity data to the Member object.
 */
class SalesforceAuthMemberExtension extends DataExtension {

	private static $db = array(
		'SalesforceUserID' => 'Varchar',
		'SalesforceOrganisationID' => 'Varchar',
		'SalesforceUsername' => 'Varchar'
	);

	public function onSalesforceIdentify($identity) {
		if($this->owner->SalesforceUserID == $identity->user_id) {
			return;
		}

		$this->owner->update(array(
			'SalesforceUserID' => $identity->user_id,
			'SalesforceOrganisationID' => $identity->organization_id,
			'SalesforceUsername' => $identity->username
		));
		$this->owner->write();
	}

}
