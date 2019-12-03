<?php

class CRM_Custompermission_RelationshipsChecker {

  /**
   * Access check based on contact hierarchy
   *
   * @param $contactId
   * @param $permission
   *
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function checkContactAccess($contactId, $permission) {
    $helper = new CRM_Custompermission_Helper;
    $helper::$chainRelationship = [];

    if (!$contactId) {
      $contactId = $helper::getCidFromEntryUrl();

      if (!$contactId) {
        return FALSE;
      }
    }

    $userID = CRM_Core_Session::getLoggedInContactID();
    $isChain = $isReverse = FALSE;

    $userChildren = $helper::getHierarchyAllChildren($userID);
    $helper::chainRelationship($userChildren['children'], $contactId);

    if (!in_array($contactId, $helper::$chainRelationship)) {
      $contactChildren = $helper::getHierarchyAllChildren($contactId);
      $helper::$chainRelationship = [];
      $helper::chainRelationship($contactChildren['children'], $userID);
      $isReverse = TRUE;
      $helper::$chainRelationship = array_reverse($helper::$chainRelationship);
    }
    else {
      $isChain = TRUE;
    }

    if (in_array($userID, $helper::$chainRelationship)) {
      $isChain = TRUE;
    }

    if (!$isChain) {
      return FALSE;
    }

    if ($isReverse) {
      array_push($helper::$chainRelationship, $contactId);
    }
    else {
      array_unshift($helper::$chainRelationship, $userID);
    }

    $i1 = 0;
    $i2 = 1;

    if (count($helper::$chainRelationship) > 2) {
      if (count($helper::$chainRelationship) == 3) {
        try {
          $cType = civicrm_api3('Contact', 'getvalue', [
            'return' => 'contact_type',
            'id' => end($helper::$chainRelationship),
          ]);
        } catch (CiviCRM_API3_Exception $e) {
          return FALSE;
        }

        if ($cType == 'Organization') {
          $i1 = 1;
          $i2 = 2;
        }
      }
      if (count($helper::$chainRelationship) > 3) {
        $i1 = 1;
        $i2 = 2;
      }
    }

    try {
      $relationsOfContact = civicrm_api3('Relationship', 'getsingle', [
        'sequential' => 1,
        'contact_id_a' => [
          'IN' => [
            $helper::$chainRelationship[$i1],
            $helper::$chainRelationship[$i2],
          ],
        ],
        'contact_id_b' => [
          'IN' => [
            $helper::$chainRelationship[$i1],
            $helper::$chainRelationship[$i2],
          ],
        ],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return FALSE;
    }

    $permissionColumn = 'is_permission_a_b';

    if ($relationsOfContact['contact_id_b'] == $helper::$chainRelationship[$i1]) {
      $permissionColumn = 'is_permission_b_a';
    }

    $sql = 'SELECT ' . $permissionColumn . ' FROM civicrm_relationship_type_setting WHERE relationship_type_id = %1';
    $relPermission = CRM_Core_DAO::singleValueQuery($sql, [
      1 => [
        $relationsOfContact['relationship_type_id'],
        'Integer',
      ],
    ]);

    if ($permission == CRM_Contact_BAO_Relationship::VIEW) {
      return $relPermission == 2;
    }
    elseif ($permission == CRM_Contact_BAO_Relationship::EDIT) {
      return $relPermission == 1 || $permission == 2;
    }

    return FALSE;
  }

}
