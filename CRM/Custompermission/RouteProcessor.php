<?php

class CRM_Custompermission_RouteProcessor {

  /**
   * @param int $action
   *
   * @return array
   */
  private static function getAllowedActions($action) {
    if ($action == CRM_Contact_BAO_Relationship::VIEW) {
      return [CRM_Contact_BAO_Relationship::VIEW, CRM_Contact_BAO_Relationship::EDIT];
    }
    else if ($action == CRM_Contact_BAO_Relationship::EDIT) {
      return [CRM_Contact_BAO_Relationship::EDIT];
    }
    else {
      return [];
    }
  }

  /**
   * @param int $action
   *
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function accessContactTab($action = CRM_Contact_BAO_Relationship::VIEW) {
    $allowedActions = self::getAllowedActions($action);

    if (empty($allowedActions)) {
      return FALSE;
    }

    if (CRM_Utils_System::getUrlPath() == 'civicrm/contact/view') {
      $currentTab = 'summary';
    }
    else {
      $allTabs = CRM_Custompermission_Helper::getAllTabs(CRM_Utils_Request::retrieve('cid', 'Positive'));

      foreach ($allTabs as $tab) {
        if ($tab['id'] != 'summary') {
          $parseUrl = parse_url($tab['url']);

          if (trim($parseUrl['path'], '/') == CRM_Utils_System::getUrlPath()) {
            $currentTab = $tab['id'];
          }
        }
      }
    }

    if (CRM_Utils_Request::retrieve('snippet', 'String') && CRM_Contact_BAO_Relationship::VIEW) {
      $accessUserToContact = TRUE;
    }
    else {
      $accessUserToContact = CRM_Custompermission_Helper::accessUserToContact(CRM_Utils_Request::retrieve('cid', 'Positive'), $action);
    }

    if (
      !empty($currentTab) && in_array(Civi::settings()->get('permission_tab_' . $currentTab), $allowedActions) &&
      $accessUserToContact
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param string $entity
   * @param int $action
   *
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function accessEntity($entity, $action = CRM_Contact_BAO_Relationship::VIEW) {
    $allowedActions = self::getAllowedActions($action);

    if (empty($allowedActions)) {
      return FALSE;
    }

    if (in_array(Civi::settings()->get('permission_tab_' . $entity), $allowedActions)) {
      if (CRM_Utils_System::getUrlPath() != 'civicrm/ajax/rest') {
        $cid = CRM_Utils_Request::retrieve('cid', 'Positive') ? CRM_Utils_Request::retrieve('cid', 'Positive') : CRM_Utils_Request::retrieve('contact_id', 'Positive');
      }
      else {
        $params = CRM_Utils_REST::buildParamList();

        $cid = $params['entity_id'];
      }

      if (!$cid) {
        $cid = CRM_Custompermission_Helper::getCidFromEntryUrl();
      }

      if (!empty($cid) && CRM_Custompermission_Helper::accessUserToContact($cid, $action)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function accessViewAllContacts() {
    $granted = FALSE;
    $routes = [
      'civicrm/ajax/contactactivity' => ['tab' => 'activity'],
      'civicrm/ajax/contactrelationships' => ['tab' => 'rel'],
      'civicrm/activity' => ['tab' => 'activity', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/activity/view' => ['tab' => 'activity', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/rel' => ['tab' => 'rel', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/note' => ['tab' => 'note', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/pledge' => ['tab' => 'pledge', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/participant' => ['tab' => 'participant', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/membership' => ['tab' => 'member', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/contribution' => ['tab' => 'contribute', 'actions' => [CRM_Core_Action::VIEW]]
    ];

    if (CRM_Custompermission_Helper::isTab()) {
      $granted = CRM_Custompermission_RouteProcessor::accessContactTab();
    }
    else {
      foreach ($routes as $route => $params) {
        if (CRM_Utils_System::getUrlPath() == $route) {
          if (!empty($params['actions'] && !in_array(CRM_Utils_Request::retrieve('action', 'String'), $params['actions']))) {
            $granted = FALSE;
          }
          else if (CRM_Custompermission_RouteProcessor::accessEntity($params['tab'])) {
            $granted = TRUE;
          }

          break;
        }
      }
    }

    return $granted;
  }

  /**
   * @return bool
   * @throws \CRM_Core_Exception
   */
  public static function accessEditAllContacts() {
    $granted = FALSE;
    $routes = [
      'civicrm/ajax/contactactivity' => ['tab' => 'activity'],
      'civicrm/activity/add' => ['tab' => 'activity'],
      'civicrm/ajax/relation' => ['tab' => 'rel', 'can_be_in_case' => TRUE],
      'civicrm/contact/view/rel' => ['tab' => 'rel', 'can_be_in_case' => TRUE, 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]],
      'civicrm/ajax/contactrelationships' => ['tab' => 'rel'],
      'civicrm/contact/view/contribution' => ['tab' => 'contribute', 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]],
      'civicrm/contact/view/case' => ['tab' => 'case', 'actions' => [CRM_Core_Action::VIEW]],
      'civicrm/contact/view/note' => ['tab' => 'note', 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]],
      'civicrm/contact/view/pledge' => ['tab' => 'pledge', 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]],
      'civicrm/contact/view/participant' => [
        'tab' => 'participant', 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE], 'can_be_snippet' => TRUE
      ],
      'civicrm/contact/view/membership' => ['tab' => 'member', 'actions' => [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]]
    ];
    $apiRoutes = [
      'entity_tag' => ['tab' => 'tag', 'actions' => [CRM_Core_Action::NONE, CRM_Core_Action::ADD, CRM_Core_Action::DELETE]]
    ];

    if (CRM_Utils_System::getUrlPath() != 'civicrm/ajax/rest') {
      if (CRM_Custompermission_Helper::isTab()) {
        $granted = CRM_Custompermission_RouteProcessor::accessContactTab(CRM_Contact_BAO_Relationship::EDIT);
      }
      else {
        foreach ($routes as $route => $params) {
          if (CRM_Utils_System::getUrlPath() == $route) {
            if (
              !empty($params['can_be_in_case']) &&
              (CRM_Utils_Request::retrieve('case_id', 'Positive') || CRM_Utils_Request::retrieve('caseID', 'Positive'))
            ) {
              $route['tab'] = 'case';
            }

            if (!empty($params['actions']) && !in_array(CRM_Utils_Request::retrieve('action', 'String'), $params['actions'])) {
              $granted = FALSE;
            }
            else if (CRM_Custompermission_RouteProcessor::accessEntity($params['tab'], CRM_Contact_BAO_Relationship::EDIT)) {
              $granted = TRUE;
            }

            break;
          }
        }
      }
    }
    else {
      foreach ($apiRoutes as $route => $params) {
        if (
          CRM_Utils_Request::retrieve('entity', 'String') == $route &&
          in_array(CRM_Utils_Request::retrieve('action', 'String'), $params['actions'])
        ) {
          $granted = CRM_Custompermission_RouteProcessor::accessEntity($params['tab'], CRM_Contact_BAO_Relationship::EDIT);

          break;
        }
      }
    }

    return $granted;
  }

}
