<?php

class CRM_Speakcivi_Logic_Campaign {


	public $fieldTemplateId = '';

	public $fieldLanguage = '';

	public $fieldSenderMail = '';

	public $customFields = array();

	function __construct($campaignId = 0) {
		$this->fieldTemplateId = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_template_id');
		$this->fieldLanguage = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_language');
		$this->fieldSenderMail = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_sender_mail');
		if ($campaignId > 0) {
			$this->customFields = $this->getCustomFields($campaignId);
		}
	}


	/**
	 * Get custom fields for campaign Id.
	 * Warning! Switch on permission "CiviCRM: access all custom data" for "ANONYMOUS USER"
	 * @param $campaignId
	 *
	 * @return array
	 * @throws CiviCRM_API3_Exception
	 */
	public function getCustomFields($campaignId) {
		$params = array(
			'sequential' => 1,
			'return' => "{$this->fieldTemplateId},{$this->fieldLanguage},{$this->fieldSenderMail}",
			'id' => $campaignId,
		);
		$result = civicrm_api3('Campaign', 'get', $params);
		if ($result['count'] == 1) {
			return $result['values'][0];
		} else {
			return array();
		}
	}


	/**
	 * Get message template id from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getTemplateId() {
		if (is_array($this->customFields) && array_key_exists($this->fieldTemplateId, $this->customFields)) {
			return (int)$this->customFields[$this->fieldTemplateId];
		}
		return 0;
	}


	/**
	 * Get language from $customFields array generated by getCustomFields() method
	 *
	 * @return string
	 */
	public function getLanguage() {
		if (is_array($this->customFields) && array_key_exists($this->fieldLanguage, $this->customFields)) {
			return $this->customFields[$this->fieldLanguage];
		}
		return '';
	}


	/**
	 * Get language from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getSenderMail() {
		if (is_array($this->customFields) && array_key_exists($this->fieldSenderMail, $this->customFields)) {
			return $this->customFields[$this->fieldSenderMail];
		}
		return '';
	}
}
