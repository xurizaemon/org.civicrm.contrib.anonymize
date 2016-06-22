<?php

require_once 'vendor/autoload.php';

use Faker\Factory;

/**
 * Email.Anonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_Email_Anonymize_spec(&$spec) {
  // $spec['locale']['api.required'] = 1;
}

/**
 * Email.Anonymize API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_Email_Anonymize($params) {
  if (!array_key_exists('locale', $params)) {
    $params['locale'] = 'en_US';
  }
  $faker = Faker\Factory::create($params['locale']);

  if (array_key_exists('id', $params)) {
    $get_params = array(
      'id' => $params['id'],
    );
    $Email = civicrm_api3('Email', 'Get', $get_params);
    $values = reset($Email['values']);
    $create_params = array(
      'email' => $faker->safeEmail(),
    );
    $Email = civicrm_api3('Email', 'Create', array_merge($get_params, $create_params));
    return civicrm_api3_create_success($Email, $get_params, 'Contact', 'Get');
  }
}
