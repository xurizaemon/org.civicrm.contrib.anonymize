<?php

namespace Civi\Anonymize;

use \DateTime;

use \Twig_Environment;
use \Twig_Loader_Filesystem;


define(SQL_TEMPLATE_DIR, __DIR__ . '/SQL/Templates');

/**
 * A collection of functions which produce SQL code to do things like generate
 * random values
 *
 * @package Civi\Anonymize
 */
class SQL {

  /**
   * Returns an SQL expression that randomly chooses between a supplied value or
   * a NULL value.
   *
   * @param $value string An SQL expression to use as the resulting value
   * when we don't choose NULL.
   *
   * @param $nullLikelihood float A number greater than 0 and less than 1
   * which represents the likelihood of choosing NULL over $value.
   * When $nullLikelihood = 0.0001 then NULL will almost never be chosen.
   * When $nullLikelihood = 0.9999 then NULL will almost always be chosen.
   *
   * @return string
   *
   * @throws \Exception when $nullLikelihood is out of bounds
   */
  public static function valueOrNull($value, $nullLikelihood) {
    $p = (float) $nullLikelihood;
    $pIsInRange = (bool) ($p > 0 && $p < 1);
    if (!$pIsInRange) {
      throw new \Exception("nullLikelihood must be greater than zero "
        . "and less than 1. Value given: " . $nullLikelihood);
    }
    return "IF(rand() <= $p, $value, NULL)";
  }

  /**
   * Returns an SQL expression that generates a random date within the
   * specified bounds
   *
   * Dates can be specified as either a string like '2017-01-01' or 'now' or by
   * passing a PHP DateTime object
   *
   * @param $start string|DateTime The lower bound for dates to be generated
   * @param $end string|DateTime the upper bound for dates to be generated
   * @return string
   */
  public static function randomDate($start, $end) {
    if (!is_a($start, "DateTime")) {
      $start = new DateTime($start);
    }
    if (!is_a($end, "DateTime")) {
      $end = new DateTime($end);
    }
    $daysMax = $end->diff($start)->days;
    $days = self::randomInteger(0, $daysMax);
    $startDateString = '"' . $start->format('Y-m-d') . '"';
    return "$startDateString + INTERVAL $days DAY";
  }

  /**
   * Returns an SQL expression to generate one random integer within a specified
   * range
   *
   * @param $min int The lower bound for the number to be generated
   * @param $max int The upper bound for the number to be generated
   * @return string
   */
  public static function randomInteger($min, $max) {
    $range = $max - $min + 1;
    $minString = ($min == 0) ? '' : "$min + ";
    return "FLOOR({$minString}RAND() * $range)";
  }

  /**
   * Returns an SQL expression to generate one random character of the specified
   * type
   *
   * @param $type string a single-character code to define the character set
   * @return string
   * @throws \Exception if $type is not found
   */
  public static function randomChar($type) {
    $types = array(
      'l' => 'abcdefghijklmnoprstuvwy', // lower case letter
      'u' => 'ABCDEFGHIJKLMNOPRSTUVWY', // upper case letter
      'd' => '0123456789', // digit
      'p' => '123456789', // positive digit
      'e' => '0123456789abcdefghijklmnoprstuvwy', // email-like character
    );
    if (!array_key_exists($type, $types)) {
      throw new \Exception("Invalid character type code");
    }
    $charSet = $types[$type];
    $position = self::randomInteger(1, strlen($charSet));
    $charSetSQL = self::stringLiteral($charSet);
    return "SUBSTR($charSetSQL, $position, 1)";
  }

