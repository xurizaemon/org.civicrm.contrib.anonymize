<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Civi\Anonymize\QueryQueue;
use Civi\Anonymize\SQL;

define(FIELDS_CONFIG, __DIR__ . '/../../../fields.yml');
define(TABLE_PREFIX, 'civicrm_');

/**
 * Database.Anonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_database_Anonymize_spec(&$spec) {
  $spec['locale']['api.required'] = 0;
  $spec['id-min']['api.required'] = 0;
  $spec['id-max']['api.required'] = 0;
}

/**
 * Database.Anonymize API
 *
 * @param array $params
 * @return null
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_database_Anonymize($params) {
  $idMin = (int) $params['id-min'];
  $idMax = (int) $params['id-max'];
  $queue = new QueryQueue();

  $fields = Yaml::parse(file_get_contents(FIELDS_CONFIG));

  foreach($fields as $tableShortName => $tableConfig) {
    $table = TABLE_PREFIX . $tableShortName;
    if ($tableConfig == 'truncate') {
      $queue->add(SQL::truncate($table));
    }
    else {
      // @TODO
    }
  }

  echo $queue->getCombined();

}

