<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Tokens_CareerHistory {

  private static $singleton;

  private $custom_group = false;

  private $custom_fields = array();

  private function __construct() {
    try {
      $this->custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Career_History'));
      $custom_fields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $this->custom_group['id'], 'is_active' => '1', 'options' => array('sort' => "weight"),));
      $this->custom_fields = $custom_fields['values'];
    } catch (Exception $e) {
      $this->custom_group = false;
      $this>$this->custom_fields = array();
    }
  }

  /**
   * @return \CRM_Tokens_CareerHistory
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Tokens_CareerHistory();
    }
    return self::$singleton;
  }

  public function tokenValues(&$values, $cids) {
    if (!$this->custom_group) {
      return;
    }

    $contacts_ids = $cids;
    if (!is_array($cids)) {
      $contacts_ids = array($cids);
    }
    foreach($contacts_ids as $cid) {
      $tokenValue = $this->buildCareerHistory($cid);
      if (!is_array($cids)) {
        $values['contact.career_history'] = $tokenValue;
      } else {
        $values[$cid]['contact.career_history'] = $tokenValue;
      }
    }
  }

  public function tokens(&$tokens) {
    if (!$this->custom_group) {
      return;
    }
    $tokens['contact']['contact.career_history'] = $this->custom_group['title'];
  }

  protected function buildCareerHistory($contact_id) {
    $sql = "SELECT * FROM `".$this->custom_group['table_name']."` WHERE entity_id = %1 ORDER BY id ASC";
    $params[1] = array($contact_id, 'Integer');

    $html = "<table>";
    $html .= "<tr>";
    foreach($this->custom_fields as $field) {
      $html .= "<th>".$field['label']."</th>";
    }
    $html .= "</tr>";

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      $html .= "<tr>";
      foreach($this->custom_fields as $field) {
        $value = "";
        $field_name = $field['column_name'];
        if (!empty($dao->$field_name)) {
          $value = $this->getFormattedFieldValue($dao->$field_name, $field);
        }


        $html .= "<td>".$value."</td>";
      }
      $html .= "</tr>";
    }
    $html .= "</table>";
    return $html;
  }

  protected function getFormattedFieldValue($value, $field) {
    if ($field['data_type'] == 'Date' && !empty($value)) {
      $actualPHPFormats = CRM_Core_SelectValues::datePluginToPHPFormats();
      $dateFormat = (array) CRM_Utils_Array::value($field['date_format'], $actualPHPFormats);
      $value = CRM_Utils_Date::processDate($value, NULL, FALSE, implode(" ", $dateFormat));
    } elseif (!empty($field['option_group_id']) && $value) {
      try {
        $value = civicrm_api3('OptionValue', 'getvalue', array(
          'return' => 'label',
          'option_group_id' => $field['option_group_id'],
          'value' => $value,
        ));
      } catch (Exception $e) {
        throw $e;
      }
    }
    return $value;
  }

}