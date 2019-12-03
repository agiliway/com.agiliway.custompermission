<?php

class CRM_Custompermission_DAO_RelationshipTypeSetting extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civicrm_relationship_type_setting';

  /**
   * Static entity name.
   *
   * @var string
   */
  static $entityName = 'RelationshipTypeSetting';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log
   * table.
   *
   * @var boolean
   */
  static $_log = TRUE;

  /**
   * @var int
   */
  public $relationship_type_id;

  /**
   * @var int
   */
  public $is_permission_a_b;

  /**
   * @var int
   */
  public $is_permission_b_a;

  /**
   * Returns the names of this table
   *
   * @return string
   */
  static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns entity name
   *
   * @return string
   */
  static function getEntityName() {
    return self::$entityName;
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'relationship_type_id' => [
          'name' => 'relationship_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Relationship type id'),
          'description' => 'Relationship type id',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.relationship_type_id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Custompermission_DAO_RelationshipTypeSetting',
          'FKClassName' => 'CRM_Contact_DAO_RelationshipType',
        ],
        'is_permission_a_b' => [
          'name' => 'is_permission_a_b',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Is permission a to b'),
          'description' => 'Is permission a to b',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.is_permission_a_b',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Custompermission_DAO_RelationshipTypeSetting',
        ],
        'is_permission_b_a' => [
          'name' => 'is_permission_b_a',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Is permission b to a'),
          'description' => 'Is permission b to a',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.is_permission_b_a',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Custompermission_DAO_RelationshipTypeSetting',
          'pseudoconstant' => [
            'optionGroupName' => 'document_categories',
          ],
        ],
      ];

      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }

    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, self::getTableName(), $prefix, []);
    return $r;
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in
   * fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }

    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Tells DB_DataObject which keys use autoincrement.
   * 'id' is autoincrementing by default.
   *
   *
   * @return array
   */
  public function sequenceKey() {
    return [FALSE];
  }

  function keys() {
    return ['relationship_type_id'];
  }

}
