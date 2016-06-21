<?php

require_once 'vendor/autoload.php';

use Faker\Factory;

/**
 * Address.Anonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_address_Anonymize_spec(&$spec) {
  // $spec['locale']['api.required'] = 1;
}

/**
 * Address.Anonymize API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_address_Anonymize($params) {
  if (!array_key_exists('locale', $params)) {
    $params['locale'] = 'en_US';
  }
  $faker = Faker\Factory::create($params['locale']);

  if (array_key_exists('id', $params)) {
    $get_params = array(
      'id' => $params['id'],
    );
    $address = civicrm_api3('Address', 'Get', $get_params);
    $values = reset($address['values']);
    $create_params = array(
      'street_number' => $faker->buildingNumber(),
      'street_name' => $faker->streetName(),
      'street_type' => $faker->streetSuffix(),
      'city' => $faker->city(),
      'postal_code' => $faker->postCode(),
    );
    $address = civicrm_api3('Address', 'Create', array_merge($get_params, $create_params));
    return civicrm_api3_create_success($address, $get_params, 'Contact', 'Get');
  }
}
