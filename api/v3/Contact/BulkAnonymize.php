<?php

define('CRM_ENCRYPT', 1);
define('CRM_SETNULL', 2);
define('CRM_ENCRYPT_EMAIL', 3);

/**
 * Contact.BulkAnonymize API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contact_bulkanonymize_spec(&$spec) {
  $spec['strategy']['api.required'] = 1;
}

/**
 * Contact.BulkAnonymize API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_bulkanonymize($params) {
  switch ($params['strategy']) {
    case 'sql':
      // Smash the DB with some crazy query.
      $salt = md5(rand().microtime());
      $tables = array(
        'civicrm_contact' => array(
          'first_name' => CRM_ENCRYPT,
          'last_name' => CRM_ENCRYPT,
          'organization_name' => CRM_ENCRYPT,
          'household_name' => CRM_ENCRYPT,
          'sort_name' => CRM_ENCRYPT,
          'display_name' => CRM_SETNULL,
          'legal_name' => CRM_SETNULL,
          'addressee_display' => CRM_ENCRYPT,
          'postal_greeting_custom' => CRM_ENCRYPT,
          'email_greeting_display' => CRM_ENCRYPT,
          'birth_date' => CRM_SETNULL,
          'source' => CRM_ENCRYPT,
          'image_URL' => CRM_SETNULL,
        ),
        'civicrm_address' => array(
          'street_address' => CRM_ENCRYPT,
          'supplemental_address_1' => CRM_ENCRYPT,
          'supplemental_address_2' => CRM_ENCRYPT,
          'city' => CRM_ENCRYPT,
          'postal_code' => CRM_SETNULL,
          'postal_code_suffix' => CRM_SETNULL,
          'geo_code_1' => CRM_SETNULL,
          'geo_code_2' => CRM_SETNULL,
        ),
        'civicrm_website' => array(
          'url' => CRM_ENCRYPT,
        ),
        'civicrm_email' => array(
          'email' => CRM_ENCRYPT_EMAIL,
        ),
        'civicrm_phone' => array(
          'phone' => CRM_ENCRYPT,
        ),
      );
      foreach ($tables as $tableName => $fields) {
        $clauses = array();
        foreach ($fields as $fieldName => $action) {
          switch ($action) {
            case CRM_ENCRYPT:
              // bbchgsdp
              $rand = uniqid();
              $clauses[] = "  $fieldName = LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(MD5(CONCAT('$salt','$rand')),8),'0','O'),'1','I'),'2','Z'),'3','B'),'4','H'),'5','S'),'6','G'),'7','T'),'8','0'),'9','P'))";
              break;

            case CRM_ENCRYPT_EMAIL:
              // bcsdpizh@example.org
              $clauses[] = "  $fieldName = CONCAT(LCASE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LEFT(MD5(CONCAT('$salt',RAND(100))),8),'0','o'),'1','i'),'2','z'),'3','b'),'4','h'),'5','s'),'6','g'),'7','t'),'8','b'),'9','p')),'@example.org')";
              break;
            case CRM_SETNULL:
              $clauses[] = "  $fieldName = NULL";
              break;
          }
        }
        if (!empty($clauses)) {
          $clause = implode(",\n", $clauses);
          $query = "UPDATE $tableName SET \n$clause;\n";
          CRM_Core_DAO::executeQuery($query);
        }
      }

    case 'api':
      // Get all contacts via API. Iterate through and call
      // Contact.Anonymize([id => x]);
  }
}
