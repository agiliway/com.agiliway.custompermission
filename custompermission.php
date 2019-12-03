<?php

require_once 'custompermission.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function custompermission_civicrm_config(&$config) {
  _custompermission_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function custompermission_civicrm_xmlMenu(&$files) {
  _custompermission_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function custompermission_civicrm_install() {
  _custompermission_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function custompermission_civicrm_postInstall() {
  _custompermission_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function custompermission_civicrm_uninstall() {
  _custompermission_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function custompermission_civicrm_enable() {
  _custompermission_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function custompermission_civicrm_disable() {
  _custompermission_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function custompermission_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _custompermission_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function custompermission_civicrm_managed(&$entities) {
  _custompermission_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function custompermission_civicrm_caseTypes(&$caseTypes) {
  _custompermission_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function custompermission_civicrm_angularModules(&$angularModules) {
  _custompermission_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function custompermission_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _custompermission_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Sets fields and a default value for attachment.
 *
 * @param $formName
 * @param $form
 */
function custompermission_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_RelationshipType') {
    $form->addRadio('is_permission_a_b', ts('Permission for Contact A to Contact B'), CRM_Core_SelectValues::getPermissionedRelationshipOptions(), ['required' => TRUE]);
    $form->addRadio('is_permission_b_a', ts('Permission for Contact B to Contact A'), CRM_Core_SelectValues::getPermissionedRelationshipOptions(), ['required' => TRUE]);
    $params = ['relationship_type_id' => $form->getVar('_id')];
    $defaults = [];
    CRM_Custompermission_BAO_RelationshipTypeSetting::retrieve($params, $defaults);
    $form->setDefaults($defaults);
    CRM_Core_Region::instance('page-body')->add([
      'template' => CRM_Custompermission_ExtensionUtil::path() . '/templates/CRM/Custompermission/Form/Field/RelationshipType.tpl',
    ]);
  }

  if ($formName == 'CRM_Contact_Form_Relationship') {
    if ($form->elementExists('is_permission_a_b')) {
      $form->removeElement('is_permission_a_b');
    }
    if ($form->elementExists('is_permission_b_a')) {
      $form->removeElement('is_permission_b_a');
    }
  }
  if ($formName == 'CRM_Note_Form_Note') {
    if (!$form->elementExists('entryURL')) {
      $currentUrl = (CRM_Utils_System::isSSL() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      $form->addElement('hidden', 'entryURL', $currentUrl);
    }
  }

}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param $formName
 * @param $form
 */
function custompermission_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_RelationshipType') {
    $params = $form->exportValues();
    $id = $form->getVar('_id');

    if (!$id) {
      try {
        $relTypeId = civicrm_api3('RelationshipType', 'getvalue', [
          'return' => 'id',
          'label_a_b' => $params['label_a_b'],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        return;
      }

      $id = (int) $relTypeId;
    }

    $params['relationship_type_id'] = $id;
    CRM_Custompermission_BAO_RelationshipTypeSetting::add($params);
  }
}

/**
 * @param array $permission
 * @param bool $granted
 *
 * @throws \CRM_Core_Exception
 */
function custompermission_civicrm_permission_check($permission, &$granted) {
  if (!$granted) {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    if ($cid == CRM_Core_Session::singleton()->get('userID')) {
      if ($permission == 'view all contacts' && CRM_Utils_System::getUrlPath() == 'civicrm/ajax/contactrelationships') {
        $granted = CRM_Core_Permission::check('view_my_relationships');
      }
    }
    else {
      if ($permission == 'view all contacts') {
        $granted = CRM_Custompermission_RelationshipsChecker::checkContactAccess($cid, CRM_Contact_BAO_Relationship::VIEW);
      }
      if ($permission == 'edit all contacts') {
        $granted = CRM_Custompermission_RelationshipsChecker::checkContactAccess($cid, CRM_Contact_BAO_Relationship::EDIT);
      }
      if ($permission == 'access all cases and activities') {
        if (CRM_Custompermission_Helper::accessUserToContact($cid, CRM_Contact_BAO_Relationship::VIEW)) {
          $granted = TRUE;
        }
      }
    }
    if ($permission == 'view all contacts' && CRM_Core_Permission::check('view all contacts in domain')) {
      $granted = TRUE;
    }
    elseif ($permission == 'edit all contacts' && CRM_Core_Permission::check('edit all contacts in domain')) {
      $granted = TRUE;
    }
  }
}

/**
 * Add new type of permission
 */
function custompermission_civicrm_permission(&$permissions) {
  $prefix = ts('CiviCRM') . ': ';
  $permissions += [
    'view_my_relationships' => $prefix . ts('View my relationships'),
  ];
}

/**
 * Implements hook_civicrm_aclWhereClause
 *
 * @param $type
 * @param $tables
 * @param $whereTables
 * @param $contactID
 * @param $where
 */
function custompermission_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  if (!$contactID) {
    return;
  }

  $urlPath = CRM_Utils_System::getUrlPath();

  if (!($urlPath == 'civicrm/contact/search' || $urlPath == 'civicrm/contact/search/advanced')) {
    return;
  }

  $hierarchyMainOrg = Civi::settings()->get('hierarchy_main_organization');

  if (!$hierarchyMainOrg) {
    return;
  }

  $userID = CRM_Core_Session::getLoggedInContactID();

  $helper = new CRM_Custompermission_Helper;
  $helper::$chainRelationship = [];

  $includeContacts = [0];

  $mainOrgChildren = $helper::getHierarchyAllChildren($hierarchyMainOrg);
  $helper::chainRelationship($mainOrgChildren['children'], $userID, TRUE);

  if (empty($helper::$chainRelationship)) {
    return;
  }

  foreach ($helper::$chainRelationship as $contact) {
    $includeContacts[] = $contact['id'];
    foreach ($contact['children'] as $child) {
      $includeContacts[] = $child['id'];
    }
  }

  $userChildren = $helper::getHierarchyAllChildren($userID);
  $helper::$chainRelationship = [];
  $helper::getAllChildrenByHierarchy($userChildren['children']);

  $includeContacts = array_merge($includeContacts, $helper::$chainRelationship);
  $includeContacts[] = $hierarchyMainOrg;

  foreach ($mainOrgChildren['children']['contacts'] as $contact) {
    $includeContacts[] = $contact['id'];
  }

  $includeContacts = array_unique($includeContacts);

  $where .= 'contact_a.id in (' . implode(',', $includeContacts) . ')';
}
