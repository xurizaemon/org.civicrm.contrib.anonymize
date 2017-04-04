<?php

namespace Civi\Anonymize;

class FieldProcessor extends TableProcessor {

  /**
   * @var string the name of this field in the database
   */
  protected $field;

  /**
   * @var array of string for special things that need to be done when
   * processing this field (e.g. "sparse")
   */
  protected $stipulations;

  public function __construct(
      $config,
      $strategy,
      $locale,
      $tableName,
      $fieldName,
      $stipulations = array()) {
    parent::__construct($config, $strategy, $locale, $tableName);
    $this->field = $fieldName;
    $this->stipulations = $stipulations;
  }

  /**
   * Returns the name of the method that we should use when processing this
   * field
   *
   * @return string
   * @throws \Exception if no method is defined for the defined strategy
   */
  protected function methodToUse() {
    if (is_string($this->config)) {
      return $this->config;
    }
    else {
      if (empty($this->config[$this->strategy])) {
        throw new \Exception("Strategy: {$this->strategy} is not " .
          "defined for table: {$this->table} field: {$this->field}");
      }
      else {
        return $this->config[$this->strategy];
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
  protected function addSQLToUpateField($value) {
    $this->addSQL(SQL::updateField($this->table, $this->field, $value));
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
    $this->addSQLToUpateField(SQL::randomStringFromPattern($pattern));
  }

  // ======================================================================
  //        Methods which are called based on per-field yaml config
  // ======================================================================

  protected function clear() {
    $this->addSQL(array()); // @TODO
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

  protected function fixed_string_donate_now() {
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
    $now = new \DateTime();
    $i = new \DateInterval('P80Y');
    $start = $now->sub($i); // 80 years ago
    $this->addSQLToUpateField(SQL::randomDate($start, $now));
  }

  protected function random_email() {
    $email = SQL::concat(
      SQL::randomString('lower', array(6,15)),
      SQL::stringLiteral('@example.org')
    );
    $this->addSQLToUpateField($email);
  }

  protected function random_foreign_key() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_is_deceased_and_date() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_name() {
    $this->addSQLToUpateField(SQL::randomString());
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
    $this->addSQLToUpateField($address);
  }

  protected function skip() {
    // Do nothing
  }

}
