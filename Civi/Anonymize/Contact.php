<?php

namespace Civi\Anonymize;

/**
 * @class
 * Anonymize class for Contacts.
 */
class Contact {

  /**
   * Accept a CiviCRM gender ID and return a Faker gender string.
   *
   * @param int|null $gender_id
   *   CiviCRM gender ID.
   *
   * @return string
   *   Gender label ("male", "female" or undefined).
   */
  public static function genderMapCiviToFaker($gender_id) {
    if (!isset($gender_id)) {
      return NULL;
    }

    switch ($gender_id) {
      case 1:
        return 'female';

      case 2:
        return 'male';

      default:
        return NULL;

    }
  }

  /**
   * Anonymize emails for a given contact ID.
   *
   * @param int $contact_id
   *   CiviCRM contact ID.
   */
  public static function anonymizeEmails($contact_id) {
    $emails = civicrm_api3('Email', 'get', array('contact_id' => $contact_id));
    if (!empty($emails['values'])) {
      foreach (array_keys($emails['values']) as $email_id) {
        civicrm_api3('Email', 'anonymize', array('id' => $email_id));
      }
    }
  }

  /**
   * Anonymize emails for a given contact ID.
   *
   * @param int|null $contact_id
   *   CiviCRM contact ID.
   */
  public static function anonymizeAddresses($contact_id) {
    $addresses = civicrm_api3('Address', 'get', array('contact_id' => $contact_id));
    if (!empty($addresses['values'])) {
      foreach (array_keys($addresses['values']) as $address_id) {
        civicrm_api3('Address', 'anonymize', array('id' => $address_id));
      }
    }
  }

}
