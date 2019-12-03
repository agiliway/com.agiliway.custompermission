<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Custompermission_Form_Settings extends CRM_Core_Form {

  /**
   * @var array
   */
  private $allTabs = [];

  /**
   * @throws \CRM_Core_Exception
   */
  public function preProcess() {
    $this->allTabs = CRM_Custompermission_Helper::getAllTabs();
  }

  public function buildQuickForm() {
    parent::buildQuickForm();

    foreach ($this->allTabs as $tab) {
      $this->addRadio('permission_tab_' . $tab['id'], $tab['title'], CRM_Core_SelectValues::getPermissionedRelationshipOptions(), ['required' => TRUE]);
    }

    $this->assign('tabElementNames', $this->getTabElementNames());

    $this->addEntityRef('hierarchy_main_organization', ts('Main organization'), [
      'create' => FALSE,
      'api' => [
        'params' => ['contact_type' => 'Organization'],
      ],
    ], TRUE);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getTabElementNames() {
    $elementNames = [];

    foreach ($this->_elements as $element) {
      $label = $element->getLabel();

      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }

    return $elementNames;
  }

  /**
   * @return array
   */
  public function setDefaultValues() {
    $defaults = [];
    $defaults['hierarchy_main_organization'] = Civi::settings()
      ->get('hierarchy_main_organization');
    foreach ($this->allTabs as $tab) {
      $paramName = 'permission_tab_' . $tab['id'];
      $defaults[$paramName] = Civi::settings()->get($paramName) ? Civi::settings()->get($paramName) : 0;
    }

    return $defaults;
  }

  public function postProcess() {
    $params = $this->exportValues();
    foreach ($this->allTabs as $tab) {
      $paramName = 'permission_tab_' . $tab['id'];

      Civi::settings()->set($paramName, $params[$paramName]);
    }
    Civi::settings()->set('hierarchy_main_organization', $params['hierarchy_main_organization']);
  }

}
