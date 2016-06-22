<?php

namespace Civi\Anonymize;

class Contact {

  /**
   * Accept a CiviCRM gender ID and return a Faker gender string.
   *
   * @param integer|undefined gender ID
   * @return string gender label
   */
  public static function genderMapCiviToFaker($gender_id)
  {
    if (!isset($gender_id)) {
      return null;
    }
    switch ($gender_id) {
      case 1:
        return 'female';
      case 2:
        return 'male';
      default:
        return undefined;
    }
  }

  /**
   * Anonymize emails for a given contact ID.
   *
   * @param integer|undefined contact ID
   */
  public static function anonymizeEmails($contact_id)
  {
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
   * @param integer|undefined contact ID
   */
  public static function anonymizeAddresses($contact_id)
  {
    $addresses = civicrm_api3('Address', 'get', array('contact_id' => $contact_id));
    if (!empty($addresses['values'])) {
      foreach (array_keys($addresses['values']) as $address_id) {
        civicrm_api3('Address', 'anonymize', array('id' => $address_id));
      }
    }
  }


}
