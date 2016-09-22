<?php

class CRM_Speakcivi_Logic_Activity {


  /**
   * Get activity status id. If status isn't exist, create it.
   *
   * @param string $activityStatus Internal name of status
   *
   * @return int
   * @throws \CiviCRM_API3_Exception
   */
  public static function getStatusId($activityStatus) {
    $params = array(
      'sequential' => 1,
      'option_group_id' => 'activity_status',
      'name' => $activityStatus,
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
   * @param $parentActivityId
   * @param $activity_date_time
   * @param $location
   *
   * @throws \CiviCRM_API3_Exception
   */
  private static function createActivity($contactId, $typeId, $subject = '', $campaignId = 0, $parentActivityId = 0, $activity_date_time = '', $location = '') {
    $params = array(
      'sequential' => 1,
      'activity_type_id' => $typeId,
      'activity_date_time' => date('Y-m-d H:i:s'),
      'status_id' => 'Completed',
      'subject' => $subject,
      'source_contact_id' => $contactId,
    );
    if ($campaignId) {
      $params['campaign_id'] = $campaignId;
    }
    if ($parentActivityId) {
      $params['parent_id'] = $parentActivityId;
    }
    if ($activity_date_time) {
      $params['activity_date_time'] = $activity_date_time;
    }
    if ($location) {
      $params['location'] = $location;
    }
    civicrm_api3('Activity', 'create', $params);
  }


  /**
   * Set unique activity. Method gets activity by given params and creates only if needed.
   * Need more performance but provide database consistency.
   *
   * @param array $params
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function setActivity($params) {
    $result = civicrm_api3('Activity', 'get', $params);
    if ($result['count'] == 0) {
      $result = civicrm_api3('Activity', 'create', $params);
    }
    return $result;
  }


  /**
   * Add Join activity to contact
   *
   * @param $contactId
   * @param $subject
   * @param $campaignId
   * @param $parentActivityId
   */
  public static function join($contactId, $subject = '', $campaignId = 0, $parentActivityId = 0) {
    $activityTypeId = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'activity_type_join');
    self::createActivity($contactId, $activityTypeId, $subject, $campaignId, $parentActivityId);
  }


  /**
   * Add Leave activity to contact
   *
   * @param $contactId
   * @param $subject
   * @param $campaignId
   * @param $parentActivityId
   * @param $activity_date_time
   * @param $location
   */
  public static function leave($contactId, $subject = '', $campaignId = 0, $parentActivityId = 0, $activity_date_time = '', $location = '') {
    $activityTypeId = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'activity_type_leave');
    self::createActivity($contactId, $activityTypeId, $subject, $campaignId, $parentActivityId, $activity_date_time, $location);
  }


  /**
   * Set source fields in custom fields
   *
   * @param $activityId
   * @param $fields
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function setSourceFields($activityId, $fields) {
    $params = array(
      'sequential' => 1,
      'id' => $activityId,
    );
    $fields = (array)$fields;
    if (array_key_exists('source', $fields) && $fields['source']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_activity_source')] = $fields['source'];
    }
    if (array_key_exists('medium', $fields) && $fields['medium']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_activity_medium')] = $fields['medium'];
    }
    if (array_key_exists('campaign', $fields) && $fields['campaign']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_activity_campaign')] = $fields['campaign'];
    }
    if (count($params) > 2) {
      civicrm_api3('Activity', 'create', $params);
    }
  }


  /**
   * Set share fields in custom fields (medium and tracking code)
   *
   * @param $activityId
   * @param $fields
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function setShareFields($activityId, $fields) {
    $params = array(
      'sequential' => 1,
      'id' => $activityId,
    );
    $fields = (array)$fields;
    if (array_key_exists('source', $fields) && $fields['source']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_tracking_codes_source')] = $fields['source'];
    }
    if (array_key_exists('medium', $fields) && $fields['medium']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_tracking_codes_medium')] = $fields['medium'];
    }
    if (array_key_exists('campaign', $fields) && $fields['campaign']) {
      $params[CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'field_tracking_codes_campaign')] = $fields['campaign'];
    }
    if (count($params) > 2) {
      civicrm_api3('Activity', 'create', $params);
    }
  }


  /**
   * Check If activity has own Join activity
   *
   * @param $activityId
   *
   * @return bool
   * @throws \CiviCRM_API3_Exception
   */
  public static function hasJoin($activityId) {
    $activityTypeId = CRM_Core_BAO_Setting::getItem('Speakcivi API Preferences', 'activity_type_join');
    $params = array(
      'sequential' => 1,
      'activity_type_id' => $activityTypeId,
      'parent_id' => $activityId,
    );
    return (bool)civicrm_api3('Activity', 'getcount', $params);
  }
}
