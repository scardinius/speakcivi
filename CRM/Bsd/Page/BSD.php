<?php

require_once 'CRM/Core/Page.php';

class CRM_Bsd_Page_BSD extends CRM_Core_Page {

  // TODO Lookup
  private $groupId = 42;

  private $campaign = array();

  private $campaignId = 0;

  function run() {

    $param = json_decode(file_get_contents('php://input'));

    header('HTTP/1.1 503 Men at work');

    if (!$param) {
      die ("missing POST PARAM");
    }

    $this->campaign = $this->getCampaign($param->external_id);
    if ($this->isValidCampaign($this->campaign)) {
      $this->campaignId = $this->campaign['id'];
    } else {
      return;
    }

    switch ($param->action_type) {
      case 'petition':
        $this->petition($param);
        break;

      case 'share':
        $this->share($param);
        break;

      default:
        CRM_Core_Error::debug_var('BSD API, Unsupported Action Type', $param->action_type, false, true);
    }

  }


  public function petition($param) {

    $contact = $this->createContact($param);
    $activity = $this->createActivity($param, $contact['id'], 'Petition', 'Scheduled');

    if ($this->checkIfConfirm($param->external_id)) {
      $h = $param->cons_hash;
      $this->sendConfirm($param, $contact, $h->emails[0]->email);
    }

  }


  public function share($param) {

    $contact = $this->createContact($param);
    $activity = $this->createActivity($param, $contact['id'], 'share', 'Completed');

  }


  /**
   * Create or update contact
   *
   * @param $param
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function createContact($param) {
    $h = $param->cons_hash;

    $apiAddressGet = 'api.Address.get';
    $apiAddressCreate = 'api.Address.create';
    $apiGroupContactGet = 'api.GroupContact.get';
    $apiGroupContactCreate = 'api.GroupContact.create';

    $contact = array(
      'sequential' => 1,
      'contact_type' => 'Individual',
      'first_name' => $h->firstname,
      'last_name' => $h->lastname,
      'email' => $h->emails[0]->email,
      $apiAddressGet => array(
        'id' => '$value.address_id',
        'contact_id' => '$value.id',
      ),
      $apiGroupContactGet => array(
        'contact_id' => '$value.id',
        'status' => 'Added',
      ),
    );

    $result = civicrm_api3('Contact', 'get', $contact);

    unset($contact[$apiAddressGet]);
    unset($contact[$apiGroupContactGet]);
    $contact[$apiAddressCreate] = array(
      'postal_code' => $h->addresses[0]->zip,
      'is_primary' => 1,
    );
    if ($result['count'] == 1) {
      $contact['id'] = $result['values'][0]['id'];
      if ($result['values'][0][$apiAddressGet]['count'] == 1) {
        $contact[$apiAddressCreate]['id'] = $result['values'][0]['address_id'];
      } else {
        $contact[$apiAddressCreate]['location_type_id'] = 1;
      }
      if ($result['values'][0][$apiGroupContactGet]['count'] == 0) {
        $contact[$apiGroupContactCreate] = array(
          'group_id' => $this->groupId,
          'contact_id' => '$value.id',
          'status' => 'Pending',
        );
      }
    } else {
      $contact['source'] = 'speakout ' . $param->action_type . ' ' . $param->external_id;
      $contact[$apiAddressCreate]['location_type_id'] = 1;
      $contact[$apiGroupContactCreate] = array(
        'group_id' => $this->groupId,
        'contact_id' => '$value.id',
        'status' => 'Pending',
      );
    }

    CRM_Core_Error::debug_var('$createContact', $contact, false, true);
    return civicrm_api3('Contact', 'create', $contact);

  }


  /**
   * Create new activity for contact.
   *
   * @param $param
   * @param $contact_id
   * @param string $activity_type
   * @param string $activity_status
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function createActivity($param, $contact_id, $activity_type = 'Petition', $activity_status = 'Scheduled') {
    $activity_type_id = CRM_Core_OptionGroup::getValue('activity_type', $activity_type, 'name', 'String', 'value');
    $activity_status_id_scheduled = CRM_Core_OptionGroup::getValue('activity_status', $activity_status, 'name', 'String', 'value');
    $params = array(
      'source_contact_id' => $contact_id,
      'source_record_id' => $param->external_id,
      'campaign_id' => $this->campaignId,
      'activity_type_id' => $activity_type_id,
      'activity_date_time' => $param->create_dt,
      'subject' => $param->action_name,
      'location' => $param->action_technical_type,
      'status_id' => $activity_status_id_scheduled,
    );
    CRM_Core_Error::debug_var('$paramsCreateActivity', $params, false, true);
    return civicrm_api3('Activity', 'create', $params);
  }


  /**
   * Get campaign by external identifier.
   *
   * @param $external_identifier
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function getCampaign($external_identifier) {
    if ($external_identifier > 0) {
      $params = array(
        'sequential' => 1,
        'external_identifier' => (int)$external_identifier,
      );
      $result = civicrm_api3('Campaign', 'get', $params);
      if ($result['count'] == 1) {
        return $result['values'][0];
      }
    }
    return array();
  }
  

  /**
   * Determine whether $campaign table has a valid structure.
   *
   * @param $campaign
   *
   * @return bool
   */
  private function isValidCampaign($campaign) {
    if (
      is_array($campaign) &&
      array_key_exists('id', $campaign) &&
      $campaign['id'] > 0
    ) {
      return true;
    }
    return false;
  }


  /**
   * Check whether this external campaing (SpeakOut ID Campaign) is marked as unsupported (ex. testing campaign).
   *
   * @param $external_id
   *
   * @return bool
   */
  private function checkIfConfirm($external_id) {
    $notconfirm_external_id = array(
      9,
    );
    return !in_array($external_id, $notconfirm_external_id);
  }


  /**
   * Send confirmation mail to contact.
   *
   * @param $param
   * @param $contact_result
   * @param $email
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function sendConfirm($param, $contact_result, $email) {
    // todo ???
    if (!$contact_result['is_error']) {
      $tplid = 69;
    }
    // todo ???
    if ($param->external_id == 8) {
      $tplid = 70;
    }
    $params = array(
      'sequential' => 1,
      'messageTemplateID' => $tplid, // todo retrieve template id from customField (how to do it?)
      'toEmail' => $email,
      'contact_id' => $contact_result['id']
    );
    CRM_Core_Error::debug_var('$paramsSpeakoutSendConfirm', $params, false, true);
    return civicrm_api3("Speakout", "sendconfirm", $params);
  }

}
