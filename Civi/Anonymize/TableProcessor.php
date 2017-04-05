<?php

namespace Civi\Anonymize;

class TableProcessor extends Processor {

  /**
   * @var string the name of the database table
   */
  private $table;

  /**
   * @var array the configuration (from yaml file) for the specific table
   */
  private $tableConfig = array();

  /**
   * @var array where each element has a field name as the key and an array of
   * (string) stipulations as the value.
   */
  private $stipulationsByField;

  /**
   * @var array of strings (without keys) for the names of fields to set to null
   */
  private $clear = array();

  /**
   * TableProcessor constructor.
   * @param string $strategy
   * @param string $locale
   * @param string $tableName
   * @param array $tableConfig
   */
  public function __construct($strategy, $locale, $tableName, $tableConfig) {
    $defaults = array(
      'clear' => array(),
      'post_process' => array(),
      'stipulations' => array(),
      'modify' => array(),
    );
    $this->tableConfig = array_merge($defaults, $tableConfig);
    parent::__construct($strategy, $locale);
    $this->table = $tableName;
    $this->configureStipulations();
    $this->configureClear();
  }

  /**
   * Take stipulations array from YAML and transpose it
   */
  private function configureStipulations() {
    $result = [];
    foreach ($this->tableConfig['stipulations'] as $stipulation => $fields) {
      $result = array_merge_recursive(
        $result,
        array_fill_keys($fields, array($stipulation))
      );
    }
    $this->stipulationsByField = $result;
  }

  /**
   * Take the "clear" setting from yaml, add every field we are going to modify
   * because it's helpful to clear them first. Why? Because some of the fields
   * we modify get "sparse" values, meaning only set in some places. It's
   * easiest to just clear everything first.
   */
  private function configureClear() {
    $this->clear = array_merge(
        $this->tableConfig['clear'],
        array_keys($this->tableConfig['modify'])
    );
  }

  /**
   * Returns the stipulations (strings) for one field
   *
   * @param $fieldName string
   * @return array of string
   */
  protected function getStipulations($fieldName) {
    if (!empty($this->stipulationsByField[$fieldName])) {
      $stipulations = $this->stipulationsByField[$fieldName];
    }
    else {
      $stipulations = array();
    }
    return $stipulations;
  }

  /**
   * Add SQL queries to the queue based on config
   */
  public function process() {
    $this->processClear();
    $this->processModify();
    $this->processPost();
  }

  protected function processClear() {
    $this->addSQL(SQL::updateFieldsToSameValue(
        $this->table,
        $this->clear,
        "NULL"
    ));
  }

  protected function processModify() {
    foreach ($this->tableConfig['modify'] as $fieldName => $fieldConfig) {
      $fieldProcessor = new FieldProcessor(
          $this->strategy,
          $this->locale,
          $this->table,
          $fieldName,
          $fieldConfig,
          $this->getStipulations($fieldName)
      );
      $fieldProcessor->process();
      $this->addSQL($fieldProcessor->getQueries());
    }
  }

  protected function processPost() {
    // @TODO
  }

}