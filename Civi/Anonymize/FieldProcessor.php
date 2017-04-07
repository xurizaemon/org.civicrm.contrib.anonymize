<?php

namespace Civi\Anonymize;

abstract class FieldProcessor extends Processor {

  /**
   * @var string the name of the database table
   */
  protected $table;

  /**
   * @var string the name of this field in the database
   */
  protected $field;

  /**
   * @var array of strings for conditions to use in the WHERE clause of any
   * updates we do to this field
   */
  protected $whereConditions = array();

  /**
   * @var string this is used in system messages to say what sort of action
   * we're taking on this field (e.g. "Modifying" or "Post-processing")
   */
  protected $verb = 'Processing';

  /**
   * FieldProcessor constructor.
   * @param string $strategy
   * @param string $locale
   * @param string $tableName
   * @param string $fieldName
   */
  public function __construct(
      $strategy,
      $locale,
      $tableName,
      $fieldName) {
    parent::__construct($strategy, $locale);
    $this->table = $tableName;
    $this->field = $fieldName;
  }

  /**
   * Child classes must define a way to set $this->whereConditions
   */
  protected abstract function setWhereConditions();

  /**
   * Child classes must which method to use when processing this field
   *
   * @return string
   */
  protected abstract function methodToUse();

  /**
   * Add SQL expressions to the queue based on config for this field and a
   * variable function
   */
  public function process() {
    $method = $this->methodToUse();
    $this->addSQLComment("{$this->verb} field `{$this->field}` using method '$method'");
    if (method_exists($this, $method)) {
      // Process the field based on a variable function (which is defined in
      // the child classes of this class
      $this->$method();
    }
    else {
      $verb = strtolower($this->verb);
      throw new \Exception("Method $method is not defined for field $verb");
    }
  }

  /**
   * @param string $value an SQL expression used for the new value of the field
   * @param array $customWhere an array of "where" conditions. If not specified,
   * then the default "where" conditions will be used, from
   * $this->whereConditions
   */
  protected function addSQLToUpdateField($value, $customWhere = array()) {
    $where = empty($customWhere) ? $this->whereConditions : $customWhere;
    $this->addSQL(SQL::updateField($this->table, $this->field, $value, $where));
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

  /**
   * Picks one of the values from within an option group
   * @param $groupName string (e.g. "individual_suffix")
   */
  protected function addSQLUpdateFromOptionValues($groupName) {
    $groupName = SQL::stringLiteral($groupName);
    $select = "SELECT v.value
      FROM civicrm_option_value v
      JOIN civicrm_option_group g ON
        g.id = v.option_group_id
      WHERE g.name = $groupName";
    $this->addSQL(SQL::updateFieldsFromRandomChoice(
      $this->table,
      array($this->field => 'int(10)'),
      $select,
      $this->whereConditions
    ));
  }

}
