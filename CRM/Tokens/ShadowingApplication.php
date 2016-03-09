<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Tokens_ShadowingApplication {

  private static $singleton;

  private $custom_group = false;

  private $custom_fields = array();

  private function __construct() {
    try {
      $this->custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'JWSS_Applicaiton_Tribunal_Judge'));
      $custom_fields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $this->custom_group['id'], 'is_active' => '1', 'options' => array('sort' => "weight"),));
      $this->custom_fields = $custom_fields['values'];
    } catch (Exception $e) {
      $this->custom_group = false;
      $this>$this->custom_fields = array();
    }
  }

  /**
   * @return \CRM_Tokens_ShadowingApplication
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Tokens_ShadowingApplication();
    }
    return self::$singleton;
  }

  public function tokenValues(&$values, $cids) {
    $contacts_ids = $cids;
    if (!is_array($cids)) {
      $contacts_ids = array($cids);
    }
    foreach($contacts_ids as $cid) {
      $tokenValue = $this->getLatestApplication($cid);
      if (!is_array($cids)) {
        $values['contact.latest_shadowing_application'] = $tokenValue;
      } else {
        $values[$cid]['contact.latest_shadowing_application'] = $tokenValue;
      }
    }
  }

  public function tokens(&$tokens) {
    if (!$this->custom_group) {
      return;
    }
    $tokens['contact']['contact.latest_shadowing_application'] = $this->custom_group['title'];
  }

  protected function getLatestApplication($contact_id) {
    $sql = "SELECT * FROM `".$this->custom_group['table_name']."` WHERE entity_id = %1 ORDER BY id DESC LIMIT 0,1";
    $params[1] = array($contact_id, 'Integer');

    $html = "";
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if($dao->fetch()) {

      $html = "<table>";
      $html .= "<tr>";
      foreach($this->custom_fields as $field) {
        $html .= "<th>".$field['label']."</th>";
      }
      $html .= "</tr>";
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
      $html .= "</table>";
    }
    return $html;
  }

  protected function getFormattedFieldValue($value, $field) {
    if ($field['data_type'] == 'Date' && !empty($value)) {
      $actualPHPFormats = CRM_Core_SelectValues::datePluginToPHPFormats();
      $dateFormat = (array) CRM_Utils_Array::value($field['date_format'], $actualPHPFormats);
      $value = CRM_Utils_Date::processDate($value, NULL, FALSE, implode(" ", $dateFormat));
    }
    return $value;
  }

}