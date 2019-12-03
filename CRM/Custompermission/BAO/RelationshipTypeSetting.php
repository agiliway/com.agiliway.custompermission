<?php

class CRM_Custompermission_BAO_RelationshipTypeSetting extends CRM_Custompermission_DAO_RelationshipTypeSetting {

  /**
   * Fetch object based on array of properties.
   *
   * @param array $params
   *   (reference ) an assoc array of name/value pairs.
   * @param array $defaults
   *   (reference ) an assoc array to hold the flattened values.
   *
   * @return CRM_Custompermission_BAO_RelationshipTypeSetting|null
   *   object on success, null otherwise
   */
  public static function retrieve(&$params, &$defaults) {
    $object = new CRM_Custompermission_BAO_RelationshipTypeSetting();
    $object->copyValues($params);

    if ($object->find(TRUE)) {
      CRM_Core_DAO::storeValues($object, $defaults);
      $object->free();

      return $object;
    }

    return NULL;
  }

  /**
   * @param $params
   *
   * @return \CRM_Core_DAO
   */
  public static function add(&$params) {
    $entity = new CRM_Custompermission_DAO_RelationshipTypeSetting();
    $entity->relationship_type_id = $params['relationship_type_id'];

    if ($entity->find(TRUE)) {
      $entity->copyValues($params);

      return $entity->update();
    }

    $entity->copyValues($params);

    return $entity->insert();
  }

  /**
   * Delete
   *
   * @param int $id
   */
  public static function del($id) {
    $entity = new CRM_Custompermission_DAO_RelationshipTypeSetting();
    $entity->relationship_type_id = $id;
    $params = [];

    if ($entity->find(TRUE)) {
      CRM_Utils_Hook::pre('delete', self::getEntityName(), $entity->relationship_type_id, $params);
      $entity->delete();
      CRM_Utils_Hook::post('delete', self::getEntityName(), $entity->relationship_type_id, $entity);
    }
  }

}
