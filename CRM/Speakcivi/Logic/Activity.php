<?php

class CRM_Speakcivi_Logic_Activity {

  /**
   * Get activity type id. If type isn't exist, create it.
   *
   * @param string $activityName
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  private static function getTypeId($activityName) {
    $params = array(
      'sequential' => 1,
      'option_group_id' => 'activity_type',
      'name' => $activityName,
    );
    $result = civicrm_api3('OptionValue', 'get', $params);
    if ($result['count'] == 0) {
      $params['is_active'] = 1;
      $result = civicrm_api3('OptionValue', 'create', $params);
    }
    return (int)$result['values'][0]['value'];
  }


  /**
   * Create activity for contact.
   *
   * @param $contactId
   * @param $typeId
   * @param $subject
   * @param $campaignId
   *
   * @throws \CiviCRM_API3_Exception
   */
  private static function createActivity($contactId, $typeId, $subject = '', $campaignId = 0) {
    $params = array(
      'sequential' => 1,
      'activity_type_id' => $typeId,
      'activity_date_time' => date('Y-m-d H:i:s'),
      'status_id' => 'Completed',
      'subject' => $subject,
      'source_contact_id' => $contactId,
      'api.ActivityContact.create' => array(
        'sequential' => 1,
        'activity_id' => '$value.id',
        'contact_id' => $contactId,
        'record_type_id' => 1,
      ),
    );
    if ($campaignId) {
      $params['campaign_id'] = $campaignId;
    }
    civicrm_api3('Activity', 'create', $params);
  }


  /**
   * Add Join activity to contact
   *
   * @param $contactId
   * @param $subject
   * @param $campaignId
   */
  public static function join($contactId, $subject = '', $campaignId = 0) {
    $activityTypeName = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'activity_type_join');
    $activityTypeId = self::getTypeId($activityTypeName);
    self::createActivity($contactId, $activityTypeId, $subject, $campaignId);
  }


  /**
   * Add Leave activity to contact
   *
   * @param $contactId
   * @param $subject
   * @param $campaignId
   */
  public static function leave($contactId, $subject = '', $campaignId = 0) {
    $activityTypeName = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'activity_type_leave');
    $activityTypeId = self::getTypeId($activityTypeName);
    self::createActivity($contactId, $activityTypeId, $subject, $campaignId);
  }
}