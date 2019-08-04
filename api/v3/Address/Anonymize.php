<?php
/**
 * @file
 * Address API extensions.
 */

require_once 'vendor/autoload.php';

use Faker\Factory;

/**
 * Address.Anonymize API specification (optional).
 *
 * This is used for documentation and validation.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_address_anonymize_spec(&$spec) {
  // $spec['locale']['api.required'] = 1;
}

/**
 * Address.Anonymize API.
 *
 * @param array $params
 *   CiviCRM API params array.
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
function civicrm_api3_address_anonymize($params) {
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
      // Faker streetName appends streetSuffix anyway.
      'street_name' => $faker->lastName(),
      'street_type' => $faker->streetSuffix(),
      'city' => $faker->city(),
      'postal_code' => $faker->postCode(),
      'postal_code_suffix' => empty($values['postal_code_suffix']) ? '' : $faker->postCode(),
    );
    // Put full street_address in as well as street_name, street_suffix etc.
    // If we don't do this, confidential data may leak via those fields.
    $create_params['street_address'] = "{$create_params['street_number']} {$create_params['street_name']} {$create_params['street_type']}";

    // Trim street_ty[e to 8 chars for CiviCRM storage.
    if (strlen($create_params['street_type']) > 8) {
      $create_params['street_type'] = '';
    }

    // If country_id is populated, replace with a random country_id.
    // We should then select a random province from that country.
    if (!empty($values['country_id'])) {
      $countries = civicrm_api3('Country', 'get', [
        'sequential' => 1,
        'options' => ['limit' => 0],
      ]);
      $create_params['country_id'] = $countries['values'][array_rand($countries['values'])]['id'];
      if (!empty($values['state_province_id'])) {
        $provinces = civicrm_api3('StateProvince', 'get', [
          'sequential' => 1,
          'country_id' => $create_params['country_id'],
          'options' => ['limit' => 0],
        ]);
        $create_params['state_province_id'] = $provinces['values'][array_rand($provinces['values'])]['id'];
      }
    }

    $address = civicrm_api3('Address', 'Create', array_merge($get_params, $create_params));
    return civicrm_api3_create_success($address, $get_params, 'Contact', 'Get');
  }
}
