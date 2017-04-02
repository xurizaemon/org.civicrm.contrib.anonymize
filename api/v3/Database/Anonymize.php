<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Civi\Anonymize\ConfigProcessor;

define(FIELDS_CONFIG, __DIR__ . '/../../../fields.yml');

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
  $spec['strategy']['api.required'] = 0;
}

/**
 * Database.Anonymize API
 *
 * @param array $params
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_database_Anonymize($params) {
  $defaults = array(
    'strategy' => 'random',
    'locale' => 'en_US',
  );
  $params = array_merge($defaults, $params);
  $config = Yaml::parse(file_get_contents(FIELDS_CONFIG));
  $processor = new ConfigProcessor($config, $params['strategy'], $params['locale']);
  $processor->process();
  echo $processor->getSQLCombined();
}
