<?php

namespace Civi\Anonymize;

class FieldModifyProcessor extends FieldProcessor {

  /**
   * @var array config from yaml for this specific field
   */
  private $fieldModifyConfig;

  /**
   * @var array No keys. Values are strings to represent special handling
   * needed for this field.
   */
  private $stipulations = array();

  /**
   * FieldProcessor constructor.
   * @param string $strategy
   * @param string $locale
   * @param string $tableName
   * @param string $fieldName
   * @param array $fieldModifyConfig
   * @param array $stipulations
   */
  public function __construct(
      $strategy,
      $locale,
      $tableName,
      $fieldName,
      $fieldModifyConfig,
      $stipulations = array()) {
    parent::__construct($strategy, $locale, $tableName, $fieldName);
    $this->verb = 'Modifying';
    $this->fieldModifyConfig = $fieldModifyConfig;
    $this->stipulations = $stipulations;
    $this->setWhereConditions();
  }

  /**
   * @throws \Exception if there are any unrecognized stipulations
   */
  protected function setWhereConditions() {
    $conditions = array(
      'only_individual' => 'contact_type = "Individual"',
      'only_household' => 'contact_type = "Household"',
      'only_organization' => 'contact_type = "Organization"',
      'sparse' => 'rand() < 0.10',
      'super_sparse' => 'rand() < 0.01',
    );
    foreach ($this->stipulations as $stipulation) {
      if (!empty($conditions[$stipulation])) {
        $this->whereConditions[] = $conditions[$stipulation];
      }
      else {
        throw new \Exception("Stipulation '$stipulation' defined for " .
          "table '{$this->table}' is not recognized.");
      }
    }
  }

  /**
   * Returns the name of the method that we should use when processing this
   * field
   *
   * @return string
   * @throws \Exception if no method is defined for the defined strategy
   */
  protected function methodToUse() {
    if (is_string($this->fieldModifyConfig)) {
      return $this->fieldModifyConfig;
    }
    else {
      if (empty($this->fieldModifyConfig[$this->strategy])) {
        throw new \Exception("Strategy: {$this->strategy} is not " .
          "defined for table: {$this->table} field: {$this->field}");
      }
      else {
        return $this->fieldModifyConfig[$this->strategy];
      }
    }
  }

  // ======================================================================
  //        Methods which are called based on per-field yaml config
  // ======================================================================

  // =============================== CLEAR =================================

  protected function clear() {
    $this->addSQLToUpdateField('NULL');
  }

  // ============================ FIXED STRING ==============================

  protected function fixed_string_donate_now() {
    $this->addSQLToUpdateField(SQL::stringLiteral("Donate Now"));
  }

  // =============================== FAKE ==================================

  protected function fake_city() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_email() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_first_name() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_job_title() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_last_name() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_organization_name() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_phone() {
    $this->addSQL(array()); // @TODO
  }

  protected function fake_street_address() {
    $this->addSQL(array()); // @TODO
  }

  // =============================== JUMBLE ==================================

  protected function jumble_all() {
    $this->addSQL(array()); // @TODO
  }

  protected function jumble_first_nick_grouped() {
    $this->addSQL(array()); // @TODO
  }

  // =============================== RANDOM ==================================

  protected function random_birth_date() {
    $start = new \DateTime();
    $start->sub(new \DateInterval('P80Y')); // shift back 80 years
    $now = new \DateTime();
    $now->sub(new \DateInterval('P1Y')); // shift back 1 year
    $this->addSQLToUpdateField(SQL::randomDate($start, $now));
  }

  protected function random_email() {
    $email = SQL::concat(
      SQL::randomString('lower', array(6,15)),
      SQL::stringLiteral('@example.org')
    );
    $this->addSQLToUpdateField($email);
  }

  protected function random_gender_id() {
    $this->addSQLUpdateFromOptionValues('gender');
  }

  protected function random_is_deceased_and_date() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_name() {
    $this->addSQLToUpdateField(SQL::randomString());
  }

  protected function random_phone() {
    $this->addSQLUpdateFromLocaleBasedPatterns(array(
      'en_US' => '\p\d\d-\p\d\d-\d\d\d\d',
    ));
  }

  protected function random_postal_code() {
    $this->addSQLUpdateFromLocaleBasedPatterns(array(
      'en_US' => '\d\d\d\d\d',
    ));
  }

  protected function random_prefix_id() {
    $this->addSQLUpdateFromOptionValues('individual_prefix');
  }

  protected function random_state_province_id() {
    $this->addSQL(SQL::renderFromTemplate('set_most_common_country_id'));
    $select = "SELECT id 
      FROM civicrm_state_province 
      WHERE country_id = @most_common_country_id";
    $this->addSQL(SQL::updateFieldsFromRandomChoice(
        $this->table,
        array($this->field => 'int(10)'),
        $select
    ));
  }

  protected function random_street_address() {
    $address = SQL::concat(
      SQL::randomInteger(1, 99999),
      SQL::stringLiteral(" "),
      SQL::randomString('caps_first', array(5,9)),
      SQL::stringLiteral(" "),
      SQL::randomString('caps_first', array(2,3)),
      SQL::stringLiteral(".")
    );
    $this->addSQLToUpdateField($address);
  }

  protected function random_suffix_id() {
    $this->addSQLUpdateFromOptionValues('individual_suffix');
  }

  protected function skip() {
    // Do nothing
  }

}
