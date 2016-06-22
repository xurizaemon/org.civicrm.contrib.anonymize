<?php
/**
 * @file
 * Email API extensions.
 */

require_once 'vendor/autoload.php';

use Faker\Factory;

/**
 * Email.Anonymize API specification (optional).
 *
 * This is used for documentation and validation.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_email_anonymize_spec(&$spec) {
  // $spec['locale']['api.required'] = 1;
}

/**
 * Email.Anonymize API.
 *
 * @param array $params
 *   CiviCRM API parameters.
 *
 * @return array
 *   CiviCRM API result.
 *
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 *
 * @throws API_Exception
 *   CiviCRM API Exception.
 */
function civicrm_api3_email_anonymize($params) {
  if (!array_key_exists('locale', $params)) {
    $params['locale'] = 'en_US';
  }
  $faker = Faker\Factory::create($params['locale']);

  if (array_key_exists('id', $params)) {
    $get_params = array(
      'id' => $params['id'],
    );
    $email = civicrm_api3('Email', 'Get', $get_params);
    $values = reset($email['values']);
    $create_params = array(
      'email' => $faker->safeEmail(),
    );
    $email = civicrm_api3('Email', 'Create', array_merge($get_params, $create_params));
    return civicrm_api3_create_success($email, $get_params, 'Contact', 'Get');
  }
}
