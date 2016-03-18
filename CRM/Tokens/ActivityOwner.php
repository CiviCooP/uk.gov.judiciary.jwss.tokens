<?php

class CRM_Tokens_ActivityOwner implements \Symfony\Component\EventDispatcher\EventSubscriberInterface {

  protected $token_name = 'activity_owner';

  protected $token_label = 'Activity owner';

  protected $assignees = array();

  public static function getSubscribedEvents() {
    return array(
      \Civi\Token\Events::TOKEN_EVALUATE => 'evaluate',
    );
  }

  public function evaluate(\Civi\Token\Event\TokenValueEvent $event)  {
    if (!$this->checkActive($event->getTokenProcessor())) {
      return;
    }

    foreach($event->getRows() as $row) {
      $actionSearchResult = $row->context['actionSearchResult'];
      $row->tokens($this->token_name, 'display_name', $this->getAssigneeField($actionSearchResult->activity_id, 'display_name'));
      $row->tokens($this->token_name, 'email', $this->getAssigneeField($actionSearchResult->id, 'email'));
      $row->tokens($this->token_name, 'phone', $this->getAssigneeField($actionSearchResult->id, 'phone'));
    }
  }

  /**
   * Check is active.
   *
   * @param \Civi\Token\TokenProcessor $processor
   *
   * @return bool
   */
  private function checkActive(\Civi\Token\TokenProcessor $processor) {
    // Extracted from scheduled-reminders code. See the class description.
    return
      !empty($processor->context['actionMapping'])
      && $processor->context['actionMapping']->getEntity() === 'civicrm_activity';
  }

  public function tokens(&$tokens) {
    $t = array();
    $t[$this->token_name . '.display_name'] = ts('Display name of ' . $this->token_label);
    $t[$this->token_name . '.email'] = ts('E-mail of ' . $this->token_label);
    $t[$this->token_name . '.phone'] = ts('Phone number of ' . $this->token_label);
    $tokens[$this->token_name] = $t;
  }

  private function getAssigneeField($activity_id, $contact_field) {
    $name = '';
    try {
      $assignees = $this->getAssignees($activity_id);
      $assignee = reset($assignees);
      if ($assignee) {
        $name = civicrm_api3('Contact', 'getvalue', array(
          'return' => $contact_field,
          'id' => $assignee
        ));
      }
    } catch (Exception $e) {
      $name = '';
    }
    return $name;
  }

  private function getAssignees($activity_id) {
    if (!isset($this->assignees[$activity_id])) {
      $this->assignees[$activity_id] = array();
      $result = civicrm_api3('ActivityContact', 'get', array(
        'sequential' => 1,
        'activity_id' => $activity_id,
        'record_type_id' => "Activity Assignees",
      ));
      foreach($result['values'] as $assignee) {
        $this->assignees[$activity_id][] = $assignee['contact_id'];
      }
    }
    return $this->assignees[$activity_id];
  }
}