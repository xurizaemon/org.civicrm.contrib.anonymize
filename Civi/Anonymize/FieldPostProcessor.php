<?php

namespace Civi\Anonymize;


class FieldPostProcessor extends FieldProcessor {

  /**
   * FieldPostProcessor constructor.
   * @param string $strategy
   * @param string $locale
   * @param string $tableName
   * @param string $fieldName
   */
  public function __construct($strategy, $locale, $tableName, $fieldName) {
    parent::__construct($strategy, $locale, $tableName, $fieldName);
    $this->verb = 'Post-processing';
  }

  protected function setWhereConditions() {
    $this->whereConditions = array();
  }

  /**
   * Returns the name of the method that we should use when processing this
   * field
   *
   * @return string
   * @throws \Exception if no method is defined for the defined strategy
   */
  protected function methodToUse() {
    $tableShortName = preg_replace(
        '/^' . self::TABLE_PREFIX . '/',
        '',
        $this->table
    );
    return "{$tableShortName}__{$this->field}";
  }

  // ======================================================================
  //        Methods which are called based on per-field yaml config
  // ======================================================================

  protected function contact__display_name() {
    $this->addSQLToUpdateField(
      SQL::concat('first_name', SQL::stringLiteral(' '),'last_name'),
      array('contact_type = "Individual"')
    );
    $this->addSQLToUpdateField(
      'household_name',
      array('contact_type = "Household"')
    );
    $this->addSQLToUpdateField(
      'organization_name',
      array('contact_type = "Organization"')
    );
  }

  protected function contact__sort_name() {
    $this->addSQLToUpdateField(
      SQL::concat('last_name', SQL::stringLiteral(', '), 'first_name'),
      array('contact_type = "Individual"')
    );
    $this->addSQLToUpdateField(
      'household_name',
      array('contact_type = "Household"')
    );
    $this->addSQLToUpdateField(
      'organization_name',
      array('contact_type = "Organization"')
    );
  }

  protected function contact__household_name() {
    $this->addSQL(SQL::renderFromTemplate('update_household_name'));
  }

  protected function address__country_id() {
    $this->addSQL(SQL::renderFromTemplate('update_address_country_id'));
  }

}
