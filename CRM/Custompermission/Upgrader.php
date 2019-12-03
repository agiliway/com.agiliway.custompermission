<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Custompermission_Upgrader extends CRM_Custompermission_Upgrader_Base {

  public function install() {
    $this->executeSqlFile('sql/install.sql');

    $this->installMenu();
  }

  public function uninstall() {
    $this->executeSqlFile('sql/uninstall.sql');

    $this->deleteMenu();
  }

  private function installMenu() {
    $value = ['name' => 'Administer'];
    CRM_Core_BAO_Navigation::retrieve($value, $navInfo);

    if (!empty($navInfo)) {
      $this->createMenuItem([
        'label' => ts('Custompermission settings'),
        'name' => 'custompermission-settings',
        'url' => 'civicrm/custompermission/settings'
      ], 'administer CiviCRM', $navInfo['id']);
    }

    $value = ['name' => 'Contacts'];
    CRM_Core_BAO_Navigation::retrieve($value, $navInfo);

    if (!empty($navInfo)) {
      $this->createMenuItem([
        'label' => ts('Organizations hierarchy'),
        'name' => 'custompermission-organizations-hierarchy',
        'url' => 'civicrm/custompermission/organizations-hierarchy'
      ], 'administer CiviCRM', $navInfo['id']);
    }
  }

  private function deleteMenu() {
    $value = ['name' => 'custompermission-settings'];
    CRM_Core_BAO_Navigation::retrieve($value, $navInfo);

    if (!empty($navInfo)) {
      CRM_Core_BAO_Navigation::processDelete($navInfo['id']);
      CRM_Core_BAO_Navigation::resetNavigation();
    }

    $value = ['name' => 'custompermission-organizations-hierarchy'];
    CRM_Core_BAO_Navigation::retrieve($value, $navInfo);

    if (!empty($navInfo)) {
      CRM_Core_BAO_Navigation::processDelete($navInfo['id']);
      CRM_Core_BAO_Navigation::resetNavigation();
    }
  }

  /**
   * @param $item
   * @param $permission
   * @param null $parentId
   *
   * @return int
   */
  private function createMenuItem($item, $permission, $parentId = NULL) {
    $value = ['name' => $item['name']];

    CRM_Core_BAO_Navigation::retrieve($value, $navInfo);

    if (!$navInfo) {
      $navigation = array(
        'permission' => $permission,
        'weight' => 0,
        'is_active' => 1,
        'parent_id' => $parentId,
      );
      $navigation = array_merge($item, $navigation);

      $element = CRM_Core_BAO_Navigation::add($navigation);
      $id = $element->id;

      CRM_Core_BAO_Navigation::resetNavigation();
    } else {
      $id = $navInfo['id'];
    }

    return $id;
  }

}
