<?php

namespace Civi\Anonymize;

class QueryQueue {

  /**
   * @var array of strings. Each string is one SQL query WITH a semicolon
   */
  protected $queries = array();

  public function __construct() {
  }

  /**
   * @param $sql string a complete SQL query at add to the queue
   */
  public function add($sql) {
    // @TODO deal with case where semicolon already exists
    $this->queries[] = $sql . ';';
  }

  /**
   * @return string returns one string with all queries, ready to execute
   */
  public function getCombined() {
    return implode("\n\n\n", $this->queries);
  }

}