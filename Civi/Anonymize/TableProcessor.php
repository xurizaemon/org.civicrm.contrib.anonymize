<?php

namespace Civi\Anonymize;

class TableProcessor extends ConfigProcessor {

  /**
   * @var string the name of the database table
   */
  protected $table;

  /**
   * @var array where each element has a field name as the key and an array of
   * (string) stipulations as the value.
   */
  private $stipulationsByField;

  public function __construct($config, $strategy, $locale, $tableName) {
    parent::__construct($config, $strategy, $locale);
    $this->table = $tableName;
    $this->processStipulations();
  }

  /**
   * Take stipulations array from YAML and transpose it
   */
  protected function processStipulations() {
    $this->stipulationsByField = array(); // @TODO
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
    if (!empty($this->config['clear'])) {
      $this->addSQL(SQL::updateFieldsToSameValue(
          $this->table,
          $this->config['clear'],
          "NULL"
      ));
    }
  }

  protected function processModify() {
    if (!empty($this->config['modify'])) {
      foreach ($this->config['modify'] as $fieldName => $fieldConfig) {
        $fieldProcessor = new FieldProcessor(
          $fieldConfig,
          $this->strategy,
          $this->locale,
          $this->table,
          $fieldName,
          $this->getStipulations($fieldName)
        );
        $fieldProcessor->process();
        $this->addSQL($fieldProcessor->getQueries());
      }
    }
  }

  protected function processPost() {
    // @TODO
  }

}