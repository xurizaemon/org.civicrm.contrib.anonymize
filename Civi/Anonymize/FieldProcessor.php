<?php

namespace Civi\Anonymize;

class FieldProcessor extends Processor {

  /**
   * @var string the name of the database table
   */
  private $table;

  /**
   * @var string the name of this field in the database
   */
  private $field;

  /**
   * @var array config from yaml for this specific field
   */
  private $fieldConfig;

  /**
   * @var array of strings for conditions to use in the WHERE clause of any
   * updates we do to this field
   */
  private $whereConditions = array();

  /**
   * FieldProcessor constructor.
   * @param string $strategy
   * @param string $locale
   * @param string $tableName
   * @param string $fieldName
   * @param array $fieldConfig
   * @param array $stipulations
   */
  public function __construct(
      $strategy,
      $locale,
      $tableName,
      $fieldName,
      $fieldConfig,
      $stipulations = array()) {
    parent::__construct($strategy, $locale);
    $this->table = $tableName;
    $this->field = $fieldName;
    $this->fieldConfig = $fieldConfig;
    $this->setWhereConditions($stipulations);
  }

  /**
   * @param array $stipulations No keys. Values are strings to represent special
   * handling needed for this field.
   * @throws \Exception if there are any unrecognized stipulations
   */
  protected function setWhereConditions($stipulations) {
    $conditions = array(
      'only_individual' => 'contact_type = "Individual"',
      'only_household' => 'contact_type = "Household"',
      'only_organization' => 'contact_type = "Organization"',
      'sparse' => 'rand() < 0.10',
      'super_sparse' => 'rand() < 0.01',
    );
    foreach ($stipulations as $stipulation) {
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
    if (is_string($this->fieldConfig)) {
      return $this->fieldConfig;
    }
    else {
      if (empty($this->fieldConfig[$this->strategy])) {
        throw new \Exception("Strategy: {$this->strategy} is not " .
          "defined for table: {$this->table} field: {$this->field}");
      }
      else {
        return $this->fieldConfig[$this->strategy];
      }
    }
  }

  public function process() {
    $method = $this->methodToUse();
    $this->addSQLComment(
      "Modifying Field `{$this->field}` using method '$method'");
    $this->$method();
  }

  /**
   * @param $value string an SQL expression used for the new value of the field
   */
  protected function addSQLToUpdateField($value) {
    $this->addSQL(SQL::updateField($this->table, $this->field, $value, $this->whereConditions));
  }

  /**
   * Sorry this function name is so long!
   *
   * @param $patterns array Keys are locale strings. Values are patterns to be
   * supplied to the SQL::
   */
  protected function addSQLUpdateFromLocaleBasedPatterns($patterns) {
    if (empty($patterns[$this->locale])) {
      $pattern = $patterns['en_US'];
    }
    else {
      $pattern = $patterns[$this->locale];
    }
    $this->addSQLToUpdateField(SQL::randomStringFromPattern($pattern));
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

  protected function random_foreign_key() {
    $this->addSQL(array()); // @TODO
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

  protected function random_state_province_id() {
    $this->addSQL(array()); // @TODO
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

  protected function skip() {
    // Do nothing
  }

}
