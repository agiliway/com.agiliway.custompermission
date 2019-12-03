<?php

class CRM_Custompermission_Helper {

  /**
   * @var bool
   */
  public static $noUseHook = FALSE;

  /**
   * @var array
   */
  private static $tempTables = [];

  /**
   * @var array
   */
  private static $allTabs = [];

  /**
   * @var array
   */
  public static $chainRelationship = [];

  /**
   * @return int
   * @throws \CRM_Core_Exception
   */
  public static function getCidFromEntryUrl() {
    if ($entryURL = CRM_Utils_Request::retrieve('entryURL', 'String')) {
      $urlQuery = parse_url($entryURL, PHP_URL_QUERY);
      parse_str(CRM_Utils_String::unstupifyUrl($urlQuery), $urlParams);

      if (!empty($urlParams['cid'])) {
        return $urlParams['cid'];
      }
    }

    return 0;
  }

  /**
   * @param int $contactId
   * @param int $permission
   *
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function accessUserToContact($contactId, $permission) {
    if (!$contactId) {
      $contactId = self::getCidFromEntryUrl();

      if (!$contactId) {
        return FALSE;
      }
    }

    $tmpTableName = self::getPermissionedTable(CRM_Core_Session::singleton()->get('userID'));
    $sql = 'SELECT COUNT(*) FROM ' . $tmpTableName . ' WHERE contact_id = ' . $contactId;

    if ($permission == CRM_Contact_BAO_Relationship::VIEW) {
      $sql .= ' AND is_permission IN (' . CRM_Contact_BAO_Relationship::EDIT . ',' . CRM_Contact_BAO_Relationship::VIEW . ')';
    }
    else if ($permission == CRM_Contact_BAO_Relationship::EDIT) {
      $sql .= ' AND is_permission = ' . CRM_Contact_BAO_Relationship::EDIT;
    }
    else {
      return FALSE;
    }

    return CRM_Core_DAO::singleValueQuery($sql) ? TRUE : FALSE;
  }

  /**
   * @param int $contactID
   *
   * @return string
   */
  private static function getPermissionedTable($contactID) {
    $dateKey = date('dhis');

    if (!empty(self::$tempTables[$contactID])) {
      return self::$tempTables[$contactID]['permissioned_contacts'];
    }
    else {
      $tmpTableName = 'my_relationships_' . $contactID . '_' . rand(10000, 100000);
      $sql = '
        CREATE TEMPORARY TABLE ' . $tmpTableName . ' (
          `contact_id` INT(10) NOT NULL,
          `is_permission` TINYINT(4) NULL DEFAULT NULL,
          PRIMARY KEY (`contact_id`)
        )';

      CRM_Core_DAO::executeQuery($sql);

      $tmpTableSecondaryContacts = 'my_secondary_relationships' . $dateKey . rand(10000, 100000);
      $sql = '
        CREATE TEMPORARY TABLE ' . $tmpTableSecondaryContacts . ' (
          `contact_id` INT(10) NOT NULL,
          `is_permission` TINYINT(4) NULL DEFAULT NULL,
          PRIMARY KEY (`contact_id`)
        )';

      CRM_Core_DAO::executeQuery($sql);
    }

    self::$tempTables[$contactID]['permissioned_contacts'] = $tmpTableName;
    self::$tempTables[$contactID]['permissioned_secondary_contacts'] = $tmpTableSecondaryContacts;

    $now = date('Y-m-d');

    self::calculatePermissions($tmpTableName, $contactID, $now);
    self::calculateInheritedPermissions($tmpTableSecondaryContacts, $tmpTableName, $now);

    $sql = '
      REPLACE INTO ' . $tmpTableName . '
      SELECT contact_id, is_permission FROM ' . $tmpTableSecondaryContacts . '
    ';

    CRM_Core_DAO::executeQuery($sql);

    try {
      $secondDegreePerms = civicrm_api3('setting', 'getvalue', ['version' => 3, 'name' => 'secondDegRelPermissions', 'group' => 'core']);
    }
    catch (Exception $e) {
      $secondDegreePerms = 0;
    }

    if ($secondDegreePerms) {
      $continue = 1;

      while ($continue > 0) {
        self::calculateInheritedPermissions($tmpTableSecondaryContacts, $tmpTableName, $now);

        $newPotentialPermissionInheritingContacts = CRM_Core_DAO::singleValueQuery('
           SELECT count(*) FROM ' . $tmpTableSecondaryContacts . ' s
           LEFT JOIN ' . $tmpTableName . ' m ON s.contact_id = m.contact_id
           WHERE m.contact_id IS NULL AND s.contact_type IN ("Organization", "Household")
        ');
        $sql = '
          REPLACE INTO ' . $tmpTableName . '
          SELECT contact_id, is_permission FROM ' . $tmpTableSecondaryContacts . '
        ';

        CRM_Core_DAO::executeQuery($sql);

        $continue = $newPotentialPermissionInheritingContacts;
      }
    }

    return $tmpTableName;
  }

  /**
   * @param string $tmpTableName
   * @param int $contactID
   * @param string $now
   */
  private static function calculatePermissions($tmpTableName, $contactID, $now) {
    $sql = '
      INSERT INTO ' . $tmpTableName . '
      
      SELECT DISTINCT 
        civicrm_relationship.contact_id_a,
        civicrm_relationship_type_setting.is_permission_b_a
      
      FROM civicrm_relationship
      
      LEFT JOIN civicrm_relationship_type ON civicrm_relationship_type.id = civicrm_relationship.relationship_type_id
      LEFT JOIN civicrm_relationship_type_setting ON civicrm_relationship_type_setting.relationship_type_id = civicrm_relationship_type.id
      
      WHERE civicrm_relationship.contact_id_b = ' . $contactID . '
      AND civicrm_relationship.is_active = 1
      AND (civicrm_relationship.start_date IS NULL OR civicrm_relationship.start_date <= "' . $now . '" )
      AND (civicrm_relationship.end_date IS NULL OR civicrm_relationship.end_date >= "' . $now . '")
      AND civicrm_relationship_type_setting.is_permission_b_a != 0
    ';

    CRM_Core_DAO::executeQuery($sql);

    $sql ='
      REPLACE INTO ' . $tmpTableName . '
      
      SELECT 
        civicrm_relationship.contact_id_b,
        civicrm_relationship_type_setting.is_permission_a_b
        
      FROM civicrm_relationship
      
      LEFT JOIN civicrm_relationship_type ON civicrm_relationship_type.id = civicrm_relationship.relationship_type_id
      LEFT JOIN civicrm_relationship_type_setting ON civicrm_relationship_type_setting.relationship_type_id = civicrm_relationship_type.id
      
      WHERE civicrm_relationship.contact_id_a = ' . $contactID . '
      AND civicrm_relationship.is_active = 1
      AND (civicrm_relationship.start_date IS NULL OR civicrm_relationship.start_date <= "' . $now . '" )
      AND (civicrm_relationship.end_date IS NULL OR civicrm_relationship.end_date >= "' . $now . '")
      AND civicrm_relationship_type_setting.is_permission_a_b != 0
    ';

    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * @param string $tmpTableSecondaryContacts
   * @param string $tmpTableName
   * @param string $now
   */
  private static function calculateInheritedPermissions($tmpTableSecondaryContacts, $tmpTableName, $now) {
    $sql = '
      REPLACE INTO ' . $tmpTableSecondaryContacts . '
      
      SELECT DISTINCT 
        contact_id_b,
        rts.is_permission_a_b
      
      FROM ' . $tmpTableName . ' tmp
      
      LEFT JOIN civicrm_relationship r ON tmp.contact_id = r.contact_id_a
      LEFT JOIN civicrm_relationship_type rt ON rt.id = r.relationship_type_id
      LEFT JOIN civicrm_relationship_type_setting rts ON rts.relationship_type_id = rt.id
      INNER JOIN civicrm_contact c ON c.id = r.contact_id_a AND c.contact_type IN ("Household", "Organization")
      INNER JOIN civicrm_contact contact_b ON contact_b.id = r.contact_id_b
      
      WHERE r.is_active = 1
      AND (start_date IS NULL OR start_date <= "' . $now . '" )
      AND (end_date IS NULL OR end_date >= "' . $now . '")
      AND rts.is_permission_a_b != 0
      AND c.is_deleted = 0
    ';

    CRM_Core_DAO::executeQuery($sql);

    $sql = '
      REPLACE INTO ' . $tmpTableSecondaryContacts . '
      
      SELECT 
        contact_id_a,
        rts.is_permission_b_a
      
      FROM ' . $tmpTableName . ' tmp
      
      LEFT JOIN civicrm_relationship r ON tmp.contact_id = r.contact_id_b
      LEFT JOIN civicrm_relationship_type rt ON rt.id = r.relationship_type_id
      LEFT JOIN civicrm_relationship_type_setting rts ON rts.relationship_type_id = rt.id
      INNER JOIN civicrm_contact c ON c.id = r.contact_id_b AND c.contact_type IN ("Household", "Organization")
      INNER JOIN civicrm_contact contact_b ON contact_b.id = r.contact_id_b
      
      WHERE r.is_active = 1
      AND (start_date IS NULL OR start_date <= "' . $now . '" )
      AND (end_date IS NULL OR end_date >= "' . $now . '" )
      AND rts.is_permission_b_a != 0
      AND c.is_deleted = 0
    ';

    CRM_Core_DAO::executeQuery($sql);
  }

  /**
   * @param int $cid
   *
   * @return array
   * @throws \CRM_Core_Exception
   */
  public static function getAllTabs($cid = 0) {
    $contactId = $cid ? $cid : CRM_Core_Session::singleton()->get('userID');

    if (empty(self::$allTabs[$contactId])) {
      $oldRequestCid = !empty($_REQUEST['cid']) ? $_REQUEST['cid'] : NULL;
      $_REQUEST['cid'] = 2;

      self::$noUseHook = TRUE;

      $pageViewSummary = new CRM_Contact_Page_View_Summary();
      $pageViewSummary->setVar('_contactId', $contactId);
      $pageViewSummary->_viewOptions = CRM_Core_BAO_Setting::valueOptions(
        CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'contact_view_options',
        TRUE
      );

      $tabs = $pageViewSummary->getTabs();

      foreach ($tabs as $key => $tab) {
        if ($tab['id'] == 'group') {
          unset($tabs[$key]);

          break;
        }
      }

      $_REQUEST['cid'] = $oldRequestCid;

      self::$noUseHook = FALSE;

      self::$allTabs[$contactId] = $tabs;
    }

    return self::$allTabs[$contactId];
  }

  /**
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function isTab() {
    return
      (CRM_Utils_System::getUrlPath() == 'civicrm/contact/view' ||
      (stripos(CRM_Utils_System::getUrlPath(), 'civicrm/contact/view') === 0 && !CRM_Custompermission_Helper::$noUseHook)) &&
      (!CRM_Utils_Request::retrieve('action', 'String') || CRM_Utils_Request::retrieve('action', 'String') == CRM_Core_Action::BROWSE);
  }

  /**
   * Returned array contains data of first level children of organization
   *
   * @param $contactId
   *
   * @return array
   */
  public static function getFirstLevelChildrenOfContact($contactId) {
    $childrenOfContact = [
      'organizations' => [],
      'contacts' => [],
    ];
    $sql = '
      SELECT
        contact_b.id,
        contact_b.display_name, 
        contact_b.contact_type,
        (
          SELECT COUNT(civicrm_relationship.id)
          FROM civicrm_relationship
          
          JOIN civicrm_contact
          ON civicrm_contact.id = civicrm_relationship.contact_id_b
          
          WHERE civicrm_relationship.contact_id_a = relationship.contact_id_b
          AND civicrm_contact.contact_type = "Organization"
          AND civicrm_relationship.is_active = 1
          AND civicrm_relationship.case_id IS NULL
        ) as amount_child_organizations,
        (
          SELECT COUNT(civicrm_relationship.id)
          FROM civicrm_relationship
          
          JOIN civicrm_contact
          ON civicrm_contact.id = civicrm_relationship.contact_id_a
          
          WHERE civicrm_relationship.contact_id_b = relationship.contact_id_b
          AND civicrm_contact.contact_type = "Individual"
          AND civicrm_relationship.is_active = 1
          AND civicrm_relationship.case_id IS NULL
        ) as amount_child_contacts
        
      FROM civicrm_relationship relationship
                
      JOIN civicrm_contact contact_b
      ON contact_b.id = relationship.contact_id_b
                
      WHERE relationship.contact_id_a = %1
      AND contact_b.contact_type = "Organization"
      AND relationship.is_active = 1
      AND relationship.case_id IS NULL
      
      UNION ALL
      
      SELECT 
        contact_a.id,
        contact_a.display_name, 
        contact_a.contact_type,
        0 as amount_child_organizations,
        0 as amount_child_contacts
        
      FROM civicrm_relationship relationship
                
      JOIN civicrm_contact contact_a
      ON contact_a.id = relationship.contact_id_a
                
      WHERE relationship.contact_id_b = %1
      AND contact_a.contact_type = "Individual"
      AND relationship.is_active = 1
      AND relationship.case_id IS NULL
    ';
    $contacts = CRM_Core_DAO::executeQuery($sql, [ 1 => [ $contactId, 'Integer' ]])->fetchAll();

    foreach ($contacts as $contact) {
      array_push($childrenOfContact[($contact['contact_type'] == 'Organization') ? 'organizations' : 'contacts'], [
        'id' => $contact['id'],
        'display_name' => $contact['display_name'],
        'amount_child_organizations' => $contact['amount_child_organizations'],
        'amount_child_contacts' => $contact['amount_child_contacts'],
      ]);
    }

    return $childrenOfContact;
  }

  /**
   * Returned array hierarchy contains data of all children of contact
   *
   * @param $contactId
   *
   * @return array
   */
  public static function getHierarchyAllChildren($contactId) {
    static $contactsId = [];

    if (in_array($contactId, $contactsId)) {
      return [];
    }

    $contactsId[] = $contactId;

    $children = self::getFirstLevelChildrenOfContact($contactId);

    foreach ($children['organizations'] as &$child) {
      $childrenLevelNext = self::getHierarchyAllChildren($child['id']);
      $child['children'] = $childrenLevelNext['children'];
    }

    $contactsId = [];

    return [
      'id' => $contactId,
      'children' => $children
    ];
  }

  /**
   * Returned array contains data of all children of contacts hierarchy
   *
   * @param $children
   *
   * @return bool
   */
  public static function getAllChildrenByHierarchy($children) {
    if (!is_array($children)) {
      return FALSE;
    }

    foreach (array_merge($children['organizations'], $children['contacts']) as $value) {
      $nextChildren = empty($value['children']) ? NULL : $value['children'];
      self::getAllChildrenByHierarchy($nextChildren);
      self::$chainRelationship[] = $value['id'];
    }

    return FALSE;
  }

  /**
   * Makes a chain relationship of contacts
   *
   * @param $children
   * @param $contactId
   * @param bool $withChildContacts
   *
   * @return bool
   */
  public static function chainRelationship($children, $contactId, $withChildContacts = FALSE) {
    if (!is_array($children)) {
      return FALSE;
    }

    foreach (array_merge($children['organizations'], $children['contacts']) as $value) {
      if ($withChildContacts) {
        self::$chainRelationship[] = [
          'id' => $value['id'],
          'children' => isset($value['children']['contacts']) ? $value['children']['contacts']: [],
        ];
      }
      else {
        self::$chainRelationship[] = $value['id'];
      }

      if ($value['id'] == $contactId) {
        return TRUE;
      }
      else {
        $nextChildren = empty($value['children']) ? NULL : $value['children'];

        if (self::chainRelationship($nextChildren, $contactId, $withChildContacts)) {
          return TRUE;
        }
        else {
          array_pop(self::$chainRelationship);
        }
      }
    }

    return FALSE;
  }

}
