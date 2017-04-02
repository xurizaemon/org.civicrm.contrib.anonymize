<?php

namespace Civi\Anonymize;

use \DateTime;

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
    return "FLOOR($min + RAND() * $max + 1)";
  }

  /**
   * Returns an SQL expression to generate one random character of the specified
   * type
   *
   * @param $type string must be 'lower', 'upper', or 'digit'
   * @return string
   * @throws \Exception when type is invalid
   */
  public static function randomChar($type) {
    $types = array(
      'lower' => array('a', 'z'),
      'upper' => array('A', 'Z'),
      'digit' => array('0', '9'),
    );
    if (!array_key_exists($type, $types)) {
      throw new \Exception("Invalid character type '{$type}'. "
        . "Type must be one of: " . implode(', ', array_keys($types)));
    }
    $charCodeMin = ord($types[$type][0]);
    $charCodeMax = ord($types[$type][1]);
    $charCode = self::randomInteger($charCodeMin, $charCodeMax);
    return "CHAR($charCode)";
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
    $firstChar = self::randomChar(($case == 'caps_none') ? 'lower' : 'upper');
    $otherChars = self::randomChar(($case == 'caps_all') ? 'upper' : 'lower');
    $allChars = array_merge(
      array($firstChar),
      array_fill(0, $length - 1, $otherChars)
    );
    return "CONCAT(" . implode(",\n    ", $allChars) . ")";
  }

  /**
   * Returns an SQL expression that produces a random string of characters of
   * random length
   *
   * @param $case string What should the case of the returned string be?
   * Options are: 'lower' (default), 'upper', or 'sentence'
   *
   * @param $length int|array The minimum and maximum length of the string to
   * be generated
   *
   * @return string
   */
  public static function randomString($case = 'caps_first', $length = array(3, 10)) {
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
      return "SUBSTRING($longString, 1, $stringLength)";
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
    return "TRUNCATE TABLE $tableName";
  }

  public static function updateField($table, $field, $value, $where = null) {
    $whereClause = '';
    if (!empty($where)) {
      $whereClause = "\nWHERE $where";
    }
    return "UPDATE $table\nSET $field = $value" . $whereClause;
  }

}
