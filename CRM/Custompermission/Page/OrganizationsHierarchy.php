<?php

class CRM_Custompermission_Page_OrganizationsHierarchy extends CRM_Core_Page {

  private $organizationsHierarchy = [];

  /**
   * @throws \CiviCRM_API3_Exception
   */
  public function preProcess() {
    if (Civi::settings()->get('hierarchy_main_organization')) {
      $mainOffice = civicrm_api3('Contact', 'getSingle', [
        'sequential' => 1,
        'id' => Civi::settings()->get('hierarchy_main_organization'),
        'return' => ['display_name', 'contact_type'],
      ]);
      $mainOffice['children'] = CRM_Custompermission_Helper::getFirstLevelChildrenOfContact($mainOffice['id']);
      array_push($this->organizationsHierarchy, $mainOffice);

      $this->assign('organizationsHierarchy', $this->organizationsHierarchy);
    }
  }

  /**
   * the main function that is called when the page loads
   * it decides the which action has to be taken for the page.
   *
   * @return null
   * @throws \CiviCRM_API3_Exception
   */
  public function run() {
    $this->preProcess();
    return parent::run();
  }

}
