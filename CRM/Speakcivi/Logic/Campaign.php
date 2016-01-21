<?php

class CRM_Speakcivi_Logic_Campaign {

	public $defaultLanguage = '';

	public $defaultCampaignTypeId = 0;

	public $defaultTemplateId = 0;

	public $fieldTemplateId = '';

	public $fieldLanguage = '';

	public $fieldSenderMail = '';

	public $fieldUrlCampaign = '';

	public $fieldUtmCampaign = '';

	public $fieldTwitterShareText = '';

	public $fieldSubjectNew = '';

	public $fieldSubjectCurrent = '';

	public $fieldMessageNew = '';

	public $fieldMessageCurrent = '';

	public $from = '';

	public $urlSpeakout = '';

	public $customFields = array();

	public $countryLangMapping = array();

	function __construct($campaignId = 0) {
		$this->fieldTemplateId = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_template_id');
		$this->fieldLanguage = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_language');
		$this->fieldSenderMail = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_sender_mail');
		$this->fieldUrlCampaign = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_url_campaign');
		$this->fieldUtmCampaign = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_utm_campaign');
		$this->fieldTwitterShareText = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_twitter_share_text');
		$this->fieldSubjectNew = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_subject_new');
		$this->fieldSubjectCurrent = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_subject_current');
		$this->fieldMessageNew = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_message_new');
		$this->fieldMessageCurrent = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_message_current');
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
		$fields = array(
			$this->fieldTemplateId,
			$this->fieldLanguage,
			$this->fieldSenderMail,
			$this->fieldUrlCampaign,
			$this->fieldUtmCampaign,
			$this->fieldTwitterShareText,
			$this->fieldSubjectNew,
			$this->fieldSubjectCurrent,
			$this->fieldMessageNew,
			$this->fieldMessageCurrent,
		);
		$params = array(
			'sequential' => 1,
			'return' => implode(",", $fields),
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


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getUrlCampaign() {
		if (is_array($this->customFields) && array_key_exists($this->fieldUrlCampaign, $this->customFields)) {
			return $this->customFields[$this->fieldUrlCampaign];
		}
		return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getUtmCampaign() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldUtmCampaign, $this->customFields)) {
	    return $this->customFields[$this->fieldUtmCampaign];
	  }
	  return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getTwitterShareText() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldTwitterShareText, $this->customFields)) {
	    return $this->customFields[$this->fieldTwitterShareText];
	  }
	  return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getSubjectNew() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldSubjectNew, $this->customFields)) {
	    return $this->customFields[$this->fieldSubjectNew];
	  }
	  return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getSubjectCurrent() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldSubjectCurrent, $this->customFields)) {
	    return $this->customFields[$this->fieldSubjectCurrent];
	  }
	  return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getMessageNew() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldMessageNew, $this->customFields)) {
	    return $this->customFields[$this->fieldMessageNew];
	  }
	  return '';
	}


	/**
	 * Get Url of campaign from $customFields array generated by getCustomFields() method
	 *
	 * @return int
	 */
	public function getMessageCurrent() {
	  if (is_array($this->customFields) && array_key_exists($this->fieldMessageCurrent, $this->customFields)) {
	    return $this->customFields[$this->fieldMessageCurrent];
	  }
	  return '';
	}


	/**
	 * Get campaign by external identifier.
	 *
	 * @param $externalIdentifier
	 *
	 * @return array
	 * @throws CiviCRM_API3_Exception
	 */
	public function getCampaign($externalIdentifier) {
		if ($externalIdentifier > 0) {
			$params = array(
				'sequential' => 1,
				'external_identifier' => (int)$externalIdentifier,
			);
			$result = civicrm_api3('Campaign', 'get', $params);
			if ($result['count'] == 1) {
				return $result['values'][0];
			}
		}
		return array();
	}


	/**
	 * Setting up new campaign in CiviCRM if this is necessary.
	 *
	 * @param $externalIdentifier
	 * @param $campaign
	 *
	 * @return array
	 * @throws CiviCRM_API3_Exception
	 */
	public function setCampaign($externalIdentifier, $campaign) {
		if (!$this->isValidCampaign($campaign)) {
			if ($externalIdentifier > 0) {
				$this->urlSpeakout = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'url_speakout');
				$externalCampaign = (object)json_decode(@file_get_contents("https://".$this->urlSpeakout."/{$externalIdentifier}.json"));
				if (is_object($externalCampaign) &&
					property_exists($externalCampaign, 'name') && $externalCampaign->name != '' &&
					property_exists($externalCampaign, 'id') && $externalCampaign->id > 0
				) {
					$this->defaultCampaignTypeId = CRM_Core_OptionGroup::getValue('campaign_type', 'Petitions', 'name', 'String', 'value');
					$this->from = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'from');
					$locale = $this->determineLanguage($externalCampaign->internal_name);
					$utmCampaign = ($externalCampaign->slug != '' ? $externalCampaign->slug : 'speakout_'.$externalCampaign->id);
					$params = array(
						'sequential' => 1,
						'title' => $externalCampaign->name,
						'external_identifier' => $externalCampaign->id,
						'campaign_type_id' => $this->defaultCampaignTypeId,
						'start_date' => date('Y-m-d H:i:s'),
						$this->fieldLanguage => $this->determineLanguage($externalCampaign->internal_name),
						$this->fieldSenderMail => $this->from,
						$this->fieldUrlCampaign => "https://".$this->urlSpeakout."/".$utmCampaign,
						$this->fieldUtmCampaign => $utmCampaign,
						$this->fieldTwitterShareText => $externalCampaign->twitter_share_text,
						$this->fieldSubjectNew => CRM_Speakcivi_Tools_Dictionary::getSubjectConfirm($locale),
						$this->fieldSubjectCurrent => CRM_Speakcivi_Tools_Dictionary::getSubjectImpact($locale),
					);
					$result = civicrm_api3('Campaign', 'create', $params);
					if ($result['count'] == 1) {
						$this->setCustomFieldBySQL($result['id'], $this->fieldMessageNew, CRM_Speakcivi_Tools_Dictionary::getMessageNew($locale));
						$this->setCustomFieldBySQL($result['id'], $this->fieldMessageCurrent, CRM_Speakcivi_Tools_Dictionary::getMessageCurrent($locale));
						return $result['values'][0];
					}
				}
			}
			return array();
		} else {
			return $campaign;
		}
	}


	/**
	 * Set new value of custom field
	 * @param int $campaignId
	 * @param string $customField For example $this->fieldMessageNew
	 * @param mixed $value
	 *
	 * @throws \CiviCRM_API3_Exception
	 */
	public function setCustomField($campaignId, $customField, $value) {
		$params = array(
			'id' => $campaignId,
			'sequential' => 1,
			$customField => $value,
		);
		civicrm_api3('Campaign', 'create', $params);
	}


	/**
	 * Set new value of custom field by using SQL query
	 * @param int $campaignId
	 * @param string $customField For example $this->fieldMessageNew
	 * @param mixed $value
	 *
	 * @throws \CiviCRM_API3_Exception
	 */
	public function setCustomFieldBySQL($campaignId, $customField, $value) {
		$query = "SELECT cg.table_name, cf.column_name
							FROM civicrm_custom_group cg
							JOIN civicrm_custom_field cf ON cg.id = cf.custom_group_id
							WHERE cg.extends = 'Campaign' AND CONCAT('custom_', cf.id) = %1";
		$params = array(
			1 => array($customField, 'String'),
		);
		$results = CRM_Core_DAO::executeQuery($query, $params);
		if ($results->fetch()) {
			if ($results->table_name && $results->column_name) {
				$query = "UPDATE $results->table_name
									SET $results->column_name = '".addslashes($value)."'
									WHERE entity_id = %1";
				$params = array(
					1 => array($campaignId, 'Integer'),
				);
				CRM_Core_DAO::executeQuery($query, $params);
			}
		}
	}


	/**
	 * Determine language based on campaign name which have to include country on the end, ex. *_EN.
	 *
	 * @param $campaignName
	 *
	 * @return string
	 */
	public function determineLanguage($campaignName) {
		$re = "/(.*)[_\\- ]([a-zA-Z]{2})$/";
		if (preg_match($re, $campaignName, $matches)) {
			$country = strtoupper($matches[2]);
			$this->countryLangMapping = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'country_lang_mapping');
			if (array_key_exists($country, $this->countryLangMapping)) {
				return $this->countryLangMapping[$country];
			}
		}
		$this->defaultLanguage = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'default_language');
		return $this->defaultLanguage;
	}


	/**
	 * Determine whether $campaign table has a valid structure.
	 *
	 * @param $campaign
	 *
	 * @return bool
	 */
	public function isValidCampaign($campaign) {
		if (
			is_array($campaign) &&
			array_key_exists('id', $campaign) &&
			$campaign['id'] > 0
		) {
			return true;
		}
		return false;
	}
}
