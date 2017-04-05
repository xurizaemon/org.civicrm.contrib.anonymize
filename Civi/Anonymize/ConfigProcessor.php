<?php

namespace Civi\Anonymize;

class ConfigProcessor extends Processor {

  /**
   * @var array Config from YAML
   */
  private $fullConfig;

  /**
   * ConfigProcessor constructor.
   *
   * @param string $strategy
   * @param string $locale
   * @param array $fullConfig directly from yaml file
   */
  public function __construct($strategy, $locale, $fullConfig) {
    $this->fullConfig = $fullConfig;
    parent::__construct($strategy, $locale);
  }

  /**
   * Adds queries to queue based on a full array of field config for multiple
   * tables
   */
  public function process() {
    foreach ($this->fullConfig as $tableShortName => $tableConfig) {
      $tableName = self::TABLE_PREFIX . $tableShortName;
      $this->addSQLComment("Table: `{$tableName}`");
      if ($tableConfig == 'truncate') {
        $this->addSQL(SQL::truncate($tableName));
      }
      else {
        $tableProcessor = new TableProcessor(
          $this->strategy, $this->locale, $tableName, $tableConfig);
        $tableProcessor->process();
        $this->addSQL($tableProcessor->getQueries());
      }
    }
  }

}
