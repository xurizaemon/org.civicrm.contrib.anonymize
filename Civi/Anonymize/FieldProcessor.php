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

  public function __construct($config, $strategy, $tableName, $fieldName, $stipulations = array()) {
    parent::__construct($config, $strategy, $tableName);
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


  protected function clear() {
    $this->addSQL(array()); // @TODO
  }

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

  protected function jumble_all() {
    $this->addSQL(array()); // @TODO
  }

  protected function jumble_first_nick_grouped() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_birth_date() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_email() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_foreign_key() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_is_deceased_and_date() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_name() {
    $this->addSQL(SQL::updateField($this->table, $this->field, SQL::randomString()));
  }

  protected function random_phone() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_postal_code() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_state_province_id() {
    $this->addSQL(array()); // @TODO
  }

  protected function random_street_address() {
    $this->addSQL(array()); // @TODO
  }

  protected function skip() {
    // Do nothing
  }

}