  /**
   * Returns an SQL expression to generate one random string based on the
   * supplied pattern. For example:
   *   \p\d\d-\p\d\d-\d\d\d\d
   * will return a US phone number
   *
   * Codes used within the pattern are defined in randomChar().
   *
   * @param $pattern string
   *
   * @return string
   */
  public static function randomStringFromPattern($pattern) {
    // Isolate substrings like "\d" and "\l"
    $parts = preg_split('#(\\\.)#', $pattern, NULL, PREG_SPLIT_DELIM_CAPTURE);
    // Remove empty strings
    $parts = array_filter($parts, function ($i) {
      return !empty($i);
    });
    $result = array();
    foreach ($parts as $part) {
      try {
        $result[] = self::randomChar(substr($part,1,1));
      }
      catch (\Exception $e) {
        $result[] = self::stringLiteral($part);
      }
    }
    return self::concat($result);
  }

  /**
   * Returns an SQL expression that produces a random string of characters with
   * a deterministic length as specified
   *
   * @param $case string What should the case of the returned string be?
   * Options are: 'caps_none', 'caps_all', 'caps_first'
   *
   * @param $length int The length of string to return
   *
   * @return string
   *
   * @throws \Exception if case is invalid
   */
  private static function fixedLengthRandomString($case, $length) {
    $cases = array('caps_none', 'caps_all', 'caps_first');
    if (!in_array($case, $cases)) {
      throw new \Exception("Invalid string case $case."
        . "Options are: " . implode(', ', $cases));
    }
    $firstChar = self::randomChar(($case == 'caps_none') ? 'l' : 'u');
    $otherChars = self::randomChar(($case == 'caps_all') ? 'u' : 'l');
    $allChars = array_merge(
      array($firstChar),
      array_fill(0, $length - 1, $otherChars)
    );
    return self::concat($allChars);
  }

  /**
   * Returns an SQL expression that produces a random string of characters of
   * random length
   *
   * @param $case string What should the case of the returned string be?
   * Options are: 'caps_none' (aka 'lower', 'caps_all' (aka 'upper'), and
   * 'caps_first' (which is the default)
   *
   * @param $length int|array The minimum and maximum length of the string to
   * be generated
   *
   * @return string
   */
  public static function randomString($case = 'caps_first', $length = array(3, 10)) {
    $case = ($case == 'lower') ? 'caps_none' : $case;
    $case = ($case == 'upper') ? 'caps_all' : $case;
    if (!is_array($length)) {
      $length = array($length, $length);
    }
    // Generate a string as long as we might need
    $longString = self::fixedLengthRandomString($case, max($length));
    if (min($length) == max($length)) {
      return $longString;
    }
    else {
      $stringLength = self::randomInteger(min($length), max($length));
      // Take only a part of the string, beginning with the first character
      // and taking a random length
      return "SUBSTRING($longString,\n    1, $stringLength)";
    }
  }

  /**
   * Returns SQL to truncate one table
   *
   * @param $tableName string the table to truncate
   *
   * @return string
   */
  public static function truncate($tableName) {
    return "DELETE FROM $tableName";
  }

  /**
   * Returns an SQL "WHERE" clause expression which includes the word "WHERE"
   *
   * @param array $whereConditions array of string (without keys) for SQL
   * expressions to use within the WHERE clause. (e.g. "is_deleted != 1")
   * @return string
   */
  public static function whereClause($whereConditions = array()) {
    $where = is_array($whereConditions) ? $whereConditions : array($whereConditions);
    $where = array_filter($where); // remove empty items
    if (!empty($where)) {
      return "\nWHERE\n" . implode(" AND\n", $where);
    }
    else {
      return '';
    }
  }

  /**
   * Returns a complete SQL query to update multiple fields in one table
   *
   * @param string $table the name of the table to update
   * @param array $fieldValueMapping Keys are field names as strings. Values are
   * SQL expressions (as strings) to use as the new value for the field
   * @param array $where an array of strings for SQL expressions to use within
   * the WHERE clause. (e.g. "is_deleted != 1")
   * @return string
   */
  public static function updateFields($table, $fieldValueMapping, $where = array()) {
    $assignments = array();
    foreach ($fieldValueMapping as $field => $value) {
      $assignments[] = "\n  $field = $value";
    }
    $whereClause = self::whereClause($where);
    $setClause = implode(',',$assignments);
    return "UPDATE {$table}\nSET{$setClause}{$whereClause}";
  }

