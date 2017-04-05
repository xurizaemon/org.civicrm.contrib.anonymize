<?php

namespace Civi\Anonymize;

class Processor {

  const TABLE_PREFIX = 'civicrm_';

  /**
   * @var array of strings. Each string is one SQL query WITH a semicolon.
   */
  protected $queryQueue = array();

  /**
   * @var string (e.g. "random", "jumble", etc) passed in through API call
   */
  protected $strategy;

  /**
   * @var string (e.g. "en_US") passed in through API call
   */
  protected $locale;

  /**
   * Processor constructor.
   * @param string $strategy
   * @param string $locale
   */
  public function __construct($strategy, $locale) {
    $this->strategy = $strategy;
    $this->locale = $locale;
  }

  /**
   * Add a query to the query queue
   *
   * @param $sql array|string A complete SQL query at add to the queue (or an
   * array of such queries). The semicolon can be present or not.
   */
  protected function addSQL($sql) {
    if (!is_array($sql)) {
      $sql = array($sql);
    }
    foreach ($sql as $query) {
      $query = trim($query, " \n;");
      $this->queryQueue[] = $query . ';';
    }
  }

  /**
   * Add a SQL comment into the queue (mostly for debugging)
   *
   * @param $content string The raw (uncommented) content to add. Must be a
   * single-line string
   */
  protected function addSQLComment($content) {
    $content = "-- $content";
    $this->queryQueue[] = $content;
  }

  /**
   * @return array
   */
  public function getQueries() {
    return $this->queryQueue;
  }

  /**
   * @return string returns one string with all queries, ready to execute
   */
  public function getSQLCombined() {
    return implode("\n\n\n", $this->queryQueue);
  }

}
