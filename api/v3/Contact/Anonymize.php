<?php

require_once 'vendor/autoload.php';

use Civi\Anonymize\Contact as Contact;
use Faker\Factory;

/**
 * Contact.Anonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contact_Anonymize_spec(&$spec) {
  $spec['locale']['api.required'] = 1;
  $spec['id']['api.required'] = 1;
}

/**
 * Contact.Anonymize API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_Anonymize($params) {
  if (!array_key_exists('locale', $params)) {
    $params['locale'] = 'en_US';
  }
  $faker = Faker\Factory::create($params['locale']);
  if (array_key_exists('id', $params)) {
    $get_params = array(
      'id' => $params['id'],
    );
    $contact = civicrm_api3('Contact', 'Get', $get_params);
    $values = reset($contact['values']);
    // At this point, we should identify the type of contact (Individual, Organization)
    // then generate appropriate data.
    switch ($values['contact_type']) {
      case 'Individual':
        $gender = Contact::genderMapCiviToFaker($values['gender_id']);
        $create_params = array(
          'first_name' => $faker->firstName($gender),
          'last_name' => $faker->lastName(),
          'birth_date' => $faker->iso8601(rand(-10, -30) . ' years'),
        );
        break;

      case 'Organization':
        $create_params = array(
          'organization_name' => $faker->company(),
        );
        break;

      default:
        throw new API_Exception('Not prepared to handle contact_type=' . $values['contact_type'], 2);
    }

    Contact::anonymizeEmails($params['id']);
    Contact::anonymizeAddresses($params['id']);

    $contact = civicrm_api3('Contact', 'Create', array_merge($get_params, $create_params));
    return civicrm_api3_create_success($contact, $get_params, 'Contact', 'Get');
  }
  throw new API_Exception('Contact.Anonymize requires a contact ID', 1);
}