  /**
   * Returns a complete SQL query to update several field in one table and set
   * them all to the same value
   *
   * @param string $table the name of the table to update
   * @param array $fields strings (without keys) of field names
   * @param string $value SQL expression to use as the new value for all the
   * fields
   * @param array $where an array of strings for SQL expressions to use within
   * the WHERE clause. (e.g. "is_deleted != 1")
   * @return string
   */
  public static function updateFieldsToSameValue($table, $fields, $value, $where = array()) {
    $fieldValueMapping = array_fill_keys($fields, $value);
    return self::updateFields($table, $fieldValueMapping, $where);
  }

  /**
   * Returns a complete SQL query to update one field in one table
   *
   * @param $table string the name of the table to update
   * @param $field string the name of the field to update
   * @param $value string an SQL expression to use as the new field value
   * @param array $where an array of strings for SQL expressions to use within
   * the WHERE clause. (e.g. "is_deleted != 1")
   * @return string
   */
  public static function updateField($table, $field, $value, $where = array()) {
    return self::updateFields($table, array($field => $value), $where);
  }

  /**
   * Return an SQL expression which double-quotes whatever is supplied
   *
   * @param $string string
   * @return string
   */
  public static function stringLiteral($string) {
    return "\"$string\"";
  }

  /**
   * Ensures that the supplied query has a semicolon
   * @param string $query
   * @return string
   */
  public static function prepareQuery($query) {
    $query = trim($query, " \n;");
    $query = empty($query) ? "" : "$query;";
    return $query;
  }

  /**
   * Split up one string with multiple queries into separate strings, each
   * containing one query. The logic here is VERY primitive. We split any time
   * we see a semicolon. This means that if a semicolon occurs within a string
   * in an SQL expression, then this function will not split it correctly :(
   *
   * @param string $queries
   * @return array of strings (without keys) where each one is an SQL query with
   * a semicolon. Even if only one query is supplied, it will still be returned
   * within an array
   */
  public static function splitQueries($queries) {
    $result = explode(';', $queries);
    $result = array_map(array('self', 'prepareQuery'), $result);
    $result = array_filter($result);
    return $result;
  }

  /**
   * Test whether or not some SQL is a comment. Currently, it only recognizes
   * comments which begin with two dashes.
   *
   * @param string $query
   * @return boolean
   */
  public static function isComment($query) {
    return (boolean) preg_match('/^--/', $query);
  }

  /**
   * Returns an SQL expression which concatenates all of the supplied values.
   * Values can be supplied as one array or separately
   *
   * @return string
   */
  public static function concat() {
    // Get all supplied values, flattened
    $args = func_get_args();
    $values = array();
    array_walk_recursive($args, function ($a) use (&$values) {
      $values[] = $a;
    });

    // Only use CONCAT if we only have more than one value
    if (count($values) == 1) {
      return $values[0];
    }

    return "CONCAT(\n    " . implode(", \n    ", $values) . ")";
  }

  /**
   * @param string $templateName
   * @param array $replacements
   * @return string
   */
  public static function renderFromTemplate($templateName, $replacements = array()) {
    $loader = new Twig_Loader_Filesystem(SQL_TEMPLATE_DIR);
    $twig = new Twig_Environment($loader);
    $twigResult = $twig->render("$templateName.sql.twig", $replacements);
    return htmlspecialchars_decode($twigResult);
  }

  /**
   * @param string $table
   * @param array $fieldsDefinition
   * @param string $selectChoices
   * @param array $updateWhere array of strings (without keys) which are each
   * valid conditions to be used withing an SQL WHERE clause.
   * @return string
   */
  public static function updateFieldsFromRandomChoice(
      $table,
      $fieldsDefinition,
      $selectChoices,
      $updateWhere) {
    return self::renderFromTemplate('random_choice', array(
      'table' => $table,
      'fields' => $fieldsDefinition,
      'select_choices' => $selectChoices,
      'update_where' => SQL::whereClause($updateWhere),
    ));
  }

}
