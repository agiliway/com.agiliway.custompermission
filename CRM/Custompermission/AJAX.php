<?php

class CRM_Custompermission_AJAX {

  /**
   *Transmits data to the hierarchy page
   *
   * @throws \CRM_Core_Exception
   */
  public static function getChildContacts() {
    $contactId = CRM_Utils_Request::retrieve('cid', 'Integer');

    $childContacts = CRM_Custompermission_Helper::getFirstLevelChildrenOfContact($contactId);

    CRM_Utils_JSON::output($childContacts);
  }

}
